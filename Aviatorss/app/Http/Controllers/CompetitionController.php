<?php

namespace App\Http\Controllers;

use App\Models\ApplicationCompetition;
use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionImage;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionResult;
use App\Models\Location;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\ParticipantListingDateFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $filter = $request->get('filter', 'all'); // all, upcoming, ongoing, finished, cancelled
        $sort = $request->get('sort', 'start_date'); // start_date, status
        $order = $request->get('order', 'desc'); // asc, desc
        $archiveThreshold = now()->subMonths(self::COMPETITION_ARCHIVE_MONTHS)->startOfDay();
        
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
            $sid = (int) $request->query('sport_id');
            if (Sport::whereKey($sid)->exists()) {
                $sportId = $sid;
            }
        }

        $query = Competition::with(['sport', 'team', 'location', 'creator']);

        // Скрываем из основной страницы завершенные соревнования старше N месяцев (они в архиве).
        $query->where(function ($q) use ($archiveThreshold) {
            $q->where('status', '!=', 'finished')
                ->orWhereDate('end_date', '>', $archiveThreshold->toDateString());
        });

        $query->when($q !== '', function ($builder) use ($q) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $builder->where('name', 'like', $like);
        });
        $query->when($sportId !== null, fn ($builder) => $builder->where('sport_id', $sportId));


        $query->when($dateFrom && $dateTo, function ($builder) use ($dateFrom, $dateTo) {
            $s = $dateFrom ?? '0000-00-00';
            $e = $dateTo ?? '9999-12-31';
            $builder->where('start_date', '<=', $e)
            ->where('end_date', '>=', $s);
        });
         $query->when($dateFrom && ! $dateTo, fn ($builder) => $builder->whereDate('end_date', '>=', $dateFrom));
         $query->when(! $dateFrom && $dateTo, fn ($builder) => $builder->whereDate('start_date', '<=', $dateTo));
        
        // Применяем фильтр по статусу
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
        
        // Применяем сортировку
        if ($sort === 'status') {
            // Сортируем по статусу: upcoming, ongoing, finished, cancelled
            $query->orderByRaw("CASE 
                WHEN status = 'upcoming' THEN 1 
                WHEN status = 'ongoing' THEN 2 
                WHEN status = 'finished' THEN 3 
                WHEN status = 'cancelled' THEN 4 
                ELSE 5 
            END " . strtoupper($order));
            // Вторичная сортировка по дате
            $query->orderBy('start_date', $order === 'asc' ? 'desc' : 'asc');
        } else {
            // Сортируем по дате (для завершённых — по окончанию, чтобы «новые» были сверху)
            if ($filter === 'finished') {
                $query->orderBy('end_date', $order)->orderBy('start_date', $order);
            } else {
                $query->orderBy('start_date', $order);
            }
            $query->orderBy('id', $order);
        }

        $view = $request->query('view', 'list');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'list';
        }

        $perPage = $view === 'cards'
            ? self::TEACHER_INDEX_PER_PAGE_CARDS
            : self::TEACHER_INDEX_PER_PAGE_LIST;

        $competitions = $query->paginate($perPage)->withQueryString();

        $sports = Sport::query()->orderBy('name')->get();

        return view('competitions.index', compact(
            'competitions',
            'filter',
            'sort',
            'order',
            'view',
            'q',
            'dateFrom',
            'dateTo',
            'sportId',
            'sports',
        ));
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
        $validated = $request->validate([
            'images' => 'required|array|min:1',
            // image — устаревшие HEIC с айфона часто не проходят; даём явные типы + AVIF
            'images.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,bmp,avif',
        ], [
            'images.required' => 'Выберите хотя бы один файл.',
            'images.*.file' => 'Каждый элемент должен быть файлом.',
            'images.*.mimes' => 'Допустимы JPEG, PNG, GIF, WebP, BMP, AVIF. Фото в формате HEIC загрузите не с телефона или конвертируйте в JPEG.',
            'images.*.max' => 'Превышен размер файла.',
        ]);

        $dir = 'competition_photos/'.$competition->id;

        foreach ($validated['images'] as $file) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($dir, $filename, 'public');
            if (! $path) {
                return redirect()
                    ->route('competitions.photos', $competition)
                    ->withErrors(['images' => 'Не удалось сохранить файл на сервере (проверьте права на каталог storage).']);
            }
            CompetitionImage::create([
                'path' => $path,
                'competition_id' => $competition->id,
                'size_bytes' => $file->getSize(),
            ]);
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
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'required|exists:locations,id',
            'competition_category_id' => 'nullable|exists:competition_categories,id',
        ], [
            'end_date.after_or_equal' => 'Дата окончания должна быть позже или равна дате начала.',
        ]);

        // Автоматически устанавливаем статус "upcoming"
        $validated['status'] = 'upcoming';

        $team = Team::query()->with('sport')->find($validated['team_id']);
        if (! $team || ! $team->sport_id) {
            return redirect()->route('competitions.create')
                ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.'])
                ->withInput();
        }

        $newStartDate = \Carbon\Carbon::parse($validated['start_date']);
        $newEndDate = \Carbon\Carbon::parse($validated['end_date']);

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

        $competition = Competition::create([
            'sport_id' => $team->sport_id,
            'team_id' => $team->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'location_id' => $validated['location_id'],
            'competition_category_id' => $validated['competition_category_id'] ?? null,
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
            'category',
            'results.team',
            'competitionApplications' => fn ($q) => $q
                ->where('status', 'pending')
                ->with('student')
                ->orderBy('created_at'),
        ]);
        
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

        return view('competitions.show', compact(
            'competition',
            'allLocations',
            'teacherCompetitionListBackUrl',
            'teacherCompetitionBackLabel',
            'competitionShowContextQuery',
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
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'required|exists:locations,id',
        ]);

        // Автоматически устанавливаем статус "upcoming"
        $validated['status'] = 'upcoming';

        $team = Team::query()->with('sport')->find($validated['team_id']);
        if (! $team || ! $team->sport_id) {
            return redirect()->route('competitions.edit', $competition)
                ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта. Отредактируйте команду и выберите вид спорта.'])
                ->withInput();
        }

        $newStartDate = \Carbon\Carbon::parse($validated['start_date']);
        $newEndDate = \Carbon\Carbon::parse($validated['end_date']);

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

        $competition->update([
            'sport_id' => $team->sport_id,
            'team_id' => $team->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'location_id' => $validated['location_id'],
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
        $allowedFilters = ['all', 'upcoming', 'ongoing', 'finished'];
        $filter = $request->get('filter', 'upcoming');
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'upcoming';
        }

        $listingFilters = $request->validate([
            'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (! empty($listingFilters['date_from']) && ! empty($listingFilters['date_to'])
            && $listingFilters['date_from'] > $listingFilters['date_to']) {
            [$listingFilters['date_from'], $listingFilters['date_to']] = [
                $listingFilters['date_to'],
                $listingFilters['date_from'],
            ];
        }

        $competitions = Competition::query()
            ->with(['sport', 'team', 'location', 'images'])
            ->where('status', '!=', 'cancelled');

        if ($filter !== 'all') {
            $competitions->where('status', $filter);
        }

        if (! empty($listingFilters['sport_id'])) {
            $competitions->where('sport_id', (int) $listingFilters['sport_id']);
        }

        ParticipantListingDateFilter::applyToCompetitionQuery(
            $competitions,
            $listingFilters['date_from'] ?? null,
            $listingFilters['date_to'] ?? null,
        );

        // От новых к старым: завершённые — по дате окончания; остальное — по дате начала
        if ($filter === 'finished') {
            $competitions->orderByDesc('end_date')->orderByDesc('start_date')->orderByDesc('id');
        } else {
            $competitions->orderByDesc('start_date')->orderByDesc('id');
        }

        $competitions = $competitions->paginate(50)->withQueryString();

        $sportsForFilter = Sport::orderBy('name')->get();

        return view(
            'competitions.student.index',
            compact('competitions', 'filter', 'listingFilters', 'sportsForFilter')
        );
    }

    /**
     * Display the specified competition for students.
     */
    public function showStudent(Competition $competition)
    {
        $competition->load(['sport', 'team', 'location', 'participants.user']);

        $sorted = $competition->participants->sortBy(function ($participant) {
            return $participant->user->lastname.' '.$participant->user->firstname;
        })->values();
        $competition->setRelation('participants', $sorted);

        $isParticipant = $sorted->contains(fn ($p) => (int) $p->user_id === (int) auth()->id());

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

        $competitionShowBack = $this->competitionShowBackLink();
        $competitionShowContextQuery = $this->competitionShowRouteQuery();

        return view('competitions.student.show', compact(
            'competition',
            'isParticipant',
            'pendingApplication',
            'latestRejectedApplication',
            'latestAcceptedApplication',
            'competitionShowBack',
            'competitionShowContextQuery',
        ));
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

        if (ApplicationCompetition::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists()) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Заявка уже отправлена и ожидает рассмотрения.');
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
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Участников можно добавлять только к предстоящим соревнованиям.');
        }

        $validated = $request->validate([
            'student_data' => 'required|string',
            'role' => 'nullable|in:student,teacher',
        ]);

        $studentData = json_decode($validated['student_data'], true);
        if (!is_array($studentData) || empty($studentData['login'])) {
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

        // Проверяем, не участвует ли уже этот пользователь
        $existingParticipant = CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Этот пользователь уже участвует в соревновании.');
        }

        CompetitionParticipant::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => $user->role ?? 'student',
        ]);

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

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Участник успешно добавлен в соревнование!');
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
     * Remove a participant from the competition.
     */
    public function removeParticipant(Competition $competition, User $user)
    {
        $participant = CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Участник не найден в этом соревновании.');
        }

        // Используем прямой запрос для удаления, так как модель имеет составной первичный ключ
        CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->delete();

        return $this->redirectToCompetitionShow($competition)
            ->with('success', 'Участник успешно удален из соревнования!');
    }

    /**
     * AJAX поиск студентов и преподавателей в LDAP.
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
                
                // Если пользователь не в нужных OU - пропускаем
                if (!$isInStudentsOu && !$isInTeachersOu) {
                    \Log::info('LDAP User excluded - not in Students/Teachers OU', [
                        'login' => $ldapUser->getFirstAttribute('samaccountname'),
                        'dn' => $dnOriginal,
                        'isInStudentsOu' => $isInStudentsOu,
                        'isInTeachersOu' => $isInTeachersOu,
                    ]);
                    continue;
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

                $dn = $ldapUser->getDn() ?? '';
                $resolveGroupNameResult = $this->resolveGroupName($ldapUser);
                $extractGroupFromDnResult = $this->extractGroupFromDn($dn);
                
                $groupName = $resolveGroupNameResult;
                if (!$groupName) {
                    $groupName = $extractGroupFromDnResult;
                }
                
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

            return response()->json(['students' => $students]);

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
        ]);

        // Загружаем все необходимые связи из базы данных
        $competition->load(['sport', 'team', 'location', 'participants.user']);

        // Получаем студентов из таблицы participants соревнования
        $students = $competition->participants->where('role', 'student')->sortBy(function($participant) {
            return $participant->user->lastname . ' ' . $participant->user->firstname;
        })->values();

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
            'competition_description' => $competition->description ?? $competition->name,
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
        $teacherParticipant = $competition->participants->where('user_id', $userId)->where('role', 'teacher')->first();
        
        if (!$teacherParticipant) {
            return 'Не указан';
        }
        
        $teacher = $teacherParticipant->user;
        return $teacher->lastname . ' ' . mb_substr($teacher->firstname, 0, 1) . '.' . ($teacher->patronymic ? mb_substr($teacher->patronymic, 0, 1) . '.' : '');
    }

    /**
     * Generate order type 2: Об участии в мероприятии
     */
    public function generateOrder2(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'teacher_participant' => 'required|exists:users,id',
            'head_of_studies' => 'required|string|max:200',
            'deputy_director' => 'required|string|max:200',
            'director_name' => 'required|string|max:200',
        ]);

        // Загружаем все необходимые связи из базы данных
        $competition->load(['sport', 'team', 'location', 'participants.user']);

        // Получаем преподавателя из таблицы participants соревнования
        $teacherParticipant = $competition->participants->where('user_id', $validated['teacher_participant'])->where('role', 'teacher')->first();
        
        if (!$teacherParticipant) {
            return $this->redirectToCompetitionShow($competition)
                ->with('error', 'Выбранный преподаватель не найден среди участников соревнования.');
        }
        
        $teacher = $teacherParticipant->user;

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
        $competition->load(['sport', 'team']);

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
            'place' => 'required|string|max:45',
        ]);

        $teamId = $competition->team_id;

        // Проверяем, не добавлен ли уже результат для этой команды в этом соревновании
        $existingResult = \App\Models\CompetitionResult::where('competitions_id', $competition->id)
            ->where('teams_id', $teamId)
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
            'place' => 'required|string|max:45',
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
     * Display results page for competitions.
     */
    public function results(Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user && $user->role === 'teacher';
        $archiveThreshold = now()->subMonths(self::COMPETITION_ARCHIVE_MONTHS)->startOfDay();
        $hasArchiveColumn = Schema::hasColumn('competition_results', 'is_archive');

        $listingFilters = $this->parseCompetitionListingFilters($request);
        extract($listingFilters);
        $place = $this->parseResultsPlaceFilter($request);
        $hasSearchFilters = $q !== '' || $dateFrom || $dateTo || $sportId || $place !== '';

        $view = $request->query('view', $isTeacher ? 'list' : 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = $isTeacher ? 'list' : 'cards';
        }

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

        // Загрузка результатов: преподаватель — все (с учётом архива); студент — только строки с местом.
        $finishedWith = ['sport', 'team', 'location', 'category', 'participants.user'];
        if ($hasArchiveColumn) {
            $finishedWith['results'] = $isTeacher
                ? function ($q) {
                    $q->where('is_archive', false)->with('team');
                }
                : function ($q) {
                    $q->where('is_archive', false)
                        ->whereNotNull('place')
                        ->where('place', '!=', '')
                        ->with('team');
                };
        } elseif ($isTeacher) {
            $finishedWith[] = 'results.team';
        } else {
            $finishedWith['results'] = function ($q) {
                $q->whereNotNull('place')
                    ->where('place', '!=', '')
                    ->with('team');
            };
        }

        if ($isTeacher) {
            $allFinishedCompetitions = Competition::with($finishedWith)
                ->where('status', 'finished')
                ->where(function ($q) use ($archiveThreshold) {
                    // Старые завершенные соревнования остаются в результатах, если у них нет мест.
                    $q->whereDate('end_date', '>', $archiveThreshold->toDateString())
                        ->orWhereDoesntHave('results', function ($r) {
                            $r->whereNotNull('place')
                                ->where('place', '!=', '');
                        });
                })
                ->latest('end_date')
                ->get();
        } else {
            $allFinishedCompetitions = Competition::with($finishedWith)
                ->where('status', 'finished')
                ->whereHas('results', function ($r) use ($hasArchiveColumn) {
                    $r->whereNotNull('place')->where('place', '!=', '');
                    if ($hasArchiveColumn) {
                        $r->where('is_archive', false);
                    }
                })
                ->latest('end_date')
                ->get();
        }

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

        // Список видов спорта для фильтра на странице результатов
        if ($isTeacher) {
            $sportsForResultsFilter = $allFinishedCompetitions
                ->map(fn ($comp) => $comp->sport)
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        } else {
            $sportIds = Competition::query()
                ->where('status', 'finished')
                ->whereHas('results', function ($r) use ($hasArchiveColumn) {
                    $r->whereNotNull('place')->where('place', '!=', '');
                    if ($hasArchiveColumn) {
                        $r->where('is_archive', false);
                    }
                })
                ->distinct()
                ->pluck('sport_id')
                ->filter();

            $sportsForResultsFilter = Sport::query()
                ->whereIn('id', $sportIds)
                ->orderBy('name')
                ->get();
        }

        // Получаем завершенные соревнования только для dropdown (без результатов и с командой)
        $competitions = $allFinishedCompetitions->filter(function($comp) {
            return $comp->results->isEmpty() && $comp->team;
        });

        // Получаем текущие соревнования (не только с результатами) для отображения
        $ongoingWith = ['sport', 'team', 'location', 'category', 'participants.user'];
        if ($hasArchiveColumn) {
            $ongoingWith['results'] = $isTeacher
                ? function ($q) {
                    $q->where('is_archive', false)->with('team');
                }
                : function ($q) {
                    $q->with('team');
                };
        } else {
            $ongoingWith[] = 'results.team';
        }

        $allOngoingCompetitions = Competition::with($ongoingWith)
            ->where('status', 'ongoing')
            ->latest('start_date')
            ->get();

        // Фильтруем: оставляем только те, у которых нет результатов и есть команда (для dropdown)
        $ongoingCompetitions = $allOngoingCompetitions->filter(function($comp) {
            return $comp->results->isEmpty() && $comp->team;
        });

        $allowedSportIds = $sportsForResultsFilter->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($sportId !== null && ! in_array($sportId, $allowedSportIds, true)) {
            $sportId = null;
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
            $place
        );

        $allFinishedCompetitionsForDisplay = $this->filterCompetitionsCollection(
            $allFinishedCompetitionsForDisplay,
            $q,
            $dateFrom,
            $dateTo,
            $sportId,
            $place
        );

        $allFinishedCompetitionsWithoutResultsForDisplay = $this->filterCompetitionsCollection(
            $allFinishedCompetitionsWithoutResultsForDisplay,
            $q,
            $dateFrom,
            $dateTo,
            $sportId,
            $place
        );

        if ($place !== '' && $place !== '__none__') {
            $allFinishedCompetitionsWithoutResultsForDisplay = collect();
        } elseif ($place === '__none__') {
            $allFinishedCompetitionsForDisplay = collect();
            $allOngoingCompetitionsForDisplay = $allOngoingCompetitionsForDisplay->filter(
                fn ($comp) => $comp->results->isEmpty()
            )->values();
        }

        $layout = $isTeacher ? 'layouts.teacher' : 'layouts.student';

        return view('competitions.results', compact(
            'competitions',
            'ongoingCompetitions',
            'allOngoingCompetitionsForDisplay',
            'allFinishedCompetitionsForDisplay',
            'allFinishedCompetitionsWithoutResultsForDisplay',
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
        ));
    }


    /**
     * @return array{q: string, dateFrom: ?string, dateTo: ?string, sportId: ?int}
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

        return compact('q', 'dateFrom', 'dateTo', 'sportId');
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
        string $place = ''
    ) {
        return $competitions->filter(function (Competition $comp) use ($q, $dateFrom, $dateTo, $sportId, $place) {
            if ($sportId !== null && (int) $comp->sport_id !== $sportId) {
                return false;
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

