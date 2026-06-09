<?php

namespace App\Http\Controllers;

use App\Models\LocationTraining;
use App\Models\Sport;
use App\Models\Team;
use App\Support\ParticipantListingDateFilter;
use App\Support\TrainingRegistrationOverlap;
use App\Support\TrainingSessionListingSort;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrainingSessionController extends Controller
{
    /**
     * Display a listing of the training sessions.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $allowedFilters = ['all', 'upcoming', 'completed', 'cancelled', 'in_progress'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');
        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 50;
        }

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
        if ($request->filled('sport_id')) {
            $sid = (int) $request->query('sport_id');
            if ($sid > 0 && Sport::query()->whereKey($sid)->exists()) {
                $sportId = $sid;
            }
        }

        $view = $request->query('view', 'list');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'list';
        }

        $statusQuery = TrainingSession::query()->with(['sport', 'team', 'location']);
        $this->applyTeacherTrainingStatusFilter($statusQuery, $filter);

        $sportIds = (clone $statusQuery)->distinct()->pluck('sport_id')->filter()->values();
        $sportsForFilter = $sportIds->isNotEmpty()
            ? Sport::query()->whereIn('id', $sportIds)->orderBy('name')->get()
            : Sport::query()->orderBy('name')->get();

        if ($sportId !== null && ! $sportsForFilter->contains(fn ($s) => (int) $s->id === (int) $sportId)) {
            $sportId = null;
        }

        $listQuery = (clone $statusQuery);
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $listQuery->where('title', 'like', $like);
        }
        if ($sportId !== null) {
            $listQuery->where('sport_id', $sportId);
        }
        ParticipantListingDateFilter::applyToTrainingSessionQuery($listQuery, $dateFrom, $dateTo);

        $sortStacks = $this->trainingListingSortStacks($request, $view);
        TrainingSessionListingSort::applyToQuery($listQuery, $sortStacks['activeSortStack']);
        $cardsSortStack = $sortStacks['cardsSortStack'];
        $listSortStack = $sortStacks['listSortStack'];

        $sessions = $listQuery
            ->paginate($perPage)
            ->withQueryString();

        $hasSearchFilters = $q !== '' || $dateFrom || $dateTo || $sportId !== null;

        return view('training-sessions.index', compact(
            'sessions',
            'sportsForFilter',
            'filter',
            'q',
            'dateFrom',
            'dateTo',
            'sportId',
            'view',
            'hasSearchFilters',
            'perPage',
            'cardsSortStack',
            'listSortStack',
        ));
    }

    /**
     * @return array{cardsSortStack: array, listSortStack: array, activeSortStack: array}
     */
    protected function trainingListingSortStacks(Request $request, string $view): array
    {
        $cardsSortStack = TrainingSessionListingSort::parseStack($request, TrainingSessionListingSort::PREFIX_CARDS);
        $listSortStack = TrainingSessionListingSort::normalizeListStack(
            TrainingSessionListingSort::parseStack($request, TrainingSessionListingSort::PREFIX_LIST)
        );
        $activeSortStack = $view === 'list' ? $listSortStack : $cardsSortStack;

        return compact('cardsSortStack', 'listSortStack', 'activeSortStack');
    }

    /**
     * @param  Builder<TrainingSession>  $query
     */
    protected function applyTeacherTrainingStatusFilter(Builder $query, string $filter): void
    {
        if ($filter === 'upcoming') {
            $query->where(function ($q) {
                $q->where('status', 'scheduled')
                    ->where('start_time', '>', now());
            });

            return;
        }
        if ($filter === 'completed') {
            $query->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhere('end_time', '<', now());
            });

            return;
        }
        if ($filter === 'cancelled') {
            $query->where('status', 'cancelled');

            return;
        }
        if ($filter === 'in_progress') {
            $query->where(function ($q) {
                $q->where('status', 'in_progress')
                    ->orWhere(function ($q2) {
                        $q2->where('start_time', '<=', now())
                            ->where('end_time', '>=', now())
                            ->where('status', '!=', 'cancelled');
                    });
            });
        }
    }

    /**
     * Show the form for creating a new training session.
     */
    public function create()
    {
        $teams = Team::with('sport')->orderBy('name')->get();
        $locations = LocationTraining::orderBy('location')->get();

        return view('training-sessions.create', compact('teams', 'locations'));
    }

    /**
     * Store a newly created training session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after_or_equal:' . now()->format('Y-m-d H:i'),
            'end_time' => 'required|date|after:start_time',
            'locations_id' => 'required|exists:locations_training,id',
        ], [
            'start_time.after_or_equal' => 'Дата и время начала не могут быть в прошлом.',
            'end_time.after' => 'Дата и время окончания должны быть позже времени начала.',
        ]);

        $team = Team::query()->findOrFail($validated['team_id']);
        if (! $team->sport_id) {
            return redirect()->route('training-sessions.create')
                ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта.'])
                ->withInput();
        }

        $validated['sport_id'] = $team->sport_id;
        unset($validated['team_id']);

        // Автоматически устанавливаем статус "scheduled"
        $validated['status'] = 'scheduled';

        $newStartTime = \Carbon\Carbon::parse($validated['start_time']);
        $newEndTime = \Carbon\Carbon::parse($validated['end_time']);

        // Проверяем пересечение времени с существующими тренировками В ТОЙ ЖЕ ЛОКАЦИИ
        $conflictingTraining = TrainingSession::where('status', '!=', 'cancelled')
            ->where('locations_id', $validated['locations_id'])
            ->where(function($query) use ($newStartTime, $newEndTime) {
                // Проверяем пересечение времени:
                // start_time нового <= end_time существующего И start_time существующего <= end_time нового
                $query->where(function($q) use ($newStartTime, $newEndTime) {
                    $q->where('start_time', '<=', $newEndTime)
                      ->where('end_time', '>=', $newStartTime);
                });
            })
            ->with('location')
            ->first();

        if ($conflictingTraining) {
            $locationName = $conflictingTraining->location ? $conflictingTraining->location->location : 'неизвестная локация';
            return redirect()->route('training-sessions.create')
                ->withErrors(['start_time' => 'Локация "' . $locationName . '" уже занята в это время. Конфликтующая тренировка: "' . $conflictingTraining->title . '" (' . $conflictingTraining->start_time->format('d.m.Y H:i') . ' - ' . $conflictingTraining->end_time->format('d.m.Y H:i') . ').'])
                ->withInput();
        }

        TrainingSession::create(array_merge($validated, [
            'team_id' => $team->id,
        ]));

        return redirect()->route('training-sessions.index')
            ->with('success', 'Тренировочная сессия успешно создана!');
    }

    /**
     * Display the specified training session.
     */
    public function show(TrainingSession $trainingSession)
    {
        $trainingSession->load(['sport', 'team', 'location', 'registrations.user']);
        
        // Сортируем регистрации по фамилии и имени
        $trainingSession->registrations = $trainingSession->registrations->sortBy(function($registration) {
            return $registration->user->lastname . ' ' . $registration->user->firstname;
        })->values();
        
        // Получаем все локации для тренировок
        $allLocations = LocationTraining::orderBy('location')->get();
        
        return view('training-sessions.show', compact('trainingSession', 'allLocations'));
    }

    /**
     * Show the form for editing the specified training session.
     */
    public function edit(TrainingSession $trainingSession)
    {
        $teams = Team::with('sport')->orderBy('name')->get();
        $locations = LocationTraining::orderBy('location')->get();

        return view('training-sessions.edit', compact('trainingSession', 'teams', 'locations'));
    }

    /**
     * Update the specified training session in storage.
     */
    public function update(Request $request, TrainingSession $trainingSession)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'locations_id' => 'required|exists:locations_training,id',
        ], [
            'end_time.after' => 'Дата и время окончания должны быть позже времени начала.',
        ]);

        $team = Team::query()->findOrFail($validated['team_id']);
        if (! $team->sport_id) {
            return redirect()->route('training-sessions.edit', $trainingSession)
                ->withErrors(['team_id' => 'У выбранной команды не указан вид спорта.'])
                ->withInput();
        }

        $validated['sport_id'] = $team->sport_id;
        $validated['team_id'] = $team->id;

        // Автоматически устанавливаем статус "scheduled"
        $validated['status'] = 'scheduled';

        $newStartTime = \Carbon\Carbon::parse($validated['start_time']);
        $newEndTime = \Carbon\Carbon::parse($validated['end_time']);

        // Проверяем пересечение времени с существующими тренировками В ТОЙ ЖЕ ЛОКАЦИИ
        // Исключаем текущую тренировку из проверки
        $conflictingTraining = TrainingSession::where('id', '!=', $trainingSession->id)
            ->where('status', '!=', 'cancelled')
            ->where('locations_id', $validated['locations_id'])
            ->where(function($query) use ($newStartTime, $newEndTime) {
                // Проверяем пересечение времени:
                // start_time нового <= end_time существующего И start_time существующего <= end_time нового
                $query->where(function($q) use ($newStartTime, $newEndTime) {
                    $q->where('start_time', '<=', $newEndTime)
                      ->where('end_time', '>=', $newStartTime);
                });
            })
            ->with('location')
            ->first();

        if ($conflictingTraining) {
            $locationName = $conflictingTraining->location ? $conflictingTraining->location->location : 'неизвестная локация';
            return redirect()->route('training-sessions.edit', $trainingSession)
                ->withErrors(['start_time' => 'Локация "' . $locationName . '" уже занята в это время. Конфликтующая тренировка: "' . $conflictingTraining->title . '" (' . $conflictingTraining->start_time->format('d.m.Y H:i') . ' - ' . $conflictingTraining->end_time->format('d.m.Y H:i') . ').'])
                ->withInput();
        }

        $trainingSession->update($validated);

        return redirect()->route('training-sessions.index')
            ->with('success', 'Тренировочная сессия успешно обновлена!');
    }

    /**
     * Cancel the specified training session.
     */
    public function cancel(TrainingSession $trainingSession)
    {
        // Можно отменить только запланированные или идущие тренировки
        if (!in_array($trainingSession->status, ['scheduled', 'in_progress'])) {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Можно отменить только запланированные или идущие тренировки.');
        }

        $trainingSession->update(['status' => 'cancelled']);

        return redirect()->route('training-sessions.show', $trainingSession)
            ->with('success', 'Тренировочная сессия успешно отменена!');
    }

    /**
     * Remove the specified training session from storage.
     */
    public function destroy(TrainingSession $trainingSession)
    {
        $trainingSession->delete();

        return redirect()->route('training-sessions.index')
            ->with('success', 'Тренировочная сессия успешно удалена!');
    }

    /**
     * Display a listing of the training sessions for students.
     */
    public function indexStudent(Request $request)
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'all'); // all, upcoming, completed, cancelled, in_progress
        $allowedFilters = ['all', 'upcoming', 'completed', 'cancelled', 'in_progress'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 50;
        }

        $listingFilters = $request->validate([
            'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $listingFilters['q'] = Str::limit(trim((string) ($listingFilters['q'] ?? '')), 255, '');

        $view = $request->query('view', 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'cards';
        }

        if (! empty($listingFilters['date_from']) && ! empty($listingFilters['date_to'])
            && $listingFilters['date_from'] > $listingFilters['date_to']) {
            [$listingFilters['date_from'], $listingFilters['date_to']] = [
                $listingFilters['date_to'],
                $listingFilters['date_from'],
            ];
        }

        // Базовый запрос для тренировок
        $baseQuery = TrainingSession::with(['sport', 'location', 'registrations' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        $this->applyStudentTrainingStatusFilter($baseQuery, $filter);
        $this->applyStudentTrainingListingFilters($baseQuery, $listingFilters);

        $sortStacks = $this->trainingListingSortStacks($request, $view);
        $listQuery = $baseQuery->clone();
        TrainingSessionListingSort::applyToQuery($listQuery, $sortStacks['activeSortStack']);
        $cardsSortStack = $sortStacks['cardsSortStack'];
        $listSortStack = $sortStacks['listSortStack'];

        $allTrainingSessions = $listQuery
            ->paginate($perPage)
            ->withQueryString();

        $sportsForFilter = Sport::orderBy('name')->get();

        $hasSearchFilters = $listingFilters['q'] !== ''
            || ! empty($listingFilters['sport_id'])
            || ! empty($listingFilters['date_from'])
            || ! empty($listingFilters['date_to']);

        return view(
            'training-sessions.student.index',
            compact(
                'allTrainingSessions',
                'filter',
                'listingFilters',
                'sportsForFilter',
                'view',
                'hasSearchFilters',
                'perPage',
                'cardsSortStack',
                'listSortStack',
            )
        );
    }

    /**
     * Training sessions the student is registered for.
     */
    public function myTrainings(Request $request)
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'all');
        $allowedFilters = ['all', 'upcoming', 'completed', 'cancelled', 'in_progress'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 50;
        }

        $listingFilters = $request->validate([
            'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $listingFilters['q'] = Str::limit(trim((string) ($listingFilters['q'] ?? '')), 255, '');

        $view = $request->query('view', 'cards');
        if (! in_array($view, ['list', 'cards'], true)) {
            $view = 'cards';
        }

        if (! empty($listingFilters['date_from']) && ! empty($listingFilters['date_to'])
            && $listingFilters['date_from'] > $listingFilters['date_to']) {
            [$listingFilters['date_from'], $listingFilters['date_to']] = [
                $listingFilters['date_to'],
                $listingFilters['date_from'],
            ];
        }

        $baseQuery = TrainingSession::with(['sport', 'location', 'registrations' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->whereHas('registrations', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        $this->applyStudentTrainingStatusFilter($baseQuery, $filter);
        $this->applyStudentTrainingListingFilters($baseQuery, $listingFilters);

        $sortStacks = $this->trainingListingSortStacks($request, $view);
        $listQuery = $baseQuery->clone();
        TrainingSessionListingSort::applyToQuery($listQuery, $sortStacks['activeSortStack']);
        $cardsSortStack = $sortStacks['cardsSortStack'];
        $listSortStack = $sortStacks['listSortStack'];

        $allTrainingSessions = $listQuery
            ->paginate($perPage)
            ->withQueryString();

        $sportsForFilter = Sport::orderBy('name')->get();

        $hasSearchFilters = $listingFilters['q'] !== ''
            || ! empty($listingFilters['sport_id'])
            || ! empty($listingFilters['date_from'])
            || ! empty($listingFilters['date_to']);

        return view(
            'training-sessions.student.my',
            compact(
                'allTrainingSessions',
                'filter',
                'listingFilters',
                'sportsForFilter',
                'view',
                'hasSearchFilters',
                'perPage',
                'cardsSortStack',
                'listSortStack',
            )
        );
    }

    /**
     * @param  Builder<TrainingSession>  $query
     */
    protected function applyStudentTrainingStatusFilter(Builder $query, string $filter): void
    {
        if ($filter === 'upcoming') {
            $query->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('status', 'scheduled')
                        ->where('start_time', '>', now());
                });

            return;
        }
        if ($filter === 'completed') {
            $query->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('status', 'completed')
                        ->orWhere('end_time', '<', now());
                });

            return;
        }
        if ($filter === 'cancelled') {
            $query->where('status', 'cancelled');

            return;
        }
        if ($filter === 'in_progress') {
            $query->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('status', 'in_progress')
                        ->orWhere(function ($q2) {
                            $q2->where('start_time', '<=', now())
                                ->where('end_time', '>=', now());
                        });
                });

            return;
        }

        $query->where('status', '!=', 'cancelled');
    }

    /**
     * Фильтр по виду спорту, диапазону дат (пересечение с интервалом тренировки) и поиску по названию.
     *
     * @param  Builder<TrainingSession>  $query
     */
    protected function applyStudentTrainingListingFilters(Builder $query, array $listingFilters): void
    {
        if (($listingFilters['q'] ?? '') !== '') {
            $like = '%'.addcslashes($listingFilters['q'], '%_\\').'%';
            $query->where('title', 'like', $like);
        }
        if (! empty($listingFilters['sport_id'])) {
            $query->where('sport_id', (int) $listingFilters['sport_id']);
        }
        ParticipantListingDateFilter::applyToTrainingSessionQuery(
            $query,
            $listingFilters['date_from'] ?? null,
            $listingFilters['date_to'] ?? null,
        );
    }

    /**
     * Display the specified training session for students.
     */
    public function showStudent(TrainingSession $trainingSession)
    {
        $trainingSession->load(['sport', 'team', 'location', 'registrations.user']);

        // Сортируем регистрации по фамилии и имени
        $trainingSession->registrations = $trainingSession->registrations->sortBy(function ($registration) {
            return $registration->user->lastname.' '.$registration->user->firstname;
        })->values();

        $user = auth()->user();
        $conflictingRegistration = $user->role === 'student'
            ? TrainingRegistrationOverlap::findConflictingRegistration($user, $trainingSession)
            : null;

        return view('training-sessions.student.show', compact('trainingSession', 'conflictingRegistration'));
    }

}

