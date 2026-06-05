<?php

namespace App\Http\Controllers;

use App\Models\ApplicationCompetition;
use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionImage;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionForm;
use App\Models\CompetitionFormType;
use App\Models\CompetitionResult;
use App\Models\Location;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\CompetitionResultPage;
use App\Support\ParticipantListingDateFilter;
use App\Support\StudentCompetitionListingSort;
use App\Support\UploadedFileErrors;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class CompetitionController extends Controller
{
    /** Завершённые соревнования старше этого срока у преподавателя уходят в фотоархив и не в основном списке. */
    private const COMPETITION_ARCHIVE_MONTHS = 6;

    private const TEACHER_INDEX_PER_PAGE_LIST = 50;

    private const TEACHER_INDEX_PER_PAGE_CARDS = 50;

    /**
     * Display a listing of the competitions for teachers.
     */
    public function index(Request $request)
    {
        return view('competitions.index', $this->resolveTeacherCompetitionsListing($request, false));
    }

    /**
     * Страница "Мои соревнования" (только созданные текущим преподавателем).
     */
    public function myIndex(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            abort(403);
        }

        return view('competitions.index', $this->resolveTeacherCompetitionsListing($request, true));
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveTeacherCompetitionsListing(Request $request, bool $onlyMine): array
    {
        $filter = $request->get('filter', 'all');
        $archiveThreshold = now()->subMonths(self::COMPETITION_ARCHIVE_MONTHS)->startOfDay();

        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');

        $dateFrom = null;
        $dateTo = null;
        if ($request->filled('date_from')) {
            try {
                $dateFrom = Carbon::parse($request->query('date_from'))->toDateString();
            } catch (\Throwable) {
            }
        }
        if ($request->filled('date_to')) {
            try {
                $dateTo = Carbon::parse($request->query('date_to'))->toDateString();
            } catch (\Throwable) {
            }
        }
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $sportId = null;
        if ($request->filled('sport_id') && is_numeric($request->query('sport_id'))) {
            $sid = (int) $request->query('sport_id');
            if (Sport::whereKey($sid)->exists()) {
                $sportId = $sid;
            }
        }

        $query = Competition::with(['sport', 'team', 'location', 'creator', 'category', 'participants.team.sport']);

        if ($onlyMine) {
            $query->where('created_by', auth()->id());
        }

        $query->where(function ($builder) use ($archiveThreshold) {
            $builder->where('status', '!=', 'finished')
                ->orWhereDate('end_date', '>', $archiveThreshold->toDateString());
        });

        $query->when($q !== '', function ($builder) use ($q) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $builder->where('name', 'like', $like);
        });

        if ($sportId !== null) {
            $query->whereListingSport($sportId);
        }

        $query->when($dateFrom && $dateTo, function ($builder) use ($dateFrom, $dateTo) {
            $s = $dateFrom ?? '0000-00-00';
            $e = $dateTo ?? '9999-12-31';
            $builder->where('start_date', '<=', $e)
                ->where('end_date', '>=', $s);
        });
        $query->when($dateFrom && ! $dateTo, fn ($builder) => $builder->whereDate('end_date', '>=', $dateFrom));
        $query->when(! $dateFrom && $dateTo, fn ($builder) => $builder->whereDate('start_date', '<=', $dateTo));

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $cardsSortStack = StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_CARDS);
        $listSortStack = StudentCompetitionListingSort::normalizeListStack(
            StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_LIST)
        );

        $view = $request->query('view', 'list');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'list';
        }

        $activeSortStack = $view === 'cards' ? $cardsSortStack : $listSortStack;
        StudentCompetitionListingSort::applyToQuery($query, $activeSortStack);

        $defaultPerPage = $view === 'cards'
            ? self::TEACHER_INDEX_PER_PAGE_CARDS
            : self::TEACHER_INDEX_PER_PAGE_LIST;

        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = $defaultPerPage;
        }

        $competitions = $query->paginate($perPage)->withQueryString();
        $sports = Sport::query()->orderBy('name')->get();

        return compact(
            'competitions',
            'filter',
            'view',
            'perPage',
            'q',
            'dateFrom',
            'dateTo',
            'sportId',
            'sports',
            'cardsSortStack',
            'listSortStack',
            'onlyMine',
        );
    }

    /**
     * Отдельная страница архива изображений соревнований.
     */
    public function photoArchive(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $archiveThreshold = now()->subMonths(self::COMPETITION_ARCHIVE_MONTHS)->startOfDay();

        $competitions = Competition::query()
            ->with(['sport', 'team', 'images', 'participants.user'])
            ->where('status', 'finished')
            ->whereDate('end_date', '<=', $archiveThreshold->toDateString())
            ->whereHas('results', function ($q) {
                $q->whereNotNull('place')
                    ->where('place', '!=', '');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%');
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('start_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('end_date', '<=', $dateTo);
            })
            ->orderByDesc('end_date')
            ->get();

        return view('competitions.photo-archive', compact('competitions', 'q', 'dateFrom', 'dateTo', 'archiveThreshold'));
    }

    public function destroyCompetitionPhoto(CompetitionImage $competitionImage)
    {
        $disk = Storage::disk('public');
        $normalized = str_replace('\\', '/', ltrim($competitionImage->path, '/'));
        if ($disk->exists($normalized)) {
            $disk->delete($normalized);
        }
        $competitionImage->delete();

        return back()
            ->with('success', 'Изображение удалено из архива.');
    }

    /**
     * Страница добавления фотографий к соревнованию.
     */
    public function photos(Competition $competition)
    {
        $images = CompetitionImage::query()
            ->where('competition_id', $competition->id)
            ->latest('id')
            ->get();

        return view('competitions.photos', compact('competition', 'images'));
    }

    public function storePhotos(Request $request, Competition $competition)
    {
        $uploaded = $request->file('images');
        $files = is_array($uploaded) ? $uploaded : ($uploaded ? [$uploaded] : []);

        if ($files === []) {
            return redirect()
                ->route('competitions.photos', $competition)
                ->withErrors(['images' => 'Выберите хотя бы один файл.']);
        }

        $missingIndex = UploadedFileErrors::firstMissing($files);
        if ($missingIndex !== null) {
            return redirect()
                ->route('competitions.photos', $competition)
                ->withErrors(['images' => UploadedFileErrors::missingSlotMessage($missingIndex)]);
        }

        $invalid = UploadedFileErrors::firstInvalid($files);
        if ($invalid !== null) {
            return redirect()
                ->route('competitions.photos', $competition)
                ->withErrors([
                    'images' => UploadedFileErrors::messageFor(
                        $invalid['file'],
                        $invalid['index'] + 1
                    ),
                ]);
        }

        $validated = $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,bmp,avif',
        ], [
            'images.required' => 'Выберите хотя бы один файл.',
            'images.*.uploaded' => 'Файл не загружен на сервер (превышен размер или лимит PHP). '.UploadedFileErrors::phpLimitsHint(),
            'images.*.file' => 'Каждый элемент должен быть файлом.',
            'images.*.mimes' => 'Допустимы JPEG, PNG, GIF, WebP, BMP, AVIF. Фото HEIC с iPhone сохраните как JPEG или загрузите с компьютера.',
            'images.*.max' => 'Размер одного файла не более 10 МБ.',
        ]);

        $dir = 'competition_photos/'.$competition->id;
        $disk = Storage::disk('public');
        if (! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        $saved = 0;
        foreach ($validated['images'] as $index => $file) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
            $extension = $extension === 'jpeg' ? 'jpg' : $extension;
            $filename = Str::uuid().'.'.$extension;
            $path = $file->storeAs($dir, $filename, 'public');
            if (! $path) {
                return redirect()
                    ->route('competitions.photos', $competition)
                    ->withErrors([
                        'images' => 'Файл №'.($index + 1).': не удалось сохранить на диск. Выполните php artisan storage:link и проверьте права на storage/app/public.',
                    ]);
            }
            CompetitionImage::create([
                'path' => $path,
                'competition_id' => $competition->id,
                'size_bytes' => $file->getSize(),
            ]);
            $saved++;
        }

        if ($saved === 0) {
            return redirect()
                ->route('competitions.photos', $competition)
                ->withErrors(['images' => 'Ни один файл не сохранён.']);
        }

        return redirect()->route('competitions.photos', $competition)
            ->with('success', 'Фотографии добавлены.');
    }

    /**
     * Show the form for creating a new competition.
     */
    public function create()
    {
        $teams = Team::with('sport')->orderBy('name')->get();
        $locations = Location::orderBy('location')->get();
        $categories = CompetitionCategory::orderBy('name_category')->get();

        return view('competitions.create', compact('teams', 'locations', 'categories'));
    }

    /**
     * Store a newly created competition in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'form_regulation_text' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'required|exists:locations,id',
            'competition_category_id' => 'nullable|exists:competition_categories,id',
            'result_type' => 'required|in:personal,team',
        ], [
            'end_date.after_or_equal' => 'Дата окончания должна быть позже или равна дате начала.',
        ]);

        // Автоматически устанавливаем статус "upcoming"
        $validated['status'] = 'upcoming';

        $isPersonal = ($validated['result_type'] ?? 'team') === 'personal';
        $team = null;
        if (! $isPersonal) {
            if (! filled($validated['team_id'])) {
                return redirect()->route('competitions.create')
                    ->withErrors(['team_id' => 'Нужно выбрать команду для командного соревнования.'])
                    ->withInput();
            }

            $team = Team::query()->with('sport')->find($validated['team_id']);
            if (! $team || ! $team->sport_id) {
                return redirect()->route('competitions.create')
                    ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.'])
                    ->withInput();
            }
        }

        $newStartDate = \Carbon\Carbon::parse($validated['start_date']);
        $newEndDate = \Carbon\Carbon::parse($validated['end_date']);

        if (! $isPersonal && $team) {
            $conflictingCompetitions = Competition::where('team_id', $team->id)
                ->where(function ($query) use ($newStartDate, $newEndDate) {
                    $query->where(function ($q) use ($newStartDate, $newEndDate) {
                        $q->where('start_date', '<=', $newEndDate->format('Y-m-d'))
                            ->where('end_date', '>=', $newStartDate->format('Y-m-d'));
                    });
                })
                ->get();

            if ($conflictingCompetitions->count() > 0) {
                $conflict = $conflictingCompetitions->first();

                return redirect()->route('competitions.create')
                    ->withErrors(['start_date' => 'Такое соревнование уже существует. Команда уже участвует в соревновании "'.$conflict->name.'" в этот период ('.$conflict->start_date->format('d.m.Y').' - '.$conflict->end_date->format('d.m.Y').').'])
                    ->withInput();
            }
        }

        $competition = Competition::create([
            'sport_id' => $isPersonal ? null : ($team?->sport_id),
            'team_id' => $isPersonal ? null : ($team?->id),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'form_regulation_text' => $validated['form_regulation_text'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'location_id' => $validated['location_id'],
            'competition_category_id' => $validated['competition_category_id'] ?? null,
            'result_type' => $validated['result_type'],
            'created_by' => auth()->id(),
        ]);

        // Если запрос через AJAX, возвращаем JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'competition_id' => $competition->id,
                'message' => 'Соревнование успешно создано!'
            ]);
        }

        return redirect()->route('competitions.index')
            ->with('success', 'Соревнование успешно создано!');
    }

    /**
     * Display the specified competition for teachers.
     */
    public function show(Competition $competition)
    {
        session()->forget('competition_show_return_from');

        // Обновляем статус соревнования перед отображением
        $competition->updateStatusIfNeeded();
        
        $competition->load([
            'sport',
            'team',
            'location',
            'creator',
            'participants.user',
            'participants.team.sport',
            'teacher.user',
            'category',
            'forms',
            'results.team.sport',
            'results.user',
            'competitionApplications' => fn ($q) => $q
                ->where('status', 'pending')
                ->with('student')
                ->orderBy('created_at'),
        ]);

        $competitionFormTypes = CompetitionFormType::query()->orderBy('name')->get();
        
        // Сортируем участников по фамилии и имени
        $competition->participants = $competition->participants->sortBy(function($participant) {
            return $participant->user->lastname . ' ' . $participant->user->firstname;
        })->values();
        
        // Получаем все локации для соревнований
        $allLocations = Location::orderBy('location')->get();

        $competitionShowBack = $this->competitionShowBackLink();
        $teacherCompetitionListBackUrl = $competitionShowBack['url'];
        $teacherCompetitionBackLabel = $competitionShowBack['label'];
        $competitionShowContextQuery = $this->competitionShowRouteQuery();

        $teams = Team::with('sport')->orderBy('name')->get();

        return view('competitions.show', compact(
            'competition',
            'allLocations',
            'teacherCompetitionListBackUrl',
            'teacherCompetitionBackLabel',
            'competitionShowContextQuery',
            'competitionFormTypes',
            'teams',
        ));
    }

    /**
     * Show the form for editing the specified competition.
     */
    public function edit(Competition $competition)
    {
        $teams = Team::with('sport')->orderBy('name')->get();
        $locations = Location::orderBy('location')->get();

        return view('competitions.edit', compact('competition', 'teams', 'locations'));
    }

    /**
     * Update the specified competition in storage.
     */
    public function update(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'form_regulation_text' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'required|exists:locations,id',
            'result_type' => 'required|in:personal,team',
        ]);

        // Автоматически устанавливаем статус "upcoming"
        $validated['status'] = 'upcoming';

        $isPersonal = ($validated['result_type'] ?? 'team') === 'personal';
        $team = null;
        if (! $isPersonal) {
            if (! filled($validated['team_id'])) {
                return redirect()->route('competitions.edit', $competition)
                    ->withErrors(['team_id' => 'Нужно выбрать команду для командного соревнования.'])
                    ->withInput();
            }

            $team = Team::query()->with('sport')->find($validated['team_id']);
            if (! $team || ! $team->sport_id) {
                return redirect()->route('competitions.edit', $competition)
                    ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.'])
                    ->withInput();
            }
        }

        $newStartDate = \Carbon\Carbon::parse($validated['start_date']);
        $newEndDate = \Carbon\Carbon::parse($validated['end_date']);

        if (! $isPersonal && $team) {
            $conflictingCompetitions = Competition::where('team_id', $team->id)
                ->where('id', '!=', $competition->id)
                ->where(function ($query) use ($newStartDate, $newEndDate) {
                    $query->where(function ($q) use ($newStartDate, $newEndDate) {
                        $q->where('start_date', '<=', $newEndDate->format('Y-m-d'))
                            ->where('end_date', '>=', $newStartDate->format('Y-m-d'));
                    });
                })
                ->get();

            if ($conflictingCompetitions->count() > 0) {
                $conflict = $conflictingCompetitions->first();

                return redirect()->route('competitions.edit', $competition)
                    ->withErrors(['start_date' => 'Такое соревнование уже существует. Команда уже участвует в соревновании "'.$conflict->name.'" в этот период ('.$conflict->start_date->format('d.m.Y').' - '.$conflict->end_date->format('d.m.Y').').'])
                    ->withInput();
            }
        }

        $competition->update([
            'sport_id' => $isPersonal ? null : ($team?->sport_id),
            'team_id' => $isPersonal ? null : ($team?->id),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'form_regulation_text' => $validated['form_regulation_text'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'location_id' => $validated['location_id'],
            'result_type' => $validated['result_type'],
        ]);

        return redirect()->route('competitions.index')
            ->with('success', 'Соревнование успешно обновлено!');
    }

    /**
     * Cancel the specified competition.
     */
    public function cancel(Competition $competition)
    {
        // Нельзя отменить уже идущие соревнования
        if ($competition->status === 'ongoing') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Нельзя отменить уже идущее соревнование.');
        }

        // Нельзя отменить уже отмененное соревнование
        if ($competition->status === 'cancelled') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Соревнование уже отменено.');
        }

        $competition->update(['status' => 'cancelled']);

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Соревнование успешно отменено!');
    }

    /**
     * Remove the specified competition from storage.
     */
    public function destroy(Competition $competition)
    {
        $competition->delete();

        return redirect()->route('competitions.index')
            ->with('success', 'Соревнование успешно удалено!');
    }

    /**
     * Display a listing of the competitions for students.
     */
    public function indexStudent(Request $request)
    {
        $filter = $this->studentCompetitionFilter($request);

        $view = $request->query('view', 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'cards';
        }

        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 50;
        }

        $listingFilters = $this->studentCompetitionListingFilters($request);

        $competitions = Competition::query()
            ->with(['sport', 'team', 'location', 'category', 'images', 'participants.team.sport']);

        $this->applyStudentCompetitionStatusFilter($competitions, $filter);
        $this->applyStudentCompetitionNameSearch($competitions, $listingFilters);
        $this->applyStudentCompetitionSportFilter($competitions, $listingFilters);
        $this->applyStudentCompetitionCategoryFilter($competitions, $listingFilters);

        ParticipantListingDateFilter::applyToCompetitionQuery(
            $competitions,
            $listingFilters['date_from'] ?? null,
            $listingFilters['date_to'] ?? null,
        );

        $cardsSortStack = StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_CARDS);
        $listSortStack = StudentCompetitionListingSort::normalizeListStack(
            StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_LIST)
        );
        $activeSortStack = $view === 'list' ? $listSortStack : $cardsSortStack;
        StudentCompetitionListingSort::applyToQuery($competitions, $activeSortStack);

        $competitions = $competitions->paginate($perPage)->withQueryString();

        $sportsForFilter = Sport::orderBy('name')->get();
        $categoriesForFilter = CompetitionCategory::query()
            ->whereHas('competitions')
            ->orderBy('name_category')
            ->get();
        $studentApplicationStates = $this->studentCompetitionApplicationStatesFor($competitions);

        return view(
            'competitions.student.index',
            compact('competitions', 'filter', 'listingFilters', 'sportsForFilter', 'categoriesForFilter', 'view', 'perPage', 'studentApplicationStates', 'cardsSortStack', 'listSortStack')
        );
    }

    public function myCompetitions(Request $request)
    {
        $filter = $this->studentCompetitionFilter($request);

        $view = $request->query('view', 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'cards';
        }

        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 50;
        }

        $listingFilters = $this->studentCompetitionListingFilters($request);

        $userId = (int) auth()->id();

        $competitions = Competition::query()
            ->with(['sport', 'team', 'location', 'category', 'images', 'participants.team.sport'])
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where(function ($qq) {
                        $qq->where('role', 'student')
                            ->orWhereNull('role');
                    });
            });

        $this->applyStudentCompetitionStatusFilter($competitions, $filter);
        $this->applyStudentCompetitionNameSearch($competitions, $listingFilters);
        $this->applyStudentCompetitionSportFilter($competitions, $listingFilters);
        $this->applyStudentCompetitionCategoryFilter($competitions, $listingFilters);

        ParticipantListingDateFilter::applyToCompetitionQuery(
            $competitions,
            $listingFilters['date_from'] ?? null,
            $listingFilters['date_to'] ?? null,
        );

        $cardsSortStack = StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_CARDS);
        $listSortStack = StudentCompetitionListingSort::normalizeListStack(
            StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_LIST)
        );
        $activeSortStack = $view === 'list' ? $listSortStack : $cardsSortStack;
        StudentCompetitionListingSort::applyToQuery($competitions, $activeSortStack);

        $competitions = $competitions->paginate($perPage)->withQueryString();
        $sportsForFilter = Sport::orderBy('name')->get();
        $categoriesForFilter = CompetitionCategory::query()
            ->whereHas('competitions')
            ->orderBy('name_category')
            ->get();
        $studentApplicationStates = $this->studentCompetitionApplicationStatesFor($competitions);

        return view(
            'competitions.student.my',
            compact('competitions', 'filter', 'listingFilters', 'sportsForFilter', 'categoriesForFilter', 'view', 'perPage', 'studentApplicationStates', 'cardsSortStack', 'listSortStack')
        );
    }

    private const STUDENT_COMPETITION_FILTERS = ['all', 'upcoming', 'ongoing', 'finished', 'cancelled'];

    private function studentCompetitionFilter(Request $request): string
    {
        $filter = (string) $request->get('filter', 'upcoming');

        return in_array($filter, self::STUDENT_COMPETITION_FILTERS, true) ? $filter : 'upcoming';
    }

    /**
     * @return array{sport_id?: int|string|null, competition_category_id?: int|string|null, date_from?: string|null, date_to?: string|null, q: string}
     */
    private function studentCompetitionListingFilters(Request $request): array
    {
        $listingFilters = $request->validate([
            'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
            'competition_category_id' => ['nullable', 'integer', Rule::exists('competition_categories', 'id')],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        if (! empty($listingFilters['date_from']) && ! empty($listingFilters['date_to'])
            && $listingFilters['date_from'] > $listingFilters['date_to']) {
            [$listingFilters['date_from'], $listingFilters['date_to']] = [
                $listingFilters['date_to'],
                $listingFilters['date_from'],
            ];
        }

        $listingFilters['q'] = Str::limit(trim((string) ($listingFilters['q'] ?? '')), 200, '');

        return $listingFilters;
    }

    private function applyStudentCompetitionStatusFilter($query, string $filter): void
    {
        if ($filter === 'cancelled') {
            $query->where('status', 'cancelled');

            return;
        }

        $query->where('status', '!=', 'cancelled');

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
    }

    private function applyStudentCompetitionNameSearch($query, array $listingFilters): void
    {
        $q = trim((string) ($listingFilters['q'] ?? ''));
        if ($q === '') {
            return;
        }

        $like = '%'.addcslashes($q, '%_\\').'%';
        $query->where('name', 'like', $like);
    }

    private function applyStudentCompetitionSportFilter($query, array $listingFilters): void
    {
        if (empty($listingFilters['sport_id'])) {
            return;
        }

        $query->whereListingSport((int) $listingFilters['sport_id']);
    }

    private function applyStudentCompetitionCategoryFilter($query, array $listingFilters): void
    {
        if (empty($listingFilters['competition_category_id'])) {
            return;
        }

        $query->where('competition_category_id', (int) $listingFilters['competition_category_id']);
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Competition>|\Illuminate\Support\Collection<int, Competition>  $competitions
     * @return array<int, string> participant|pending|can_apply
     */
    private function studentCompetitionApplicationStatesFor($competitions): array
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'student') {
            return [];
        }

        $items = $competitions instanceof LengthAwarePaginator
            ? $competitions->getCollection()
            : collect($competitions);

        $competitionIds = $items->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($competitionIds === []) {
            return [];
        }

        $participantIds = CompetitionParticipant::query()
            ->where('user_id', $user->id)
            ->whereIn('competition_id', $competitionIds)
            ->pluck('competition_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $pendingIds = ApplicationCompetition::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereIn('competition_id', $competitionIds)
            ->pluck('competition_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $states = [];
        foreach ($items as $competition) {
            $id = (int) $competition->id;
            if (isset($participantIds[$id])) {
                $states[$id] = 'participant';
            } elseif (isset($pendingIds[$id]) && $competition->status === 'upcoming') {
                $states[$id] = 'pending';
            } elseif ($competition->status === 'upcoming') {
                $states[$id] = 'can_apply';
            }
        }

        return $states;
    }

    /**
     * Display the specified competition for students.
     */
    public function showStudent(Competition $competition)
    {
        $competition->updateStatusIfNeeded();
        $competition->refresh();

        $competition->load(['sport', 'team', 'location', 'participants.user']);

        $sorted = $competition->participants->sortBy(function ($participant) {
            return $participant->user->lastname.' '.$participant->user->firstname;
        })->values();
        $students = $sorted->filter(function ($participant) {
            return ($participant->role ?? 'student') === 'student';
        })->values();
        $competition->setRelation('participants', $students);

        $isParticipant = $students->contains(fn ($p) => (int) $p->user_id === (int) auth()->id());

        $pendingApplication = ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        $latestRejectedApplication = ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', auth()->id())
            ->where('status', 'rejected')
            ->orderByDesc('updated_at')
            ->first();

        $latestAcceptedApplication = ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', auth()->id())
            ->where('status', 'accepted')
            ->orderByDesc('accepted_at')
            ->orderByDesc('updated_at')
            ->first();

        $latestExpiredApplication = ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', auth()->id())
            ->where('status', ApplicationCompetition::STATUS_EXPIRED)
            ->orderByDesc('updated_at')
            ->first();

        $competitionShowBack = $this->competitionShowBackLink();
        $competitionShowContextQuery = $this->competitionShowRouteQuery();

        $myCompetitionForm = null;
        if ($isParticipant) {
            $myCompetitionForm = CompetitionForm::query()
                ->with('formType')
                ->where('competition_id', $competition->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('competitions.student.show', [
            'competition' => $competition,
            'isParticipant' => $isParticipant,
            'pendingApplication' => $pendingApplication,
            'latestExpiredApplication' => $latestExpiredApplication,
            'latestRejectedApplication' => $latestRejectedApplication,
            'latestAcceptedApplication' => $latestAcceptedApplication,
            'competitionShowBack' => $competitionShowBack,
            'competitionShowContextQuery' => $competitionShowContextQuery,
            'myCompetitionForm' => $myCompetitionForm,
        ]);
    }

    /**
     * Студент подаёт заявку на участие (ожидает рассмотрения преподавателем).
     */
    public function applyStudent(Request $request, Competition $competition)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Заявку могут подавать только студенты.');
        }

        $competition->updateStatusIfNeeded();
        $competition->refresh();

        if ($competition->status === 'cancelled') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Это соревнование отменено.');
        }

        if ($competition->status !== 'upcoming') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Заявку можно подать только на предстоящие соревнования.');
        }

        if (CompetitionParticipant::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->exists()) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Вы уже в списке участников этого соревнования.');
        }

        $overlap = $this->findOverlappingCompetitionForStudent($user->id, $competition);
        if ($overlap) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Вы находитесь в это время на другом соревновании и не можете подать заявку.');
        }

        if (ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->where('status', ApplicationCompetition::STATUS_PENDING)
            ->exists()) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Заявка уже отправлена и ожидает рассмотрения.');
        }

        if (ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->where('status', ApplicationCompetition::STATUS_EXPIRED)
            ->exists()) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Заявка по этому соревнованию уже была подана и не была рассмотрена.');
        }

        ApplicationCompetition::create([
            'user_id' => $user->id,
            'competition_id' => $competition->id,
            'status' => 'pending',
        ]);

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Заявка отправлена. После одобрения преподавателем вы появитесь в списке участников.');
    }

    /**
     * Преподаватель принимает заявку студента на соревнование.
     */
    public function acceptCompetitionApplication(Competition $competition, ApplicationCompetition $application)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        if ((int) $application->competition_id !== (int) $competition->id) {
            abort(404);
        }

        $competition->updateStatusIfNeeded();
        $competition->refresh();

        if ($competition->status !== 'upcoming') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Принимать заявки можно только к предстоящим соревнованиям.');
        }

        if ($application->status !== 'pending') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Эта заявка уже обработана.');
        }

        $student = $application->student;
        if (! $student || $student->role !== 'student') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Некорректные данные заявки.');
        }

        $overlap = $this->findOverlappingCompetitionForStudent($student->id, $competition);
        if ($overlap) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Нельзя принять заявку: студент уже участвует в соревновании «'.$overlap->name.'» в это время.');
        }

        if (! CompetitionParticipant::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $student->id)
            ->exists()) {
            CompetitionParticipant::create([
                'competition_id' => $competition->id,
                'user_id' => $student->id,
                'role' => 'student',
            ]);
        }

        $application->update([
            'status' => 'accepted',
            'accepted_by_user_id' => auth()->id(),
            'accepted_at' => now(),
            'rejection_reason' => null,
        ]);

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Заявка принята, студент добавлен в участники.');
    }

    /**
     * Преподаватель отклоняет заявку студента.
     */
    public function rejectCompetitionApplication(Request $request, Competition $competition, ApplicationCompetition $application)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        if ((int) $application->competition_id !== (int) $competition->id) {
            abort(404);
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:2000',
        ], [
            'rejection_reason.max' => 'Текст причины не длиннее 2000 символов.',
        ]);

        if ($application->status !== 'pending') {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Эта заявка уже обработана.');
        }

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'accepted_by_user_id' => null,
            'accepted_at' => null,
        ]);

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Заявка отклонена.');
    }

    /**
     * Add a participant to the competition.
     */
    public function addParticipant(Request $request, Competition $competition)
    {
        // Обновляем статус соревнования перед проверкой
        $competition->updateStatusIfNeeded();
        // Перезагружаем модель из базы данных, чтобы получить актуальный статус
        $competition->refresh();
        
        // Проверяем статус соревнования - можно добавлять участников только к предстоящим соревнованиям
        if ($competition->status !== 'upcoming') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Участников можно добавлять только к предстоящим соревнованиям.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Участников можно добавлять только к предстоящим соревнованиям.');
        }

        $validated = $request->validate([
            'student_data' => 'required|string',
            'role' => 'nullable|in:student,teacher',
            'team_id' => 'nullable|exists:teams,id',
        ], [
            'student_data.required' => 'Выберите студента из списка.',
        ]);

        $studentData = json_decode($validated['student_data'], true);
        if (!is_array($studentData) || empty($studentData['login'])) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Не удалось определить выбранного студента. Попробуйте выбрать снова.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->withErrors(['student_data' => 'Не удалось определить выбранного студента. Попробуйте выбрать снова.'])
                ->withInput();
        }

        $firstname = trim($studentData['firstname'] ?? '');
        $lastname = trim($studentData['lastname'] ?? '');
        $patronymic = trim($studentData['patronymic'] ?? '');
        $login = trim($studentData['login']);
        $role = $studentData['role'] ?? 'student';
        $groupName = $studentData['group_name'] ?? null;
        if (!$groupName && !empty($studentData['dn'])) {
            $groupName = $this->extractGroupFromDn($studentData['dn']);
        }

        // Для всех преподавателей устанавливаем группу "Преподаватели"
        if ($role === 'teacher') {
            $groupName = 'Преподаватели';
        }

        if ($firstname === '' || $lastname === '' || $login === '') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'У выбранного студента отсутствуют необходимые данные.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->withErrors(['student_data' => 'У выбранного студента отсутствуют необходимые данные.'])
                ->withInput();
        }

        $user = User::where('login', $login)->first();

        if (!$user) {
            $user = User::create([
                'login' => $login,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'role' => $role,
                'group_name' => $groupName,
                'active' => true,
            ]);
        } else {
            $user->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'role' => $role ?? $user->role,
                'group_name' => $groupName ?? $user->group_name,
            ]);
        }

        if (($user->role ?? 'student') === 'student') {
            $overlap = $this->findOverlappingCompetitionForStudent($user->id, $competition);
            if ($overlap) {
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => 'Нельзя добавить участника: студент уже участвует в соревновании «'.$overlap->name.'» в это время.'], 422);
                }
                return $this->redirectToCompetitionShow($competition)
                    ->with('participants_error', 'Нельзя добавить участника: студент уже участвует в соревновании «'.$overlap->name.'» в это время.')
                    ->withInput();
            }
        }

        $isTeacher = ($user->role ?? 'student') === 'teacher';
        if ($isTeacher) {
            $competition->loadMissing('teacher');
            $existingTeacherId = (int) ($competition->teacher?->user_id ?? 0);
            if ($existingTeacherId > 0 && $existingTeacherId !== (int) $user->id) {
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => 'В соревновании уже назначен преподаватель.'], 422);
                }

                return $this->redirectToCompetitionShow($competition)
                    ->with('participants_error', 'В соревновании уже назначен преподаватель.');
            }
        }

        // Проверяем, не участвует ли уже этот пользователь
        $existingParticipant = CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Этот пользователь уже участвует в соревновании.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('participants_error', 'Этот пользователь уже участвует в соревновании.');
        }

        $isPersonal = ($competition->result_type ?? 'team') === 'personal';
        $isStudent = ($user->role ?? 'student') === 'student';
        $selectedTeamId = isset($validated['team_id']) ? (int) $validated['team_id'] : null;

        if ($isPersonal && $isStudent && ! $selectedTeamId) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Для личного соревнования нужно выбрать команду (дисциплину) для участника.'], 422);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('participants_error', 'Для личного соревнования нужно выбрать команду (дисциплину) для участника.')
                ->withInput();
        }

        // Для личных соревнований спорт может быть не задан при создании.
        // Заполняем sport_id по первой добавленной команде (дисциплине) участника.
        if ($isPersonal && $isStudent && $selectedTeamId) {
            $selectedTeam = Team::query()->with('sport')->find($selectedTeamId);
            if (! $selectedTeam || ! $selectedTeam->sport_id) {
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.'], 422);
                }

                return $this->redirectToCompetitionShow($competition)
                    ->with('participants_error', 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.')
                    ->withInput();
            }

            if (! $competition->sport_id) {
                $competition->update(['sport_id' => $selectedTeam->sport_id]);
                $competition->refresh();
            }
        }

        $participant = CompetitionParticipant::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'team_id' => ($isPersonal && $isStudent) ? $selectedTeamId : null,
            'role' => $user->role ?? 'student',
        ]);
        $participant->load('user');

        // Если добавлен преподаватель — фиксируем его отдельно (один на соревнование).
        if ($isTeacher) {
            $competition->teacher()->updateOrCreate(
                ['competition_id' => $competition->id],
                ['user_id' => $user->id]
            );
            $competition->loadMissing('teacher.user');

            if ($request->expectsJson()) {
                $teacherUser = $competition->teacher?->user;
                return response()->json([
                    'ok' => true,
                    'message' => 'Преподаватель добавлен.',
                    'teacher_id' => $teacherUser?->id,
                    'teacher_label' => $teacherUser
                        ? trim($teacherUser->lastname.' '.$teacherUser->firstname.' '.($teacherUser->patronymic ?? ''))
                        : null,
                    'teacher_html' => view('competitions.partials.teacher-block', [
                        'competition' => $competition,
                    ])->render(),
                ]);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('success', 'Преподаватель добавлен.');
        }

        // Автодобавление в команду (только для студентов и только для личных соревнований).
        if ($isPersonal && $isStudent && $selectedTeamId) {
            $existingMember = \App\Models\TeamMember::query()
                ->where('team_id', $selectedTeamId)
                ->where('user_id', $user->id)
                ->whereNull('out')
                ->first();

            if (! $existingMember) {
                \App\Models\TeamMember::query()->create([
                    'team_id' => $selectedTeamId,
                    'user_id' => $user->id,
                    'id_adding' => auth()->id(),
                    'type_user' => 'member',
                    'joined_at' => now(),
                    'added_via' => 'competition',
                ]);
            }
        }

        // Автодобавление в команду соревнования для командных соревнований.
        if (! $isPersonal && $isStudent && $competition->team_id) {
            $teamId = (int) $competition->team_id;
            $existingMember = \App\Models\TeamMember::query()
                ->where('team_id', $teamId)
                ->where('user_id', $user->id)
                ->whereNull('out')
                ->first();

            if (! $existingMember) {
                \App\Models\TeamMember::query()->create([
                    'team_id' => $teamId,
                    'user_id' => $user->id,
                    'id_adding' => auth()->id(),
                    'type_user' => 'member',
                    'joined_at' => now(),
                    'added_via' => 'competition',
                ]);
            }
        }

        ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'accepted',
                'accepted_by_user_id' => auth()->id(),
                'accepted_at' => now(),
                'rejection_reason' => null,
            ]);

        if ($request->expectsJson()) {
            return response()->json($this->participantLiveUpdatePayload($competition, $participant));
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Участник успешно добавлен в соревнование!');
    }

    /**
     * @return array<string, mixed>
     */
    private function participantLiveUpdatePayload(Competition $competition, CompetitionParticipant $participant): array
    {
        $participant->loadMissing('user', 'team.sport');
        $isStudent = ($participant->role ?? 'student') === 'student';
        $count = CompetitionParticipant::query()->where('competition_id', $competition->id)->count();
        $studentCount = CompetitionParticipant::query()
            ->where('competition_id', $competition->id)
            ->where(function ($q) {
                $q->where('role', 'student')->orWhereNull('role');
            })
            ->count();

        $payload = [
            'ok' => true,
            'message' => 'Участник успешно добавлен в соревнование!',
            'count' => $count,
            'student_count' => $studentCount,
            'user_id' => (int) $participant->user_id,
            'is_student' => $isStudent,
            'row_html' => view('competitions.partials.participant-row', [
                'competition' => $competition,
                'participant' => $participant,
            ])->render(),
        ];

        if ($isStudent) {
            $competitionFormTypes = CompetitionFormType::query()->orderBy('name')->get();
            $payload['forms_row_html'] = view('competitions.partials.competition-form-row', [
                'competition' => $competition,
                'participant' => $participant,
                'competitionFormTypes' => $competitionFormTypes,
                'form' => null,
                'formsLocked' => $competition->status === 'cancelled',
                'formsReturnOnly' => $competition->formsReturnStatusEditable(),
            ])->render();
            $payload['admission_row_html'] = view('competitions.partials.competition-admission-row', [
                'competition' => $competition,
                'participant' => $participant,
                'admissionEditable' => $competition->medicalAdmissionStatusEditable(),
            ])->render();
        }

        return $payload;
    }

    private function findOverlappingCompetitionForStudent(int $userId, Competition $competition): ?Competition
    {
        $start = $competition->start_date;
        $end = $competition->end_date;

        if (! $start || ! $end) {
            return null;
        }

        return Competition::query()
            ->where('id', '!=', $competition->id)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where(function ($qq) {
                        $qq->where('role', 'student')
                            ->orWhereNull('role');
                    });
            })
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Update participant role.
     */
    public function updateParticipantRole(Request $request, Competition $competition, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|in:student,teacher',
        ]);

        // Преобразуем userId в число, если это строка
        $userId = is_numeric($userId) ? (int)$userId : $userId;
        
        $participant = CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Участник не найден в этом соревновании.');
        }

        // Используем прямое обновление через запрос, так как модель имеет составной первичный ключ
        CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $userId)
            ->update(['role' => $validated['role']]);

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Роль участника успешно обновлена!');
    }

    /**
     * Save form assignments (вид формы и номер формы) for competition students.
     */
    public function storeForms(Request $request, Competition $competition)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        // Обновляем статус соревнования перед проверкой
        $competition->updateStatusIfNeeded();
        $competition->refresh();

        if ($competition->status === 'cancelled') {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Нельзя изменять форму для отменённого соревнования.',
                ], 422);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Нельзя изменять форму для отменённого соревнования.');
        }

        if ($competition->formsReturnStatusEditable()) {
            return $this->storeFormsReturnStatusOnly($request, $competition);
        }

        if (! $competition->formsAreEditable()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Нельзя изменять форму для этого соревнования.',
                ], 422);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Нельзя изменять форму для этого соревнования.');
        }

        $validated = $request->validate([
            'forms' => 'required|array',
            'forms.*.form_issued' => 'nullable|in:0,1',
            'forms.*.form_type_id' => 'nullable|integer|exists:competition_form_types,id',
            'forms.*.form_number' => 'nullable|string|max:50',
            'forms.*.form_status' => 'nullable|string|in:pending,submitted',
        ]);

        $competition->load('participants');
        $studentParticipants = $competition->participants->filter(function ($participant) {
            return ($participant->role ?? 'student') === 'student';
        });

        // Проверка на дубли пары «вид формы + номер» в отправленной форме
        $seenTypeNumbers = [];
        $savedFormDates = [];

        foreach ($studentParticipants as $participant) {
            $userId = (int) $participant->user_id;
            $row = $validated['forms'][(string) $userId] ?? null;
            if (!is_array($row)) {
                continue;
            }

            $formIssued = ($row['form_issued'] ?? '0') === '1';
            $formTypeId = isset($row['form_type_id']) ? (int) $row['form_type_id'] : 0;
            $formNumber = trim((string) ($row['form_number'] ?? ''));
            $rowFormStatus = $row['form_status'] ?? CompetitionForm::STATUS_PENDING;
            if (! in_array($rowFormStatus, [CompetitionForm::STATUS_PENDING, CompetitionForm::STATUS_SUBMITTED], true)) {
                $rowFormStatus = CompetitionForm::STATUS_PENDING;
            }

            if (! $formIssued) {
                $form = CompetitionForm::firstOrNew([
                    'competition_id' => $competition->id,
                    'user_id' => $userId,
                ]);
                $form->form_issued = false;
                $form->form_type_id = null;
                $form->form_view = null;
                $form->form_number = null;
                $form->form_status = CompetitionForm::STATUS_PENDING;
                $form->issued_at = null;
                $form->submitted_at = null;
                $form->save();
                $savedFormDates[(string) $userId] = [
                    'issued_at' => null,
                    'submitted_at' => null,
                ];
                continue;
            }

            // Дубль в таблице: «Не сдал» резервирует номер до сдачи
            if ($formNumber !== '' && $formTypeId > 0) {
                $typeNumberKey = $formTypeId.':'.$formNumber;
                if ($rowFormStatus === CompetitionForm::STATUS_PENDING) {
                    if (isset($seenTypeNumbers[$typeNumberKey]) && (int) $seenTypeNumbers[$typeNumberKey] !== $userId) {
                        $msg = 'Этот вид формы и номер уже заняты другим студентом в таблице.';
                        if ($request->expectsJson()) {
                            return response()->json(['ok' => false, 'message' => $msg], 422);
                        }
                        return $this->redirectToCompetitionShow($competition)->with('error', $msg);
                    }
                    $seenTypeNumbers[$typeNumberKey] = $userId;
                } elseif (isset($seenTypeNumbers[$typeNumberKey]) && (int) $seenTypeNumbers[$typeNumberKey] !== $userId) {
                    $msg = 'Этот вид формы и номер уже заняты другим студентом в таблице.';
                    if ($request->expectsJson()) {
                        return response()->json(['ok' => false, 'message' => $msg], 422);
                    }
                    return $this->redirectToCompetitionShow($competition)->with('error', $msg);
                }
            }

            if ($formTypeId <= 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Нужно указать вид формы для студента: '.$participant->user?->lastname.' '.$participant->user?->firstname,
                    ], 422);
                }
                return $this->redirectToCompetitionShow($competition)
                    ->with('error', 'Нужно указать вид формы для студента: '.$participant->user?->lastname.' '.$participant->user?->firstname);
            }

            $type = CompetitionFormType::find($formTypeId);
            if (! $type) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Выбранный вид формы не найден.',
                    ], 422);
                }
                return $this->redirectToCompetitionShow($competition)
                    ->with('error', 'Выбранный вид формы не найден.');
            }

            $form = CompetitionForm::firstOrNew([
                'competition_id' => $competition->id,
                'user_id' => $userId,
            ]);
            $wasIssued = $form->exists && (bool) $form->form_issued;
            $wasSubmitted = $form->exists && $form->form_status === CompetitionForm::STATUS_SUBMITTED;
            $formStatus = $rowFormStatus;

            // Конфликт: тот же номер и «Не сдал», если даты соревнований пересекаются
            if ($formNumber !== '' && $formTypeId > 0) {
                $conflict = $this->findFormTypeNumberConflict($formTypeId, $formNumber, $competition, $userId);
                if ($conflict) {
                    $msg = $this->formTypeNumberConflictMessage($conflict, $competition->id);
                    if ($request->expectsJson()) {
                        return response()->json(['ok' => false, 'message' => $msg], 422);
                    }
                    return $this->redirectToCompetitionShow($competition)->with('error', $msg);
                }
            }

            $form->form_issued = true;
            $form->form_type_id = $formTypeId;
            $form->form_view = $type->name;
            $form->form_number = $formNumber !== '' ? $formNumber : null;
            $form->form_status = $formStatus;

            if (! $wasIssued) {
                $form->issued_at = now()->toDateString();
            }

            if ($formStatus === CompetitionForm::STATUS_SUBMITTED) {
                if (! $wasSubmitted) {
                    $form->submitted_at = now()->toDateString();
                }
            } else {
                $form->submitted_at = null;
            }

            $form->save();

            $savedFormDates[(string) $userId] = [
                'issued_at' => $form->formattedIssuedAt(),
                'submitted_at' => $form->formattedSubmittedAt(),
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Данные формы для студентов сохранены.',
                'forms' => $savedFormDates,
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Данные формы для студентов сохранены.');
    }

    /**
     * После завершения соревнования — только отметка «Сдал» / «Не сдал».
     */
    private function storeFormsReturnStatusOnly(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'forms' => 'required|array',
            'forms.*.form_status' => 'nullable|string|in:pending,submitted',
        ]);

        $competition->load('participants');
        $studentParticipants = $competition->participants->filter(function ($participant) {
            return ($participant->role ?? 'student') === 'student';
        });

        $savedFormDates = [];

        foreach ($studentParticipants as $participant) {
            $userId = (int) $participant->user_id;
            $row = $validated['forms'][(string) $userId] ?? null;
            if (! is_array($row)) {
                continue;
            }

            $form = CompetitionForm::query()
                ->where('competition_id', $competition->id)
                ->where('user_id', $userId)
                ->first();

            if (! $form || ! $form->form_issued) {
                continue;
            }

            $rowFormStatus = $row['form_status'] ?? CompetitionForm::STATUS_PENDING;
            if (! in_array($rowFormStatus, [CompetitionForm::STATUS_PENDING, CompetitionForm::STATUS_SUBMITTED], true)) {
                $rowFormStatus = CompetitionForm::STATUS_PENDING;
            }

            $wasSubmitted = $form->form_status === CompetitionForm::STATUS_SUBMITTED;

            $form->form_status = $rowFormStatus;

            if ($rowFormStatus === CompetitionForm::STATUS_SUBMITTED) {
                if (! $wasSubmitted) {
                    $form->submitted_at = now()->toDateString();
                }
            } else {
                $form->submitted_at = null;
            }

            $form->save();

            $savedFormDates[(string) $userId] = [
                'issued_at' => $form->formattedIssuedAt(),
                'submitted_at' => $form->formattedSubmittedAt(),
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Отметки о сдаче формы сохранены.',
                'forms' => $savedFormDates,
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Отметки о сдаче формы сохранены.');
    }

    public function storeMedicalAdmission(Request $request, Competition $competition)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $file = $request->file('medical_admission_document');
        if ($file && method_exists($file, 'isValid') && ! $file->isValid()) {
            $phpUploadMax = (string) ini_get('upload_max_filesize');
            $phpPostMax = (string) ini_get('post_max_size');

            $msg = match ($file->getError()) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Превышен лимит загрузки файла на сервере. ',
                UPLOAD_ERR_PARTIAL => 'Файл загрузился не полностью. ',
                UPLOAD_ERR_NO_FILE => 'Файл не выбран. ',
                UPLOAD_ERR_NO_TMP_DIR => 'На сервере отсутствует временная папка для загрузки. ',
                UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск. ',
                UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением PHP. ',
                default => 'Не удалось загрузить файл. ',
            };

            $msg .= 'Лимиты PHP: upload_max_filesize='.$phpUploadMax.', post_max_size='.$phpPostMax.'.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => trim($msg)], 422);
            }

            return $this->redirectToCompetitionShow($competition)->with('error', trim($msg));
        }

        $competition->updateStatusIfNeeded();
        $competition->refresh();

        $action = (string) $request->input('submit_action', 'save_admissions');
        if (! in_array($action, ['save_admissions', 'attach_document'], true)) {
            $action = 'save_admissions';
        }

        if ($competition->status === 'cancelled') {
            $message = 'Допуск нельзя изменять для отменённого соревнования.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return $this->redirectToCompetitionShow($competition)->with('error', $message);
        }

        if ($action === 'save_admissions' && ! $competition->medicalAdmissionStatusEditable()) {
            $message = 'Допуск можно изменять только для предстоящих соревнований.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return $this->redirectToCompetitionShow($competition)->with('error', $message);
        }

        if ($action === 'attach_document' && ! $competition->medicalAdmissionDocumentEditable()) {
            $message = 'Документ можно прикрепить только к предстоящему соревнованию.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return $this->redirectToCompetitionShow($competition)->with('error', $message);
        }

        $rules = [
            'submit_action' => 'nullable|string|in:save_admissions,attach_document',
        ];

        if ($action === 'save_admissions') {
            $rules['admissions'] = 'required|array';
            $rules['admissions.*.medical_admission_status'] = 'nullable|string|in:'.implode(',', CompetitionParticipant::MEDICAL_ADMISSION_STATUSES);
        } else {
            $rules['admissions'] = 'nullable|array';
            $rules['admissions.*.medical_admission_status'] = 'nullable|string|in:'.implode(',', CompetitionParticipant::MEDICAL_ADMISSION_STATUSES);
        }

        $validated = $request->validate($rules);

        $competition->load('participants');
        $studentParticipants = $competition->participants->filter(function ($participant) {
            return ($participant->role ?? 'student') === 'student';
        });

        if ($action === 'save_admissions') {
            foreach ($studentParticipants as $participant) {
                $userId = (int) $participant->user_id;
                $row = $validated['admissions'][(string) $userId] ?? null;
                if (! is_array($row)) {
                    continue;
                }

                $status = $row['medical_admission_status'] ?? CompetitionParticipant::MEDICAL_ADMISSION_PENDING;
                if (! in_array($status, CompetitionParticipant::MEDICAL_ADMISSION_STATUSES, true)) {
                    $status = CompetitionParticipant::MEDICAL_ADMISSION_PENDING;
                }

                CompetitionParticipant::where('competition_id', $competition->id)
                    ->where('user_id', $userId)
                    ->update(['medical_admission_status' => $status]);
            }
        }

        if ($file) {
            $maxBytes = 100 * 1024 * 1024;
            $size = (int) ($file->getSize() ?? 0);
            if ($size > $maxBytes) {
                $msg = 'Превышен размер файла (100MB).';
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => $msg], 422);
                }
                return $this->redirectToCompetitionShow($competition)->with('error', $msg);
            }

            $ext = strtolower((string) $file->getClientOriginalExtension());
            $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx'];
            if (! in_array($ext, $allowed, true)) {
                $msg = 'Можно прикрепить только PDF / PNG / JPG / DOC / DOCX.';
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => $msg], 422);
                }
                return $this->redirectToCompetitionShow($competition)->with('error', $msg);
            }

            $path = $file->storeAs(
                'competitions/'.$competition->id.'/medical-admission',
                'signed-'.now()->format('Ymd_His').'-'.Str::uuid().'.'.$ext,
                'public'
            );

            $competition->medical_admission_document_path = $path;
            $competition->medical_admission_document_original_name = (string) $file->getClientOriginalName();
            $competition->save();
        } elseif ($action === 'attach_document') {
            // Если PHP/сервер отбросил файл из-за лимитов, Laravel может не увидеть файл вовсе.
            $phpUploadMax = (string) ini_get('upload_max_filesize');
            $phpPostMax = (string) ini_get('post_max_size');
            $msg = 'Файл не был получен сервером. Проверь лимиты PHP и веб-сервера. '
                .'Лимиты PHP: upload_max_filesize='.$phpUploadMax.', post_max_size='.$phpPostMax.'.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return $this->redirectToCompetitionShow($competition)->with('error', $msg);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $action === 'attach_document'
                    ? 'Документ прикреплён.'
                    : 'Допуск к соревнованию сохранён.',
                'medical_admission_document' => [
                    'has' => filled($competition->medical_admission_document_path),
                    'url' => filled($competition->medical_admission_document_path)
                        ? asset('storage/'.$competition->medical_admission_document_path)
                        : null,
                ],
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', $action === 'attach_document'
                ? 'Документ прикреплён.'
                : 'Допуск к соревнованию сохранён.');
    }

    public function storeFormType(Request $request, Competition $competition)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $competition->updateStatusIfNeeded();
        $competition->refresh();

        if (! $competition->formsAreEditable()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Нельзя создавать виды формы для завершённого или отменённого соревнования.',
                ], 422);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Нельзя создавать виды формы для завершённого или отменённого соревнования.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($validated['name']);
        if ($name === '') {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Название вида формы не может быть пустым.',
                ], 422);
            }
            return $this->redirectToCompetitionShow($competition)->with('error', 'Название вида формы не может быть пустым.');
        }

        $type = CompetitionFormType::firstOrCreate(['name' => $name]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Вид формы создан.',
                'type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                ],
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Вид формы создан.');
    }

    /**
     * Remove a participant from the competition.
     */
    public function removeParticipant(Request $request, Competition $competition, User $user)
    {
        $participant = CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Участник не найден в этом соревновании.'], 404);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Участник не найден в этом соревновании.');
        }

        $isStudent = ($participant->role ?? 'student') === 'student';
        $userId = (int) $user->id;

        CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->delete();

        if ($isStudent) {
            CompetitionForm::query()
                ->where('competition_id', $competition->id)
                ->where('user_id', $userId)
                ->delete();

            // Если студент был автоматически добавлен в команду из соревнования — убираем из команды.
            $competition->loadMissing('team');
            $teamId = null;
            if (($competition->result_type ?? 'team') === 'personal') {
                $teamId = $participant->team_id ? (int) $participant->team_id : null;
            } else {
                $teamId = $competition->team_id ? (int) $competition->team_id : null;
            }

            if ($teamId) {
                // Автоматически добавленных из соревнования не сохраняем в истории команды:
                // если убрали из соревнования (в т.ч. по ошибке) — просто удаляем строку.
                \App\Models\TeamMember::query()
                    ->where('team_id', $teamId)
                    ->where('user_id', $userId)
                    ->whereNull('out')
                    ->where('added_via', 'competition')
                    ->delete();
            }
        }

        if ($request->expectsJson()) {
            $count = CompetitionParticipant::query()->where('competition_id', $competition->id)->count();
            $studentCount = CompetitionParticipant::query()
                ->where('competition_id', $competition->id)
                ->where(function ($q) {
                    $q->where('role', 'student')->orWhereNull('role');
                })
                ->count();

            return response()->json([
                'ok' => true,
                'message' => 'Участник успешно удален из соревнования!',
                'user_id' => $userId,
                'is_student' => $isStudent,
                'count' => $count,
                'student_count' => $studentCount,
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Участник успешно удален из соревнования!');
    }

    /**
     * AJAX поиск студентов в LDAP (для добавления участников-студентов).
     */
    public function searchStudents(Request $request, Competition $competition)
    {
        $search = trim($request->input('search', ''));
        
        if (mb_strlen($search, 'UTF-8') < 2) {
            return response()->json(['students' => []]);
        }

        try {
            if (!$competition->relationLoaded('participants')) {
                $competition->load('participants');
            }

            $participantIds = $competition->participants->pluck('user_id')->toArray();
            $searchWords = array_filter(preg_split('/\s+/u', mb_strtolower($search, 'UTF-8')), function ($word) {
                return $word !== '';
            });

            if (empty($searchWords)) {
                return response()->json(['students' => []]);
            }

            $ldapUsers = collect();

            $ldapUsers = collect();

            try {
                $firstWord = $searchWords[0];
                $ldapUsers = LdapUser::query()
                    ->whereContains('cn', $firstWord)
                    ->orWhereContains('name', $firstWord)
                    ->orWhereContains('displayname', $firstWord)
                    ->limit(200)
                    ->get();
            } catch (\Throwable $e) {
                \Log::error('LDAP search error', [
                    'search' => $search,
                    'error' => $e->getMessage(),
                ]);
            }

            $students = [];

            foreach ($ldapUsers as $ldapUser) {
                $dnOriginal = $ldapUser->getDn() ?? '';
                $dn = mb_strtolower($dnOriginal, 'UTF-8');
                
                // Получаем CN для проверки наличия (ДО), (ДПО) и т.д. в имени
                $cn = $this->getFirstAttribute($ldapUser, ['cn', 'name', 'displayname']) ?? '';
                $cnLower = mb_strtolower($cn, 'UTF-8');
                
                // Исключаем всех пользователей из ДО, ДПО, ОТМ, ПО (и студентов, и преподавателей)
                // Проверяем:
                // 1. Наличие OU ДО, ДПО, ОТМ, ПО в DN
                // 2. Наличие (ДО), (ДПО), (ОТМ), (ПО) в CN
                $isExcluded = $this->isInExcludedOu($dnOriginal) || 
                             $this->isInExcludedOu($dn) ||
                             $this->isInExcludedOuSimple($dnOriginal) ||
                             $this->isInExcludedOuSimple($dn) ||
                             $this->hasExcludedMarkerInCn($cn) ||
                             $this->hasExcludedMarkerInCn($cnLower);
                
                if ($isExcluded) {
                    \Log::info('LDAP User excluded - in excluded OU/CN', [
                        'login' => $ldapUser->getFirstAttribute('samaccountname'),
                        'dn' => $dnOriginal,
                        'cn' => $cn,
                    ]);
                    continue;
                }
                
                // Проверяем, что пользователь находится в OU "Студенты" или "Преподаватели" по DN
                $isInStudentsOu = false;
                $isInTeachersOu = false;
                
                // Проверяем DN на наличие OU=Студенты (без ДО, ДПО, ПО, ОТМ)
                if (preg_match('/ou\s*=\s*([^,]+)/i', $dnOriginal, $matches)) {
                    $ouName = mb_strtolower(trim($matches[1]), 'UTF-8');
                    
                    // Проверяем, что это OU "Студенты" или содержит "студенты"
                    if (stripos($ouName, 'студенты') !== false || stripos($ouName, 'students') !== false) {
                        // Проверяем, что не содержит ДО, ДПО, ПО, ОТМ
                        if (stripos($ouName, 'до') === false && 
                            stripos($ouName, 'дпо') === false && 
                            stripos($ouName, 'по') === false && 
                            stripos($ouName, 'отм') === false) {
                            $isInStudentsOu = true;
                        }
                    }
                    
                    // Проверяем, что это OU "Преподаватели" или содержит "преподаватели"
                    if (stripos($ouName, 'преподаватели') !== false || 
                        stripos($ouName, 'teachers') !== false ||
                        stripos($ouName, 'преподаватель') !== false ||
                        stripos($ouName, 'teacher') !== false) {
                        $isInTeachersOu = true;
                    }
                }
                
                // Также проверяем все OU в DN (может быть несколько)
                if (preg_match_all('/ou\s*=\s*([^,]+)/i', $dnOriginal, $allMatches)) {
                    foreach ($allMatches[1] as $ouName) {
                        $ouNameLower = mb_strtolower(trim($ouName), 'UTF-8');
                        
                        // Проверяем OU "Студенты"
                        if ((stripos($ouNameLower, 'студенты') !== false || stripos($ouNameLower, 'students') !== false) &&
                            stripos($ouNameLower, 'до') === false && 
                            stripos($ouNameLower, 'дпо') === false && 
                            stripos($ouNameLower, 'по') === false && 
                            stripos($ouNameLower, 'отм') === false) {
                            $isInStudentsOu = true;
                        }
                        
                        // Проверяем OU "Преподаватели"
                        if (stripos($ouNameLower, 'преподаватели') !== false || 
                            stripos($ouNameLower, 'teachers') !== false ||
                            stripos($ouNameLower, 'преподаватель') !== false ||
                            stripos($ouNameLower, 'teacher') !== false) {
                            $isInTeachersOu = true;
                        }
                    }
                }
                
                // Если пользователь не попал в OU, пробуем определить роль по группе/атрибутам,
                // т.к. у преподавателей OU может отличаться.
                $dnForGroup = $ldapUser->getDn() ?? '';
                $resolveGroupNameResult = $this->resolveGroupName($ldapUser);
                $extractGroupFromDnResult = $this->extractGroupFromDn($dnForGroup);
                $groupNameCandidate = $resolveGroupNameResult ?: $extractGroupFromDnResult;
                $groupNameLowerCandidate = mb_strtolower((string) $groupNameCandidate, 'UTF-8');

                if (!$isInStudentsOu && !$isInTeachersOu) {
                    if (str_contains($groupNameLowerCandidate, 'преподав')) {
                        $isInTeachersOu = true;
                    } else {
                        \Log::info('LDAP User excluded - not in Students/Teachers OU', [
                            'login' => $ldapUser->getFirstAttribute('samaccountname'),
                            'dn' => $dnOriginal,
                            'isInStudentsOu' => $isInStudentsOu,
                            'isInTeachersOu' => $isInTeachersOu,
                            'group_name_candidate' => $groupNameCandidate,
                        ]);
                        continue;
                    }
                }
                
                // Определяем роль на основе OU
                $role = null;
                if ($isInStudentsOu) {
                    $role = 'student';
                } elseif ($isInTeachersOu) {
                    $role = 'teacher';
                }
                
                if (!$role) {
                    continue;
                }
                
                // Для студентов: дополнительно проверяем разрешенные OU (кроме уже исключенных ДО, ДПО, ОТМ, ПО)
                if ($role === 'student') {
                    // Если студент не в разрешенных OU - пропускаем
                    if (!$this->isUserInAllowedOu($ldapUser)) {
                        \Log::info('LDAP Student excluded - not in allowed OU', [
                            'login' => $ldapUser->getFirstAttribute('samaccountname'),
                            'dn' => $dnOriginal,
                        ]);
                        continue;
                    }
                }

                $firstname = $this->getFirstAttribute($ldapUser, ['givenname', 'givenName', 'gn']);
                $lastname = $this->getFirstAttribute($ldapUser, ['sn', 'surname']);
                $patronymic = $this->getFirstAttribute($ldapUser, ['middlename', 'initials']);
                $commonName = $this->getFirstAttribute($ldapUser, ['cn', 'name', 'displayname']);
                [$firstname, $lastname, $patronymic] = $this->normalizeLdapNames($firstname, $lastname, $patronymic, $commonName);
                $login = $this->getFirstAttribute($ldapUser, ['samaccountname']);

                if (!$firstname || !$lastname || !$login) {
                    continue;
                }

                $firstnameLower = mb_strtolower($firstname, 'UTF-8');
                $lastnameLower = mb_strtolower($lastname, 'UTF-8');
                $patronymicLower = $patronymic ? mb_strtolower($patronymic, 'UTF-8') : '';
                $cnLower = mb_strtolower($commonName ?? '', 'UTF-8');
                $composedCnLower = mb_strtolower(trim(implode(' ', array_filter([$lastname, $firstname, $patronymic]))), 'UTF-8');

                $matches = true;
                foreach ($searchWords as $word) {
                    if (
                        !str_contains($firstnameLower, $word) &&
                        !str_contains($lastnameLower, $word) &&
                        !str_contains($patronymicLower, $word) &&
                        !str_contains($cnLower, $word) &&
                        !str_contains($composedCnLower, $word)
                    ) {
                        $matches = false;
                        break;
                    }
                }

                if (!$matches) {
                    continue;
                }

                $user = User::where('login', $login)->first();
                if ($user && in_array($user->id, $participantIds)) {
                    continue;
                }

                // Дополнительная проверка: исключаем всех из ДО, ДПО, ОТМ, ПО (на всякий случай)
                $dnCheck = $ldapUser->getDn() ?? '';
                if ($this->isInExcludedOu($dnCheck) || 
                    $this->isInExcludedOu(strtolower($dnCheck)) ||
                    $this->isInExcludedOuSimple($dnCheck) ||
                    $this->isInExcludedOuSimple(strtolower($dnCheck))) {
                    \Log::info('LDAP User excluded - in excluded OU', [
                        'login' => $login,
                        'dn' => $dnCheck,
                    ]);
                    continue;
                }

                $dn = $dnForGroup;
                $groupName = $groupNameCandidate;
                
                // Для всех преподавателей устанавливаем группу "Преподаватели"
                // чтобы объединить основные, кандидатов и внешнее совместительство в одну группу
                if ($role === 'teacher') {
                    $groupName = 'Преподаватели';
                }
                
                // Логируем информацию о группе для отладки
                \Log::info('LDAP Group Resolution', [
                    'search' => $search,
                    'login' => $login,
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'dn' => $dn,
                    'cn' => $cn,
                    'role' => $role,
                    'resolveGroupName_result' => $resolveGroupNameResult,
                    'extractGroupFromDnResult' => $extractGroupFromDnResult,
                    'final_group_name' => $groupName,
                    'ldap_group_attribute' => env('LDAP_GROUP_NAME_ATTRIBUTE', ''),
                    'all_attributes' => $ldapUser->getAttributes(),
                ]);

                $students[] = [
                    'id' => $user ? $user->id : null,
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'patronymic' => $patronymic,
                    'login' => $login,
                    'group_name' => $groupName,
                    'role' => $role,
                    'status_fizorg' => $user ? (bool) $user->status_fizorg : false,
                    'dn' => $dn,
                ];
            }

            // Сортируем по фамилии и имени
            usort($students, function($a, $b) {
                $cmp = strcmp($a['lastname'], $b['lastname']);
                if ($cmp === 0) {
                    $cmp = strcmp($a['firstname'], $b['firstname']);
                }
                return $cmp;
            });

            // По умолчанию возвращаем только студентов. Для преподавателей есть отдельный поиск.
            // Если запрос приходит из searchTeachers — нужны все (фильтрует вызывающий метод).
            $forTeachers = (bool) $request->input('_for_teachers', false);
            if ($forTeachers) {
                return response()->json(['students' => $students]);
            }

            $onlyStudents = array_values(array_filter($students, fn ($row) => ($row['role'] ?? null) === 'student'));

            return response()->json(['students' => $onlyStudents]);

        } catch (\Throwable $e) {
            \Log::error('Ошибка поиска студентов в AD', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'search' => $search,
                'competition_id' => $competition->id,
            ]);

            return response()->json([
                'students' => [], 
                'error' => 'Ошибка поиска в Active Directory'
            ], 500);
        }
    }

    /**
     * AJAX поиск преподавателей в LDAP (для назначения ответственного преподавателя).
     */
    public function searchTeachers(Request $request, Competition $competition)
    {
        $search = trim($request->input('search', ''));

        if (mb_strlen($search, 'UTF-8') < 2) {
            return response()->json(['teachers' => []]);
        }

        // Переиспользуем ту же логику поиска, передавая флаг чтобы получить всех (не только студентов).
        $cloned = $request->duplicate(
            array_merge($request->query(), ['_for_teachers' => '1']),
            $request->request->all()
        );
        $result = $this->searchStudents($cloned, $competition);
        $data = $result->getData(true);
        $rows = $data['students'] ?? [];
        $teachers = array_values(array_filter($rows, fn ($row) => ($row['role'] ?? null) === 'teacher'));

        return response()->json(['teachers' => $teachers]);
    }

    /**
     * Назначить/изменить ответственного преподавателя (один на соревнование).
     * Преподаватель хранится в competition_teachers и также добавляется в participants как role=teacher.
     */
    public function updateResponsibleTeacher(Request $request, Competition $competition)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        // Можно менять только у предстоящих
        $competition->updateStatusIfNeeded();
        $competition->refresh();
        if ($competition->status !== 'upcoming') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Ответственного преподавателя можно менять только у предстоящих соревнований.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Ответственного преподавателя можно менять только у предстоящих соревнований.');
        }

        $validated = $request->validate([
            'student_data' => 'required|string',
        ], [
            'student_data.required' => 'Выберите преподавателя из списка.',
        ]);

        $teacherData = json_decode($validated['student_data'], true);
        if (!is_array($teacherData) || empty($teacherData['login'])) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Не удалось определить выбранного преподавателя.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->withErrors(['student_data' => 'Не удалось определить выбранного преподавателя.'])
                ->withInput();
        }

        $firstname = trim($teacherData['firstname'] ?? '');
        $lastname = trim($teacherData['lastname'] ?? '');
        $patronymic = trim($teacherData['patronymic'] ?? '');
        $login = trim($teacherData['login']);
        $groupName = 'Преподаватели';

        if ($firstname === '' || $lastname === '' || $login === '') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'У выбранного преподавателя отсутствуют необходимые данные.'], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->withErrors(['student_data' => 'У выбранного преподавателя отсутствуют необходимые данные.'])
                ->withInput();
        }

        $user = User::where('login', $login)->first();
        if (!$user) {
            $user = User::create([
                'login' => $login,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'role' => 'teacher',
                'group_name' => $groupName,
                'active' => true,
            ]);
        } else {
            $user->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'role' => 'teacher',
                'group_name' => $groupName,
            ]);
        }

        // Старый ответственный преподаватель
        $competition->loadMissing('teacher');
        $prevTeacherId = (int) ($competition->teacher?->user_id ?? 0);

        // Обновляем ответственного преподавателя
        $competition->teacher()->updateOrCreate(
            ['competition_id' => $competition->id],
            ['user_id' => $user->id]
        );

        // Синхронизируем participants: удаляем старого преподавателя, добавляем нового (если нет)
        if ($prevTeacherId > 0 && $prevTeacherId !== (int) $user->id) {
            CompetitionParticipant::query()
                ->where('competition_id', $competition->id)
                ->where('user_id', $prevTeacherId)
                ->where('role', 'teacher')
                ->delete();
        }

        $existingTeacherParticipant = CompetitionParticipant::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$existingTeacherParticipant) {
            CompetitionParticipant::create([
                'competition_id' => $competition->id,
                'user_id' => $user->id,
                'team_id' => null,
                'role' => 'teacher',
            ]);
        } else {
            $existingTeacherParticipant->update([
                'role' => 'teacher',
            ]);
        }

        $competition->loadMissing('teacher.user');

        if ($request->expectsJson()) {
            $teacherUser = $competition->teacher?->user;
            return response()->json([
                'ok' => true,
                'message' => 'Ответственный преподаватель сохранён.',
                'teacher_id' => $teacherUser?->id,
                'teacher_label' => $teacherUser
                    ? trim($teacherUser->lastname.' '.$teacherUser->firstname.' '.($teacherUser->patronymic ?? ''))
                    : null,
                'teacher_html' => view('competitions.partials.teacher-block', [
                    'competition' => $competition,
                ])->render(),
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Ответственный преподаватель сохранён.');
    }

    private function getFirstAttribute(LdapUser $user, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $user->getFirstAttribute($key);
            if ($value) {
                return $this->sanitizeLdapString($value);
            }
        }
        return null;
    }

    private function normalizeLdapNames(?string $firstname, ?string $lastname, ?string $patronymic, ?string $commonName): array
    {
        $first = $firstname ? trim($firstname) : null;
        $last = $lastname ? trim($lastname) : null;
        $middle = $patronymic ? trim($patronymic) : null;
        $cn = $commonName ? trim($commonName) : null;

        if ($first && str_contains($first, ' ')) {
            $parts = array_values(array_filter(preg_split('/\s+/u', $first) ?: []));
            if (!empty($parts)) {
                $first = array_shift($parts);
                if (!$middle && !empty($parts)) {
                    $middle = implode(' ', $parts);
                }
            }
        }

        if ((!$first || !$last) && $cn) {
            $this->fillNamesFromCommonName($first, $last, $middle, $cn);
        } elseif (!$middle && $cn) {
            $this->fillMiddleNameFromCommonName($middle, $cn);
        }

        return [$first, $last, $middle];
    }

    private function fillNamesFromCommonName(?string &$firstname, ?string &$lastname, ?string &$patronymic, ?string $commonName): void
    {
        if (!$commonName) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($commonName)) ?: []));
        if (empty($parts)) {
            return;
        }

        if (!$lastname) {
            $lastname = array_shift($parts);
        }

        if (!$firstname && !empty($parts)) {
            $firstname = array_shift($parts);
        }

        if (!$patronymic && !empty($parts)) {
            $patronymic = implode(' ', $parts);
        }
    }

    private function fillMiddleNameFromCommonName(?string &$patronymic, ?string $commonName): void
    {
        if (!$commonName || $patronymic) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($commonName)) ?: []));
        if (count($parts) >= 3) {
            $patronymic = implode(' ', array_slice($parts, 2));
        }
    }

    private function sanitizeLdapString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace(["\xC2\xA0", "\xE2\x80\xAF"], ' ', $value);
        $normalized = preg_replace('/\s+/u', ' ', $normalized ?? '') ?? '';
        $normalized = trim($normalized);

        return $normalized === '' ? null : $normalized;
    }

    private function extractGroupFromDn(?string $dn): ?string
    {
        if (!$dn) {
            return null;
        }

        if (preg_match('/OU=([^,]+)/i', $dn, $matches)) {
            $value = trim($matches[1]);
            return $value !== '' ? $value : null;
        }

        return null;
    }

    private function getAllowedOus(): array
    {
        $value = env('LDAP_ALLOWED_OUS', 'Студенты|ДО|ДПО|ОТМ|ПО');
        if (!$value) {
            return [];
        }

        $normalized = str_replace(';', ',', $value);
        $parts = array_filter(array_map('trim', explode(',', $normalized)));

        return array_map(function ($part) {
            $segments = array_map('trim', explode('|', $part));
            $segments = array_map(function ($segment) {
                if ($segment === '') {
                    return '';
                }
                if (stripos($segment, 'ou=') === 0 || stripos($segment, 'dc=') === 0) {
                    return $segment;
                }
                return 'OU=' . $segment;
            }, $segments);
            return implode('|', $segments);
        }, $parts);
    }

    private function isUserInAllowedOu(LdapUser $user): bool
    {
        $dn = strtolower($user->getDn() ?? '');
        $allowed = $this->getAllowedOus();

        if (empty($allowed) || !$dn) {
            return true;
        }

        foreach ($allowed as $ou) {
            $ouLower = strtolower($ou);

            $parts = explode('|', $ouLower);
            $ouName = trim($parts[0]);
            $excludes = array_filter(array_map('trim', array_slice($parts, 1)));

            if ($ouName === '' || !str_contains($dn, $ouName)) {
                continue;
            }

            $excludeMatch = false;
            foreach ($excludes as $exclude) {
                if ($exclude !== '' && str_contains($dn, $exclude)) {
                    $excludeMatch = true;
                    break;
                }
            }

            if (!$excludeMatch) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет, находится ли DN в исключенных OU (ДО, ДПО, ОТМ, ПО)
     */
    private function isInExcludedOu(string $dn): bool
    {
        if (empty($dn)) {
            return false;
        }

        // Исключаемые OU - проверяем все возможные варианты регистра
        $excludedOus = ['до', 'ДО', 'дпо', 'ДПО', 'отм', 'ОТМ', 'по', 'ПО'];
        
        // Приводим DN к нижнему регистру для универсальной проверки
        $dnLower = mb_strtolower($dn, 'UTF-8');
        
        // Проверяем наличие каждого исключенного OU в DN
        foreach ($excludedOus as $excludedOu) {
            $excludedOuLower = mb_strtolower($excludedOu, 'UTF-8');
            
            // Ищем "ou=до", "ou=дпо" и т.д. в DN (без учета регистра)
            // Проверяем различные варианты написания
            if (preg_match('/ou\s*=\s*' . preg_quote($excludedOuLower, '/') . '(?=[,\s]|$)/i', $dn) ||
                preg_match('/ou\s*=\s*' . preg_quote($excludedOu, '/') . '(?=[,\s]|$)/i', $dn) ||
                stripos($dn, 'ou=' . $excludedOuLower) !== false ||
                stripos($dn, 'ou=' . $excludedOu) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Простая проверка наличия исключенных OU в DN (более надежный метод)
     */
    private function isInExcludedOuSimple(string $dn): bool
    {
        if (empty($dn)) {
            return false;
        }

        // Исключаемые OU в разных вариантах регистра
        $excludedOus = ['до', 'ДО', 'дпо', 'ДПО', 'отм', 'ОТМ', 'по', 'ПО'];
        
        // Приводим DN к нижнему регистру для универсальной проверки
        $dnLower = mb_strtolower($dn, 'UTF-8');
        
        // Просто проверяем наличие строки в DN
        // Ищем "ou=до", "ou=дпо" и т.д. в любом месте DN
        foreach ($excludedOus as $excludedOu) {
            $excludedOuLower = mb_strtolower($excludedOu, 'UTF-8');
            
            // Проверяем различные варианты: "ou=до", "OU=ДО", "ou = до" и т.д.
            // Используем простой поиск подстроки для надежности
            if (stripos($dnLower, 'ou=' . $excludedOuLower) !== false ||
                stripos($dnLower, 'ou =' . $excludedOuLower) !== false ||
                stripos($dnLower, 'ou= ' . $excludedOuLower) !== false ||
                stripos($dnLower, 'ou = ' . $excludedOuLower) !== false) {
                return true;
            }
            
            // Также проверяем с регулярным выражением для более точного поиска
            if (preg_match('/ou\s*=\s*' . preg_quote($excludedOuLower, '/') . '(?=[,\s]|$)/i', $dn)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет, есть ли в CN маркеры исключения: (ДО), (ДПО), (ОТМ), (ПО)
     */
    private function hasExcludedMarkerInCn(string $cn): bool
    {
        if (empty($cn)) {
            return false;
        }

        // Исключаемые маркеры в CN
        $excludedMarkers = ['(до)', '(дпо)', '(отм)', '(по)', '(ДО)', '(ДПО)', '(ОТМ)', '(ПО)'];
        
        foreach ($excludedMarkers as $marker) {
            if (stripos($cn, $marker) !== false) {
                return true;
            }
        }

        return false;
    }

    private function determineRole(LdapUser $user): string
    {
        $dn = (string) $user->getDn();
        $memberOf = (array) $user->getAttribute('memberof', []);

        if ($this->hasAnyKeyword([$dn], $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_TEACHER', ['teacher', 'преподаватель'])) ||
            $this->hasAnyKeyword($memberOf, $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_TEACHER', ['teachers', 'преподаватели']))) {
            return 'teacher';
        }

        if ($this->hasAnyKeyword([$dn], $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_STUDENT', ['student', 'студент'])) ||
            $this->hasAnyKeyword($memberOf, $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_STUDENT', ['students', 'студенты']))) {
            return 'student';
        }

        return env('LDAP_DEFAULT_ROLE', 'student');
    }

    private function getRoleKeywords(string $envKey, array $defaults): array
    {
        $value = env($envKey, '');
        if (empty($value)) {
            return $defaults;
        }
        return array_map('strtolower', array_map('trim', explode(',', $value)));
    }

    private function hasAnyKeyword(array $strings, array $keywords): bool
    {
        foreach ($strings as $string) {
            foreach ($keywords as $keyword) {
                if (str_contains(strtolower($string), strtolower($keyword))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function resolveGroupName(LdapUser $user): ?string
    {
        $groupAttribute = env('LDAP_GROUP_NAME_ATTRIBUTE', '');
        if (empty($groupAttribute)) {
            return null;
        }

        $value = $user->getFirstAttribute($groupAttribute);
        return $value ? trim($value) : null;
    }

    /**
     * Generate order type 1: Об освобождении от учебных занятий
     */
    public function generateOrder1(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'accompanying_teacher' => 'required|exists:users,id',
            'dispatcher' => 'required|string|max:200',
            'deputy_director' => 'required|string|max:200',
            'director_name' => 'nullable|string|max:200',
        ], [
            'accompanying_teacher.required' => 'Нужно выбрать преподавателя.',
            'accompanying_teacher.exists' => 'Нужно выбрать преподавателя.',
        ]);

        // Загружаем все необходимые связи из базы данных
        $competition->load(['sport', 'team', 'location', 'participants.user']);

        // Получаем студентов из таблицы participants соревнования
        $students = $competition->participants->where('role', 'student')->sortBy(function($participant) {
            return $participant->user->lastname . ' ' . $participant->user->firstname;
        })->values();

        // Формируем полный адрес из данных локации в формате: "в г. ... по адресу: ..."
        $locationFull = '';
        if ($competition->location) {
            $locationText = $competition->location->location ?? '';
            $organizerText = $competition->location->organizer ?? '';
            
            if ($locationText) {
                // Если локация содержит "город" или "г." — склоняем в предложный: "в г. Ангарске"
                if (stripos($locationText, 'город') !== false || stripos($locationText, 'г.') !== false) {
                    $locationFull = $this->ruLocationToPrepositionalPhrase($locationText);
                } else {
                    // Иначе добавляем "в городе" или "по адресу:" в зависимости от формата
                    if (stripos($locationText, 'ул.') !== false || stripos($locationText, 'улица') !== false || stripos($locationText, 'адрес') !== false) {
                        $locationFull = 'по адресу: ' . $locationText;
                    } else {
                        $locationFull = 'в городе ' . $this->ruPrepositionalPlace($locationText);
                    }
                }
                
                if ($organizerText) {
                    if (stripos($locationText, 'адрес') === false && stripos($locationText, 'ул.') === false) {
                        $locationFull .= ' по адресу: ' . $organizerText;
                    } else {
                        $locationFull .= ', ' . $organizerText;
                    }
                }
            } else {
                $locationFull = $organizerText ? 'по адресу: ' . $organizerText : 'Не указана';
            }
        } else {
            $locationFull = 'Не указана';
        }

        $orderData = [
            'date' => \Carbon\Carbon::parse($validated['order_date'])->format('d.m.Y'),
            // Данные из таблицы competitions
            'competition_name' => $competition->name,
            'competition_description' => trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($competition->description ?? $competition->name)))) ?: $competition->name,
            'start_date' => $competition->start_date->format('d.m.Y'),
            'end_date' => $competition->end_date->format('d.m.Y'),
            // Данные из таблицы sports через связь
            'sport_name' => $competition->sport->name ?? '',
            'team_name' => $competition->team->name ?? '',
            // Данные из таблицы locations через связь
            'location' => $competition->location->location ?? 'Не указана',
            'location_address' => $competition->location->organizer ?? '',
            'location_full' => $locationFull,
            // Данные из таблицы competition_participants и users
            'students' => $students,
            'students_count' => $students->count(),
            // Получаем преподавателя из участников
            'accompanying_teacher' => $this->getTeacherName($competition, $validated['accompanying_teacher']),
            'dispatcher' => $validated['dispatcher'],
            'deputy_director' => $validated['deputy_director'],
            'director_name' => $validated['director_name'] ?? 'А.Н. Якубовский',
        ];

        try {
            // Генерируем PDF документ
            $pdf = Pdf::loadView('competitions.order-1-pdf', $orderData);
            $pdf->setPaper('a4', 'portrait');
            
            $filename = 'prikaz_osvobozhdenie_' . $competition->id . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании PDF документа: ' . $e->getMessage());
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Ошибка при создании документа: ' . $e->getMessage());
        }
    }

    /**
     * Get teacher name from competition participants
     */
    private function getTeacherName(Competition $competition, int $userId): string
    {
        $competition->loadMissing('teacher.user');
        $teacher = $competition->teacher?->user;
        if (! $teacher || (int) $teacher->id !== (int) $userId) {
            return 'Не указан';
        }

        return $teacher->lastname . ' ' . mb_substr($teacher->firstname, 0, 1) . '.' . ($teacher->patronymic ? mb_substr($teacher->patronymic, 0, 1) . '.' : '');
    }

    /**
     * Generate order type 2: Об участии в мероприятии
     */
    public function generateOrder2(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'head_of_studies' => 'required|string|max:200',
            'deputy_director' => 'required|string|max:200',
            'director_name' => 'required|string|max:200',
        ]);

        // Загружаем все необходимые связи из базы данных
        $competition->load(['sport', 'team', 'location', 'participants.user', 'teacher.user']);

        $teacher = $competition->teacher?->user;
        if (! $teacher) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'В соревновании не назначен преподаватель.');
        }

        // Формируем полный адрес из данных локации в формате: "в городе ... по адресу: ..."
        $locationFull = '';
        if ($competition->location) {
            $locationText = $competition->location->location ?? '';
            $organizerText = $competition->location->organizer ?? '';
            
            if ($locationText) {
                // Если локация содержит "город" или "г.", начинаем с "в"
                if (stripos($locationText, 'город') !== false || stripos($locationText, 'г.') !== false) {
                    $locationFull = $locationText;
                } else {
                    // Иначе добавляем "в городе" или "по адресу:" в зависимости от формата
                    if (stripos($locationText, 'ул.') !== false || stripos($locationText, 'улица') !== false || stripos($locationText, 'адрес') !== false) {
                        $locationFull = 'по адресу: ' . $locationText;
                    } else {
                        $locationFull = 'в городе ' . $locationText;
                    }
                }
                
                if ($organizerText) {
                    if (stripos($locationText, 'адрес') === false && stripos($locationText, 'ул.') === false) {
                        $locationFull .= ' по адресу: ' . $organizerText;
                    } else {
                        $locationFull .= ', ' . $organizerText;
                    }
                }
            } else {
                $locationFull = $organizerText ? 'по адресу: ' . $organizerText : 'Не указана';
            }
        } else {
            $locationFull = 'Не указана';
        }

        $orderData = [
            'date' => \Carbon\Carbon::parse($validated['order_date'])->format('d.m.Y'),
            // Данные из таблицы competitions
            'competition_name' => $competition->name,
            'start_date' => $competition->start_date->format('d.m.Y'),
            'end_date' => $competition->end_date->format('d.m.Y'),
            // Данные из таблицы sports через связь
            'sport_name' => $competition->sport->name ?? '',
            'team_name' => $competition->team->name ?? '',
            // Данные из таблицы locations через связь
            'location' => $competition->location->location ?? 'Не указана',
            'location_address' => $competition->location->organizer ?? '',
            'location_full' => $locationFull,
            // Данные из таблицы users через competition_participants
            'teacher_name' => $teacher->lastname . ' ' . mb_substr($teacher->firstname, 0, 1) . '.' . ($teacher->patronymic ? mb_substr($teacher->patronymic, 0, 1) . '.' : ''),
            'teacher_full_name' => $teacher->lastname . ' ' . $teacher->firstname . ' ' . ($teacher->patronymic ?? ''),
            // Дополнительные данные из формы
            'head_of_studies' => $validated['head_of_studies'],
            'deputy_director' => $validated['deputy_director'],
            'director_name' => $validated['director_name'],
        ];

        try {
            // Генерируем PDF документ
            $pdf = Pdf::loadView('competitions.order-2-pdf', $orderData);
            $pdf->setPaper('a4', 'portrait');
            
            $filename = 'prikaz_uchastie_' . $competition->id . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании приказа 2: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ошибка при создании документа: ' . $e->getMessage()]);
        }
    }

    /**
     * Именная заявка на участие в соревновании (PDF).
     */
    public function generateNamedApplication(Request $request, Competition $competition)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $validated = $request->validate([
            'application_date' => 'nullable|date',
        ]);

        $competition->load(['sport', 'participants.user', 'forms']);

        $students = $competition->participants
            ->filter(fn ($p) => ($p->role ?? 'student') === 'student')
            ->sortBy(fn ($p) => ($p->user->lastname ?? '').' '.($p->user->firstname ?? ''))
            ->values();

        $formsByUserId = $competition->forms->keyBy('user_id');

        $tableRows = [];
        $index = 1;
        foreach ($students as $participant) {
            $user = $participant->user;
            $form = $formsByUserId->get($participant->user_id);
            $tableRows[] = [
                'index' => $index++,
                'full_name' => trim(($user->lastname ?? '').' '.($user->firstname ?? '').' '.($user->patronymic ?? '')),
                'birth_date' => '',
                'student_card' => '',
            ];
        }

        $studentsCount = $students->count();
        $applicationDate = isset($validated['application_date'])
            ? \Carbon\Carbon::parse($validated['application_date'])
            : now();

        $data = [
            'competition_name' => $competition->name,
            'sport_name' => $this->ruSportPoPhrase($competition->sport->name ?? ''),
            'institution_name' => 'Иркутского авиационного техникума',
            'table_rows' => $tableRows,
            'students_count' => $studentsCount,
            'people_label' => $this->ruPeopleLabel($studentsCount),
            'participants_label' => $this->ruParticipantsLabel($studentsCount),
            'document_date_line' => '«'.$applicationDate->format('d').'» '.$applicationDate->translatedFormat('F').' '.$applicationDate->format('Y').' г.',
        ];

        try {
            $pdf = Pdf::loadView('competitions.named-application-pdf', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'imennaya_zayavka_'.$competition->id.'_'.now()->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании именной заявки: '.$e->getMessage());

            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Ошибка при создании документа: '.$e->getMessage());
        }
    }

    private function ruPeopleLabel(int $count): string
    {
        $n = abs($count) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return 'человек';
        }
        if ($n1 === 1) {
            return 'человек';
        }
        if ($n1 >= 2 && $n1 <= 4) {
            return 'человека';
        }

        return 'человек';
    }

    private function ruParticipantsLabel(int $count): string
    {
        $n = abs($count) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return 'участников';
        }
        if ($n1 === 1) {
            return 'участник';
        }
        if ($n1 >= 2 && $n1 <= 4) {
            return 'участника';
        }

        return 'участников';
    }

    private function ruSportPoPhrase(string $sportName): string
    {
        $sportName = trim($sportName);
        if ($sportName === '') {
            return '';
        }

        $lower = mb_strtolower($sportName, 'UTF-8');

        // Несклоняемые/устойчивые варианты
        $indeclinable = ['дзюдо', 'карате', 'самбо', 'айкидо', 'кикбоксинг'];
        if (in_array($lower, $indeclinable, true)) {
            return $sportName;
        }

        // Частые виды спорта на -бол
        $bolMap = [
            'футбол' => 'футболу',
            'волейбол' => 'волейболу',
            'баскетбол' => 'баскетболу',
            'гандбол' => 'гандболу',
            'флорбол' => 'флорболу',
        ];
        if (isset($bolMap[$lower])) {
            return $this->preserveCapitalization($sportName, $bolMap[$lower]);
        }

        // Составные названия:
        // - настольный теннис → настольному теннису
        // - лёгкая атлетика → лёгкой атлетике
        // По возможности склоняем прилагательное + существительное.
        $parts = preg_split('/\s+/u', $sportName) ?: [$sportName];
        $parts = array_values(array_filter($parts, fn ($p) => trim((string) $p) !== ''));
        if (count($parts) > 1) {
            $n = count($parts);
            $last = $parts[$n - 1];
            $prev = $parts[$n - 2];

            if ($this->looksLikeRuAdjective($prev)) {
                $parts[$n - 2] = $this->ruDativeAdjective($prev);
                $parts[$n - 1] = $this->ruDative($last);
                return implode(' ', $parts);
            }

            // fallback: склоняем только последнее слово
            $parts[$n - 1] = $this->ruDative($last);
            return implode(' ', $parts);
        }

        return $this->ruDative($sportName);
    }

    private function looksLikeRuAdjective(string $word): bool
    {
        $w = trim($word);
        if ($w === '') {
            return false;
        }

        $lower = mb_strtolower($w, 'UTF-8');

        return (bool) preg_match('/(ый|ий|ой|ая|яя|ое|ее)$/u', $lower);
    }

    private function ruDativeAdjective(string $word): string
    {
        $w = trim($word);
        if ($w === '') {
            return '';
        }

        $lower = mb_strtolower($w, 'UTF-8');

        // masculine
        if (preg_match('/(ый|ой)$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ому', 'ому');
        }
        if (preg_match('/ий$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ему', 'ему');
        }

        // feminine
        if (preg_match('/ая$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ой', 'ой');
        }
        if (preg_match('/яя$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ей', 'ей');
        }

        // neuter
        if (preg_match('/ое$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ому', 'ому');
        }
        if (preg_match('/ее$/u', $lower)) {
            return mb_substr($w, 0, -2, 'UTF-8') . $this->preserveCapitalization('ему', 'ему');
        }

        return $w;
    }

    private function ruDative(string $word): string
    {
        $w = trim($word);
        if ($w === '') return '';

        $lower = mb_strtolower($w, 'UTF-8');
        $len = mb_strlen($lower, 'UTF-8');
        $last = mb_substr($lower, -1, 1, 'UTF-8');

        // Нейтральные/средний род на -ие/-ние/-ание/-ение: плавание → плаванию
        if ($len >= 2) {
            $last2 = mb_substr($lower, -2, 2, 'UTF-8');
            if ($last2 === 'ие') {
                return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 2, 'UTF-8') . 'ию';
            }
        }
        if ($len >= 3) {
            $last3 = mb_substr($lower, -3, 3, 'UTF-8');
            if ($last3 === 'ние') {
                return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 3, 'UTF-8') . 'нию';
            }
        }
        if ($len >= 5) {
            $last5 = mb_substr($lower, -5, 5, 'UTF-8');
            if ($last5 === 'ание' || $last5 === 'ение') {
                return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8') . 'ю';
            }
        }

        // атлетика → атлетике, гимнастика → гимнастике
        if ($last === 'а') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'е';
        }

        // плавание (…ние) попало выше; прочее на -е/-о: в основном не используется для спорта, оставим как есть
        if ($last === 'е' || $last === 'о') {
            return $w;
        }

        // на -я → -е (борьбa уже в -а, но например "стрельба" тоже -а)
        if ($last === 'я') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'е';
        }

        // мягкий знак → ю (теннисный "гольф" нет, но "фехтование" тоже выше)
        if ($last === 'ь') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'ю';
        }

        // на -й → -ю (хоккей → хоккею)
        if ($last === 'й') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'ю';
        }

        // согласная → +у (теннис → теннису)
        return $w . 'у';
    }

    private function preserveCapitalization(string $original, string $replacementLower): string
    {
        // Если оригинал начался с заглавной — делаем заглавной первую букву
        $first = mb_substr($original, 0, 1, 'UTF-8');
        $isUpper = $first !== '' && $first === mb_strtoupper($first, 'UTF-8');
        if (! $isUpper) {
            return $replacementLower;
        }

        return mb_strtoupper(mb_substr($replacementLower, 0, 1, 'UTF-8'), 'UTF-8')
            .mb_substr($replacementLower, 1, null, 'UTF-8');
    }

    /**
     * "г. Ангарск" -> "в г. Ангарске"
     * "город Иркутск" -> "в городе Иркутске"
     */
    private function ruLocationToPrepositionalPhrase(string $locationText): string
    {
        $t = trim($locationText);
        if ($t === '') {
            return $t;
        }

        // Already has leading "в "
        $lower = mb_strtolower($t, 'UTF-8');
        if (str_starts_with($lower, 'в ')) {
            return $t;
        }

        // "г. X"
        if (preg_match('/^г\.\s*(.+)$/ui', $t, $m)) {
            $city = trim($m[1]);
            return 'в г. '.$this->ruPrepositionalPlace($city);
        }

        // "город X"
        if (preg_match('/^город\s+(.+)$/ui', $t, $m)) {
            $city = trim($m[1]);
            return 'в городе '.$this->ruPrepositionalPlace($city);
        }

        // Fallback
        return 'в '.$this->ruPrepositionalPlace($t);
    }

    /**
     * Very small RU предложный падеж for places:
     * Ангарск -> Ангарске, Иркутск -> Иркутске, Москва -> Москве, Тверь -> Твери.
     */
    private function ruPrepositionalPlace(string $place): string
    {
        $w = trim($place);
        if ($w === '') {
            return '';
        }

        $lower = mb_strtolower($w, 'UTF-8');
        $len = mb_strlen($lower, 'UTF-8');
        $last = mb_substr($lower, -1, 1, 'UTF-8');
        $last2 = $len >= 2 ? mb_substr($lower, -2, 2, 'UTF-8') : '';

        // -ск -> -ске (Ангарск, Иркутск)
        if ($last2 === 'ск') {
            return $w.'е';
        }

        // -а -> -е (Москва -> Москве)
        if ($last === 'а') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'е';
        }

        // -я -> -е (Толя? условно)
        if ($last === 'я') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'е';
        }

        // -ь -> -и (Тверь -> Твери)
        if ($last === 'ь') {
            return mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 1, 'UTF-8').'и';
        }

        // default: +е
        return $w.'е';
    }

    /**
     * Generate order type 3: О месте проведения занятий
     */
    public function generateOrder3(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'location_classes' => 'required|string|max:200',
        ]);

        $orderData = [
            'date' => \Carbon\Carbon::parse($validated['order_date'])->format('d.m.Y'),
            'location_classes' => $validated['location_classes'],
        ];

        try {
            // Генерируем PDF документ
            $pdf = Pdf::loadView('competitions.order-3-pdf', $orderData);
            $pdf->setPaper('a4', 'portrait');
            
            $filename = 'prikaz_mesto_zanyatii_' . $competition->id . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании приказа 3: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ошибка при создании документа: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a new result for the competition.
     */
    public function storeResult(Request $request, Competition $competition)
    {
        $competition->load(['sport', 'team', 'participants.user', 'participants.team']);

        if (($competition->result_type ?? 'team') === 'personal') {
            $validated = $request->validate([
                'results' => 'required|array',
                'results.*' => 'nullable|integer|min:1',
            ], [
                'results.*.integer' => 'Место должно быть целым числом.',
                'results.*.min' => 'Место должно быть больше 0.',
            ]);

            $studentIds = $competition->participants
                ->filter(fn ($p) => ($p->role ?? 'student') === 'student')
                ->pluck('user_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $saved = 0;
            foreach ($studentIds as $userId) {
                $place = (string) ($validated['results'][$userId] ?? $validated['results'][(string) $userId] ?? '');
                if ($place === '') {
                    CompetitionResult::query()
                        ->where('competitions_id', $competition->id)
                        ->where('user_id', $userId)
                        ->delete();
                    continue;
                }

                $participantTeamId = $competition->participants
                    ->first(fn ($p) => (int) $p->user_id === (int) $userId)?->team_id;

                CompetitionResult::query()->updateOrCreate(
                    [
                        'competitions_id' => $competition->id,
                        'user_id' => $userId,
                    ],
                    [
                        'teams_id' => $participantTeamId,
                        'place' => $place,
                        'result_type' => 'personal',
                    ]
                );
                $saved++;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Результаты сохранены.',
                    'saved' => $saved,
                ]);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('success', 'Результаты сохранены.');
        }

        if (! $competition->team_id || ! $competition->team) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У соревнования не указана команда. Невозможно добавить результат.',
                ], 422);
            }

            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'У соревнования не указана команда. Невозможно добавить результат.');
        }

        $validated = $request->validate([
            'place' => 'required|integer|min:1|max:45',
        ], [
            'place.integer' => 'Место должно быть целым числом.',
            'place.min' => 'Место должно быть больше 0.',
        ]);

        $teamId = $competition->team_id;

        // Проверяем, не добавлен ли уже результат для этой команды в этом соревновании
        $existingResult = \App\Models\CompetitionResult::where('competitions_id', $competition->id)
            ->where('teams_id', $teamId)
            ->where('result_type', 'team')
            ->first();

        if ($existingResult) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Результат для этой команды в данном соревновании уже существует.'
                ], 422);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Результат для этой команды в данном соревновании уже существует.');
        }

        $result = \App\Models\CompetitionResult::create([
            'competitions_id' => $competition->id,
            'teams_id' => $teamId,
            'place' => $validated['place'],
            'result_type' => 'team',
        ]);

        // Загружаем связи для ответа
        $competition->load('category');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Результат успешно добавлен!',
                'result' => [
                    'id' => $result->id,
                    'place' => $result->place,
                    'team_name' => $competition->team->name ?? 'Не указана',
                    'category_name' => $competition->category->name_category ?? 'Не указана',
                ]
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Результат успешно добавлен!');
    }

    /**
     * Update the specified result in storage.
     */
    public function updateResult(Request $request, Competition $competition, \App\Models\CompetitionResult $result)
    {
        // Проверяем, что результат принадлежит этому соревнованию
        if ($result->competitions_id !== $competition->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Результат не найден или не принадлежит этому соревнованию.'
                ], 404);
            }
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Результат не найден или не принадлежит этому соревнованию.');
        }

        $validated = $request->validate([
            'place' => 'required|integer|min:1|max:45',
        ], [
            'place.integer' => 'Место должно быть целым числом.',
            'place.min' => 'Место должно быть больше 0.',
        ]);

        $result->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Результат успешно обновлен!',
                'result' => [
                    'id' => $result->id,
                    'place' => $result->place,
                ]
            ]);
        }

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Результат успешно обновлен!');
    }

    /**
     * Remove the specified result from storage.
     */
    public function destroyResult(Competition $competition, \App\Models\CompetitionResult $result)
    {
        // Проверяем, что результат принадлежит этому соревнованию
        if ($result->competitions_id !== $competition->id) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Результат не найден или не принадлежит этому соревнованию.');
        }

        $result->delete();

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Результат успешно удален!');
    }

    /**
     * Страница результата соревнования (основная информация и участники).
     */
    public function showResult(Competition $competition)
    {
        $user = auth()->user();

        if ($user->role === 'teacher') {
            if (! CompetitionResultPage::guestCanView($competition)) {
                abort(404);
            }
        } elseif (! CompetitionResultPage::guestCanView($competition)) {
            abort(404);
        }

        $competition = CompetitionResultPage::loadCompetition($competition);

        return view('competitions.result-show', compact('competition'));
    }

    /**
     * Display competition results listing page.
     */
    public function results(Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user && $user->role === 'teacher';
        $userId = $user ? (int) $user->id : null;
        $archiveThreshold = now()->subMonths(self::COMPETITION_ARCHIVE_MONTHS)->startOfDay();
        $hasArchiveColumn = Schema::hasColumn('competition_results', 'is_archive');

        $listingFilters = $this->parseCompetitionListingFilters($request);
        extract($listingFilters);
        $place = $this->parseResultsPlaceFilter($request);
        $hasSearchFilters = $q !== '' || $dateFrom || $dateTo || $sportId || $categoryId !== null || $place !== '';

        $view = $request->query('view', $isTeacher ? 'list' : 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = $isTeacher ? 'list' : 'cards';
        }

        $perPage = (int) $request->query('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $cardsSortStack = StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_CARDS);
        $listSortStack = StudentCompetitionListingSort::normalizeListStack(
            StudentCompetitionListingSort::parseStack($request, StudentCompetitionListingSort::PREFIX_LIST)
        );
        $activeSortStack = $view === 'list' ? $listSortStack : $cardsSortStack;

        // Автоархивация результатов завершенных соревнований старше N месяцев (для отображения у преподавателя).
        if ($hasArchiveColumn) {
            CompetitionResult::query()
                ->where('is_archive', false)
                ->whereHas('competition', function ($q) use ($archiveThreshold) {
                    $q->where('status', 'finished')
                        ->whereDate('end_date', '<=', $archiveThreshold->toDateString());
                })
                ->update(['is_archive' => true]);
        }

        // Загрузка результатов: преподаватель и студент видят все завершённые соревнования с местами.
        $finishedWith = ['sport', 'team', 'location', 'category', 'participants.user', 'participants.team.sport'];
        if ($hasArchiveColumn) {
            $finishedWith['results'] = function ($q) {
                $q->where('is_archive', false)
                    ->whereNotNull('place')
                    ->where('place', '!=', '')
                    ->with(['team.sport', 'user']);
            };
        } else {
            $finishedWith['results'] = function ($q) {
                $q->whereNotNull('place')
                    ->where('place', '!=', '')
                    ->with(['team.sport', 'user']);
            };
        }

        $allFinishedCompetitions = Competition::with($finishedWith)
            ->where('status', 'finished')
            ->where(function ($q) use ($archiveThreshold) {
                $q->whereDate('end_date', '>', $archiveThreshold->toDateString())
                    ->orWhereDoesntHave('results', function ($r) {
                        $r->whereNotNull('place')
                            ->where('place', '!=', '');
                    });
            })
            ->latest('end_date')
            ->get();

        // Получаем завершенные соревнования только с результатами для отображения
        $allFinishedCompetitionsForDisplay = $allFinishedCompetitions->filter(function ($comp) {
            return $comp->results->isNotEmpty();
        });

        // Завершенные соревнования без результатов (без "мест") — отдельный блок только у преподавателя
        $allFinishedCompetitionsWithoutResultsForDisplay = $isTeacher
            ? $allFinishedCompetitions->filter(function ($comp) {
                return $comp->results->isEmpty();
            })
            : collect();

        $sportsForResultsFilter = CompetitionResultPage::collectSportsForCompetitionsFilter(
            $allFinishedCompetitions
        );

        // Получаем завершенные соревнования только для dropdown (без результатов и с командой)
        $competitions = $allFinishedCompetitions->filter(function($comp) {
            return $comp->results->isEmpty() && $comp->team;
        });

        // Получаем текущие соревнования (не только с результатами) для отображения
        $ongoingWith = ['sport', 'team', 'location', 'category', 'participants.user'];
        if ($hasArchiveColumn) {
            $ongoingWith['results'] = $isTeacher
                ? function ($q) {
                    $q->where('is_archive', false)->with(['team.sport', 'user']);
                }
                : function ($q) {
                    $q->with(['team.sport', 'user']);
                };
        } else {
            $ongoingWith['results'] = fn ($q) => $q->with(['team.sport', 'user']);
        }

        $allOngoingCompetitions = Competition::with($ongoingWith)
            ->where('status', 'ongoing')
            ->latest('start_date')
            ->get();

        // Фильтруем: оставляем только те, у которых нет результатов и есть команда (для dropdown)
        $ongoingCompetitions = $allOngoingCompetitions->filter(function($comp) {
            return $comp->results->isEmpty() && $comp->team;
        });

        $categoriesForResultsFilter = CompetitionResultPage::collectCategoriesForCompetitionsFilter(
            $allFinishedCompetitions->concat($allOngoingCompetitions)
        );

        $allowedSportIds = $sportsForResultsFilter->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($sportId !== null && ! in_array($sportId, $allowedSportIds, true)) {
            $sportId = null;
        }

        $allowedCategoryIds = $categoriesForResultsFilter->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($categoryId !== null && ! in_array($categoryId, $allowedCategoryIds, true)) {
            $categoryId = null;
        }

        $placesForResultsFilter = $this->collectPlacesForResultsFilter(
            $allFinishedCompetitions,
            $allOngoingCompetitions
        );

        if ($place !== '' && $place !== '__none__' && ! $placesForResultsFilter->contains($place)) {
            $place = '';
        }

        $allOngoingCompetitionsForDisplay = $this->filterCompetitionsCollection(
            $allOngoingCompetitions,
            $q,
            $dateFrom,
            $dateTo,
            $sportId,
            $place,
            null,
            $categoryId
        );

        $allFinishedCompetitionsForDisplay = $this->filterCompetitionsCollection(
            $allFinishedCompetitionsForDisplay,
            $q,
            $dateFrom,
            $dateTo,
            $sportId,
            $place,
            null,
            $categoryId
        );

        $allFinishedCompetitionsWithoutResultsForDisplay = $this->filterCompetitionsCollection(
            $allFinishedCompetitionsWithoutResultsForDisplay,
            $q,
            $dateFrom,
            $dateTo,
            $sportId,
            $place,
            null,
            $categoryId
        );

        if ($place !== '' && $place !== '__none__') {
            $allFinishedCompetitionsWithoutResultsForDisplay = collect();
        } elseif ($place === '__none__') {
            $allFinishedCompetitionsForDisplay = collect();
            $allOngoingCompetitionsForDisplay = $allOngoingCompetitionsForDisplay->filter(
                fn ($comp) => $comp->results->isEmpty()
            )->values();
        }

        $sortedWithResults = StudentCompetitionListingSort::sortCompetitionCollection(
            $allFinishedCompetitionsForDisplay->unique('id')->values(),
            $activeSortStack
        );
        $sortedWithoutResults = StudentCompetitionListingSort::sortCompetitionCollection(
            $allFinishedCompetitionsWithoutResultsForDisplay->unique('id')->values(),
            $activeSortStack
        );

        $expandedResultRows = CompetitionResultPage::expandCompetitionsToResultListingRows($sortedWithResults);

        $finishedListingItems = $expandedResultRows
            ->map(fn (array $row) => [
                'kind' => 'result',
                'competition' => $row['competition'],
                'result' => $row['result'],
            ])
            ->concat($sortedWithoutResults->map(fn (Competition $c) => [
                'kind' => 'without',
                'competition' => $c,
            ]))
            ->values();

        $currentPage = max(1, (int) $request->query('page', 1));
        $total = $finishedListingItems->count();
        $finishedPageItems = $finishedListingItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $finishedPaginator = new LengthAwarePaginator(
            $finishedPageItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $finishedResultsListingItems = $finishedPageItems
            ->filter(fn (array $item) => ($item['kind'] ?? '') === 'result')
            ->values();

        $allFinishedCompetitionsForDisplay = $finishedResultsListingItems
            ->pluck('competition')
            ->unique('id')
            ->values();

        $allFinishedCompetitionsWithoutResultsForDisplay = $finishedPageItems
            ->filter(fn (array $item) => ($item['kind'] ?? '') === 'without')
            ->pluck('competition')
            ->values();

        $ongoingPaginator = null;
        if ($isTeacher) {
            $ongoingMerged = StudentCompetitionListingSort::sortCompetitionCollection(
                ($allOngoingCompetitionsForDisplay ?? collect())->unique('id')->values(),
                $activeSortStack
            );

            $ongoingPage = max(1, (int) $request->query('ongoing_page', 1));
            $ongoingTotal = $ongoingMerged->count();
            $ongoingPageItems = $ongoingMerged->slice(($ongoingPage - 1) * $perPage, $perPage)->values();

            $ongoingPaginator = new LengthAwarePaginator(
                $ongoingPageItems,
                $ongoingTotal,
                $perPage,
                $ongoingPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                    'pageName' => 'ongoing_page',
                ]
            );

            $allOngoingCompetitionsForDisplay = $ongoingPageItems;
        }

        $layout = $isTeacher ? 'layouts.teacher' : 'layouts.student';

        return view('competitions.results', compact(
            'competitions',
            'ongoingCompetitions',
            'allOngoingCompetitionsForDisplay',
            'allFinishedCompetitionsForDisplay',
            'allFinishedCompetitionsWithoutResultsForDisplay',
            'finishedResultsListingItems',
            'finishedPaginator',
            'ongoingPaginator',
            'sportsForResultsFilter',
            'placesForResultsFilter',
            'layout',
            'q',
            'dateFrom',
            'dateTo',
            'sportId',
            'place',
            'hasSearchFilters',
            'view',
            'perPage',
            'cardsSortStack',
            'listSortStack',
            'categoriesForResultsFilter',
            'categoryId',
        ));
    }

    /**
     * Отчёт по результатам за выбранный месяц (только преподаватель).
     */
    public function resultsReport(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            abort(403);
        }

        $month = trim((string) $request->query('month', ''));
        if ($month === '') {
            $month = now()->format('Y-m');
        }

        try {
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return redirect()
                ->route('competitions.results')
                ->with('error', 'Некорректный месяц для отчёта.');
        }

        $currentMonthStart = now()->startOfMonth();
        if ($monthStart->greaterThan($currentMonthStart)) {
            return redirect()
                ->route('competitions.results')
                ->with('error', 'Нельзя сформировать отчёт за будущий месяц.');
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $competitions = Competition::query()
            ->with([
                'sport',
                'team',
                'location',
                'category',
                'teacher.user',
                'participants.user',
                'results.team',
            ])
            ->where('status', 'finished')
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $viewData = [
            'month' => $monthStart->format('Y-m'),
            'monthLabel' => $monthStart->translatedFormat('F Y'),
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'competitions' => $competitions,
        ];

        $filename = 'otchet_'.$monthStart->format('Y_m').'.pdf';

        return Pdf::loadView('competitions.results-report-pdf', $viewData)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }


    /**
     * @return array{q: string, dateFrom: ?string, dateTo: ?string, sportId: ?int, categoryId: ?int}
     */
    private function parseCompetitionListingFilters(Request $request): array
    {
        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');

        $dateFrom = null;
        $dateTo = null;
        if ($request->filled('date_from')) {
            try {
                $dateFrom = \Illuminate\Support\Carbon::parse($request->query('date_from'))->toDateString();
            } catch (\Throwable) {
            }
        }
        if ($request->filled('date_to')) {
            try {
                $dateTo = \Illuminate\Support\Carbon::parse($request->query('date_to'))->toDateString();
            } catch (\Throwable) {
            }
        }
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $sportId = null;
        if ($request->filled('sport_id') && is_numeric($request->query('sport_id'))) {
            $sportId = (int) $request->query('sport_id');
        }

        $categoryId = null;
        if ($request->filled('competition_category_id') && is_numeric($request->query('competition_category_id'))) {
            $categoryId = (int) $request->query('competition_category_id');
        }

        return compact('q', 'dateFrom', 'dateTo', 'sportId', 'categoryId');
    }

    private function parseResultsPlaceFilter(Request $request): string
    {
        if (! $request->filled('place')) {
            return '';
        }

        $place = Str::limit(trim((string) $request->query('place')), 45, '');

        return $place === '__none__' ? '__none__' : $place;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  ...$competitionGroups
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function collectPlacesForResultsFilter(...$competitionGroups)
    {
        return collect($competitionGroups)
            ->flatten(1)
            ->flatMap(fn (Competition $comp) => $comp->results)
            ->pluck('place')
            ->map(fn ($place) => trim((string) $place))
            ->filter()
            ->unique()
            ->sort(function (string $a, string $b) {
                if (is_numeric($a) && is_numeric($b)) {
                    return (int) $a <=> (int) $b;
                }

                return strnatcasecmp($a, $b);
            })
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  $competitions
     * @return \Illuminate\Support\Collection<int, Competition>
     */
    private function filterCompetitionsCollection(
        $competitions,
        string $q,
        ?string $dateFrom,
        ?string $dateTo,
        ?int $sportId,
        string $place = '',
        ?int $studentUserId = null,
        ?int $categoryId = null
    ) {
        return $competitions->filter(function (Competition $comp) use ($q, $dateFrom, $dateTo, $sportId, $place, $studentUserId, $categoryId) {
            if ($sportId !== null) {
                if ($comp->isPersonalCompetition()) {
                    $matchingBySport = $comp->results->filter(function ($result) use ($comp, $sportId) {
                        $resultSportId = CompetitionResultPage::resolveSportIdForUser(
                            $comp,
                            $result->user_id ? (int) $result->user_id : null
                        );

                        return (int) $resultSportId === $sportId;
                    });

                    if ($matchingBySport->isEmpty()) {
                        return false;
                    }

                    $comp->setRelation('results', $matchingBySport->values());
                } else {
                    $compSportId = CompetitionResultPage::resolveSportIdForUser($comp, $studentUserId);

                    if ((int) $compSportId !== $sportId) {
                        return false;
                    }
                }
            }

            if ($categoryId !== null) {
                if ((int) $comp->competition_category_id !== $categoryId) {
                    return false;
                }
            }

            if ($q !== '') {
                $needle = mb_strtolower($q);
                if (! str_contains(mb_strtolower($comp->name), $needle)) {
                    return false;
                }
            }

            if (! ParticipantListingDateFilter::competitionIntervalsOverlap(
                $comp->start_date,
                $comp->end_date,
                $dateFrom,
                $dateTo
            )) {
                return false;
            }

            if ($place === '') {
                return true;
            }

            if ($place === '__none__') {
                return $comp->results->isEmpty();
            }

            $matchingResults = $comp->results->filter(
                fn ($result) => (string) $result->place === $place
            );

            if ($matchingResults->isEmpty()) {
                return false;
            }

            $comp->setRelation('results', $matchingResults->values());

            return true;
        })->values();
    }

    private function resolveCompetitionShowFrom(?Request $request = null): ?string
    {
        $request = $request ?? request();
        $from = $request->input('from', $request->query('from'));

        if (is_string($from) && in_array($from, ['results', 'photo-archive', 'index'], true)) {
            return $from;
        }

        if ($request->hasAny(['filter', 'sort', 'order', 'view', 'page', 'q', 'sport_id', 'date_from', 'date_to'])) {
            return 'index';
        }

        return 'index';
    }

    /**
     * Query-параметры для ссылки на страницу соревнования (контекст «откуда пришли»).
     *
     * @return array<string, mixed>
     */
    private function competitionShowRouteQuery(?Request $request = null): array
    {
        $request = $request ?? request();
        $from = $this->resolveCompetitionShowFrom($request);

        if ($from === 'results') {
            return ['from' => 'results'];
        }

        if ($from === 'photo-archive') {
            return array_filter([
                'from' => 'photo-archive',
                'q' => $request->input('q', $request->query('q')),
                'date_from' => $request->input('date_from', $request->query('date_from')),
                'date_to' => $request->input('date_to', $request->query('date_to')),
            ], static fn ($v) => $v !== null && $v !== '');
        }

        return array_filter([
            'from' => 'index',
            'filter' => $request->input('filter', $request->query('filter')),
            'sort' => $request->input('sort', $request->query('sort')),
            'order' => $request->input('order', $request->query('order')),
            'view' => $request->input('view', $request->query('view')),
            'page' => $request->input('page', $request->query('page')),
            'q' => $request->input('q', $request->query('q')),
            'sport_id' => $request->input('sport_id', $request->query('sport_id')),
            'date_from' => $request->input('date_from', $request->query('date_from')),
            'date_to' => $request->input('date_to', $request->query('date_to')),
        ], static fn ($v) => $v !== null && $v !== '');
    }

    private function findFormTypeNumberConflict(
        int $formTypeId,
        string $formNumber,
        Competition $currentCompetition,
        int $userId
    ): ?CompetitionForm {
        $rangeStart = $currentCompetition->start_date;
        $rangeEnd = $currentCompetition->end_date;

        return CompetitionForm::query()
            ->with('competition:id,name,start_date,end_date')
            ->where('form_type_id', $formTypeId)
            ->where('form_number', $formNumber)
            ->where('form_status', CompetitionForm::STATUS_PENDING)
            ->where(function ($q) use ($currentCompetition, $userId, $rangeStart, $rangeEnd) {
                // Другой студент в этом же соревновании
                $q->where(function ($q2) use ($currentCompetition, $userId) {
                    $q2->where('competition_id', $currentCompetition->id)
                        ->where('user_id', '!=', $userId);
                })
                // Другое соревнование с пересекающимися датами
                    ->orWhere(function ($q2) use ($currentCompetition, $rangeStart, $rangeEnd) {
                        $q2->where('competition_id', '!=', $currentCompetition->id)
                            ->whereHas('competition', function ($q3) use ($rangeStart, $rangeEnd) {
                                $q3->where('start_date', '<=', $rangeEnd)
                                    ->where('end_date', '>=', $rangeStart);
                            });
                    });
            })
            ->first();
    }

    private function formTypeNumberConflictMessage(CompetitionForm $conflict, int $currentCompetitionId): string
    {
        $competitionName = $conflict->competition?->name;

        if ((int) $conflict->competition_id === $currentCompetitionId) {
            return 'Этот вид формы и номер уже назначены другому студенту в этом соревновании.';
        }

        if ($competitionName) {
            return 'Этот вид формы и номер уже назначены другому студенту в соревновании «'.$competitionName.'».';
        }

        return 'Этот вид формы и номер уже назначены другому студенту в другом соревновании.';
    }

    /**
     * @return array{url: string, label: string}
     */
    private function competitionShowBackLink(?Request $request = null): array
    {
        $request = $request ?? request();
        $from = $this->resolveCompetitionShowFrom($request);

        if ($from === 'results') {
            return [
                'url' => route('competitions.results'),
                'label' => 'Назад к результатам',
            ];
        }

        if ($from === 'photo-archive') {
            return [
                'url' => route('competitions.photo-archive', array_filter([
                    'q' => $request->input('q', $request->query('q')),
                    'date_from' => $request->input('date_from', $request->query('date_from')),
                    'date_to' => $request->input('date_to', $request->query('date_to')),
                ], static fn ($v) => $v !== null && $v !== '')),
                'label' => 'Назад в архив',
            ];
        }

        if ($from === 'index') {
            return [
                'url' => route('competitions.index', array_filter([
                    'filter' => $request->input('filter', $request->query('filter')),
                    'sort' => $request->input('sort', $request->query('sort')),
                    'order' => $request->input('order', $request->query('order')),
                    'view' => $request->input('view', $request->query('view')),
                    'page' => $request->input('page', $request->query('page')),
                    'q' => $request->input('q', $request->query('q')),
                    'sport_id' => $request->input('sport_id', $request->query('sport_id')),
                    'date_from' => $request->input('date_from', $request->query('date_from')),
                    'date_to' => $request->input('date_to', $request->query('date_to')),
                ], static fn ($v) => $v !== null && $v !== '')),
                'label' => 'Назад к списку',
            ];
        }

        return [
            'url' => route('competitions.index'),
            'label' => 'Назад к списку',
        ];
    }

    private function redirectToCompetitionShow(Competition $competition, ?Request $request = null): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('competitions.show', array_merge(
            ['competition' => $competition],
            $this->competitionShowRouteQuery($request)
        ));
    }

    /**
     * Helper methods for LDAP operations (copied from AuthController pattern)
     */
}

