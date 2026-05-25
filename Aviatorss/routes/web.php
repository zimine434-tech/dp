<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HomeCarouselPhotoController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LocationTrainingController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamJoinRequestController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingRegistrationController;
use App\Http\Middleware\EnsureAllowedRoles;
use App\Support\ParticipantListingDateFilter;
use App\Models\Competition;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

// Главная страница - для гостей показываем публичный контент, для авторизованных перенаправляем на dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return app(GuestController::class)->index();
})->name('home');

// Публичные маршруты для гостей
Route::get('/guest/news', [GuestController::class, 'news'])->name('guest.news');
Route::get('/guest/news/{news}', [GuestController::class, 'showNews'])->name('guest.news.show');
Route::get('/guest/sports', [GuestController::class, 'sports'])->name('guest.sports');
Route::get('/guest/sports/{sport}', [GuestController::class, 'showSport'])->name('guest.sports.show');
Route::get('/guest/teams', [GuestController::class, 'teams'])->name('guest.teams');
Route::get('/guest/teams/{team}', [GuestController::class, 'showTeam'])->name('guest.teams.show');
Route::get('/guest/competitions', [GuestController::class, 'competitions'])->name('guest.competitions');
Route::get('/guest/competitions/{competition}', [GuestController::class, 'showCompetition'])->name('guest.competitions.show');
Route::get('/guest/results', [GuestController::class, 'results'])->name('guest.results');
Route::get('/guest/training-sessions', [GuestController::class, 'trainingSessions'])->name('guest.training-sessions');
Route::get('/guest/training-sessions/{trainingSession}', [GuestController::class, 'showTrainingSession'])->name('guest.training-sessions.show');
Route::get('/guest/users/{user}', [GuestController::class, 'showUserProfile'])->name('guest.users.show');

// Public routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Выход без auth: при истёкшей сессии POST с устаревшим CSRF всё равно можно завершить через GET.
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', EnsureAllowedRoles::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Новости
        $publishedNews = \App\Models\News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->latest('date')
            ->take(3)
            ->get();
        
        // Ближайшие соревнования
        $upcomingCompetitions = \App\Models\Competition::with(['sport', 'team', 'location', 'images'])
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get();
        
        // Ближайшие тренировки
        $upcomingTrainingSessions = \App\Models\TrainingSession::with(['sport', 'team', 'location'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(3)
            ->get();
        
        $view = $user->role === 'teacher' ? 'dashboard-teacher' : 'dashboard-student';
        return view($view, [
            'user' => $user, 
            'publishedNews' => $publishedNews,
            'upcomingCompetitions' => $upcomingCompetitions,
            'upcomingTrainingSessions' => $upcomingTrainingSessions
        ]);
    })->name('dashboard');
    
    Route::get('/profile', function () {
        $user = auth()->user();
        $view = $user->role === 'teacher' ? 'profile-teacher' : 'profile-student';
        return view($view, ['user' => $user]);
    })->name('profile');
    Route::get('/profile/avatar/edit', [AuthController::class, 'editAvatar'])->name('profile.avatar.edit');
    Route::post('/profile/avatar', [AuthController::class, 'updateAvatar'])->name('profile.avatar');

    // История участия (студенты; совпадает со счётчиками в блоке «Статистика» профиля)
    Route::middleware([EnsureAllowedRoles::class . ':student'])->group(function () {
        Route::get('/profile/participations/teams', function () {
            $user = auth()->user();
            $memberships = $user->teamMembersForProfileParticipationListing()
                ->sortByDesc(fn ($m) => $m->joined_at?->timestamp ?? 0)
                ->values();

            return view('profile.participations.teams', compact('user', 'memberships'));
        })->name('profile.participations.teams');

        Route::get('/profile/participations/competitions', function (Request $request) {
            $listingFilters = $request->validate([
                'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to' => ['nullable', 'date_format:Y-m-d'],
            ]);

            $user = auth()->user();
            $sportId = isset($listingFilters['sport_id']) ? (int) $listingFilters['sport_id'] : null;
            $dateFrom = $listingFilters['date_from'] ?? null;
            $dateTo = $listingFilters['date_to'] ?? null;
            $filteredCompetitions = $user->competitionParticipants()
                ->with(['competition.sport', 'competition.team', 'competition.location'])
                ->get()
                ->filter(function ($p) use ($sportId, $dateFrom, $dateTo) {
                    $c = $p->competition;
                    if (! $c) {
                        return false;
                    }
                    if ($c->status !== 'finished') {
                        return false;
                    }
                    if ($sportId && (int) $c->sport_id !== $sportId) {
                        return false;
                    }
                    if (! ParticipantListingDateFilter::competitionIntervalsOverlap(
                        $c->start_date,
                        $c->end_date,
                        $dateFrom,
                        $dateTo
                    )) {
                        return false;
                    }

                    return true;
                })
                ->sortByDesc(function ($p) {
                    $c = $p->competition;

                    return optional($c?->end_date)->timestamp
                        ?? optional($c?->start_date)->timestamp
                        ?? 0;
                })
                ->values();

            $perPage = 20;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $participations = new LengthAwarePaginator(
                $filteredCompetitions->slice(($page - 1) * $perPage, $perPage)->values(),
                $filteredCompetitions->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $sportsForFilter = Sport::orderBy('name')->get();

            return view('profile.participations.competitions', compact(
                'user',
                'participations',
                'listingFilters',
                'sportsForFilter'
            ));
        })->name('profile.participations.competitions');

        Route::get('/profile/participations/trainings', function (Request $request) {
            $listingFilters = $request->validate([
                'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to' => ['nullable', 'date_format:Y-m-d'],
            ]);
            $user = auth()->user();
            $sportId = isset($listingFilters['sport_id']) ? (int) $listingFilters['sport_id'] : null;
            $dateFrom = $listingFilters['date_from'] ?? null;
            $dateTo = $listingFilters['date_to'] ?? null;
            $filteredRegistrations = $user->trainingRegistrations()
                ->with(['training.sport', 'training.team', 'training.location'])
                ->latest('registered_at')
                ->get()
                ->filter(function ($r) use ($sportId, $dateFrom, $dateTo) {
                    $t = $r->training;
                    if (! $t || ! $t->isParticipantHistoryFinished()) {
                        return false;
                    }
                    if ($sportId && (int) $t->sport_id !== $sportId) {
                        return false;
                    }
                    if (! ParticipantListingDateFilter::trainingSessionIntervalsOverlap(
                        $t->start_time,
                        $t->end_time,
                        $dateFrom,
                        $dateTo
                    )) {
                        return false;
                    }

                    return true;
                })
                ->sortByDesc(fn ($r) => $r->training?->end_time?->timestamp ?? 0)
                ->values();

            $perPage = 20;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $registrations = new LengthAwarePaginator(
                $filteredRegistrations->slice(($page - 1) * $perPage, $perPage)->values(),
                $filteredRegistrations->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $sportsForFilter = Sport::orderBy('name')->get();

            return view('profile.participations.trainings', compact('user', 'registrations', 'listingFilters', 'sportsForFilter'));
        })->name('profile.participations.trainings');
    });

    // Маршруты для команд
    Route::get('/teams', function () {
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return app(TeamController::class)->index();
        } else {
            return app(TeamController::class)->indexStudent();
        }
    })->name('teams.index')->middleware(['auth', EnsureAllowedRoles::class]);

    // Маршруты для соревнований
    Route::get('/competitions', function (Request $request) {
        $controller = new CompetitionController();
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $controller->index($request);
        } else {
            return $controller->indexStudent($request);
        }
    })->name('competitions.index')->middleware(['auth', EnsureAllowedRoles::class]);

    // Страница результатов соревнований
    Route::get('/competitions/results', [CompetitionController::class, 'results'])->name('competitions.results')->middleware(['auth', EnsureAllowedRoles::class]);

    // Маршруты для тренировочных сессий
    Route::get('/training-sessions', function (Request $request) {
        $controller = new TrainingSessionController();
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $controller->index($request);
        } else {
            return $controller->indexStudent($request);
        }
    })->name('training-sessions.index')->middleware(['auth', EnsureAllowedRoles::class]);
    
    // Отдельный маршрут для студентов 
    Route::get('/training-sessions/student', function (Request $request) {
        return app(TrainingSessionController::class)->indexStudent($request);
    })->name('training-sessions.student.index')->middleware(['auth', EnsureAllowedRoles::class . ':student']);

    // Явно определяем create перед параметризованным маршрутом, чтобы избежать конфликта
    Route::middleware(['auth', EnsureAllowedRoles::class . ':teacher'])->group(function () {
        Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
        Route::get('/competitions/photo-archive', [CompetitionController::class, 'photoArchive'])->name('competitions.photo-archive');
        Route::delete('/competitions/photo-archive/{competitionImage}', [CompetitionController::class, 'destroyCompetitionPhoto'])->name('competitions.photo-archive.destroy');
        Route::get('/training-sessions/create', [TrainingSessionController::class, 'create'])->name('training-sessions.create');
    });

    Route::get('/competitions/{competition}', function (Competition $competition) {
        $controller = new CompetitionController();
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $controller->show($competition);
        } else {
            return $controller->showStudent($competition);
        }
    })->name('competitions.show')->middleware(['auth', EnsureAllowedRoles::class]);

    Route::get('/training-sessions/{trainingSession}', function (TrainingSession $trainingSession) {
        $controller = new TrainingSessionController();
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $controller->show($trainingSession);
        } else {
            return $controller->showStudent($trainingSession);
        }
    })->name('training-sessions.show')->middleware(['auth', EnsureAllowedRoles::class]);

    // Маршруты для регистрации на тренировки (только для студентов)
    Route::middleware(['auth', EnsureAllowedRoles::class . ':student'])->group(function () {
        Route::post('/training-sessions/{trainingSession}/register', [TrainingRegistrationController::class, 'register'])->name('training-sessions.register');
        Route::post('/training-sessions/{trainingSession}/unregister', [TrainingRegistrationController::class, 'unregister'])->name('training-sessions.unregister');
        Route::post('/competitions/{competition}/apply', [CompetitionController::class, 'applyStudent'])->name('competitions.apply');

        // Заявки на вступление в команду
        Route::post('/guest/teams/{team}/join-requests', [TeamJoinRequestController::class, 'store'])->name('guest.teams.join-requests.store');
        Route::post('/teams/{team}/join-requests', [TeamJoinRequestController::class, 'store'])->name('teams.join-requests.store');
    });

    // Маршруты для новостей (разные для студентов и преподавателей)
    Route::get('/news', function () {
        $controller = new NewsController();
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return $controller->index(request());
        } else {
            return $controller->indexStudent(request());
        }
    })->name('news.index')->middleware(['auth', EnsureAllowedRoles::class]);

    // Маршруты для команд для преподавателей
    Route::middleware(['auth', EnsureAllowedRoles::class . ':teacher'])->group(function () {
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::patch('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
        Route::get('/teams/{team}/search-students', [TeamController::class, 'searchStudents'])->name('teams.search-students');
        Route::put('/teams/{team}/members/{member}', [TeamController::class, 'updateMemberRole'])->name('teams.members.update-role');
        Route::post('/teams/{team}/members/{member}/accept', [TeamController::class, 'acceptMember'])->name('teams.members.accept');
        Route::delete('/teams/{team}/members/{member}', [TeamController::class, 'removeMember'])->name('teams.members.remove');

        // Студенты (без массовой синхронизации из LDAP)
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/groups/{groupName}/schedule', [StudentController::class, 'groupSchedule'])->name('students.groups.schedule');
        Route::post('/students/{student}/toggle-fizorg', [StudentController::class, 'toggleFizorg'])->name('students.toggle-fizorg')->whereNumber('student');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show')->whereNumber('student');

        // Заявки на вступление в команду (для тренера команды)
        Route::get('/teams/{team}/join-requests', [TeamJoinRequestController::class, 'index'])->name('teams.join-requests.index');
        Route::post('/teams/{team}/join-requests/{joinRequest}/approve', [TeamJoinRequestController::class, 'approve'])->name('teams.join-requests.approve');
        Route::post('/teams/{team}/join-requests/{joinRequest}/reject', [TeamJoinRequestController::class, 'reject'])->name('teams.join-requests.reject');

        // Карусель фото на публичной главной (storage/home)
        Route::get('/home-carousel-photos', [HomeCarouselPhotoController::class, 'index'])->name('home-carousel-photos.index');
        Route::post('/home-carousel-photos', [HomeCarouselPhotoController::class, 'store'])->name('home-carousel-photos.store');
        Route::put('/home-carousel-photos/order', [HomeCarouselPhotoController::class, 'updateOrder'])->name('home-carousel-photos.order');
        Route::delete('/home-carousel-photos', [HomeCarouselPhotoController::class, 'destroy'])->name('home-carousel-photos.destroy');
        
        // Маршруты для спорта
        Route::resource('sports', SportController::class);
        
        // Маршруты для новостей
        Route::resource('news', NewsController::class)->except(['index', 'show']);
        Route::post('/news/{news}/publish', [NewsController::class, 'publish'])->name('news.publish');
        
        // Маршруты для соревнований (только для преподавателей)
        Route::resource('competitions', CompetitionController::class)->except(['index', 'show', 'create']);
        Route::post('/competitions/{competition}/cancel', [CompetitionController::class, 'cancel'])->name('competitions.cancel');
        
        // Маршруты для результатов соревнований
        Route::post('/competitions/{competition}/results', [CompetitionController::class, 'storeResult'])->name('competitions.results.store');
        Route::put('/competitions/{competition}/results/{result}', [CompetitionController::class, 'updateResult'])->name('competitions.results.update');
        Route::delete('/competitions/{competition}/results/{result}', [CompetitionController::class, 'destroyResult'])->name('competitions.results.destroy');
        Route::get('/competitions/{competition}/photos', [CompetitionController::class, 'photos'])->name('competitions.photos');
        Route::post('/competitions/{competition}/photos', [CompetitionController::class, 'storePhotos'])->name('competitions.photos.store');
        
        // Маршруты для тренировочных сессий (только для преподавателей)
        Route::resource('training-sessions', TrainingSessionController::class)->except(['index', 'show', 'create']);
        Route::post('/training-sessions/{trainingSession}/cancel', [TrainingSessionController::class, 'cancel'])->name('training-sessions.cancel');
        
        // Маршруты для локаций тренировок
        Route::post('/location-trainings', [LocationTrainingController::class, 'store'])->name('location-trainings.store');
        Route::put('/location-trainings/{locationTraining}', [LocationTrainingController::class, 'update'])->name('location-trainings.update');
        Route::delete('/location-trainings/{locationTraining}', [LocationTrainingController::class, 'destroy'])->name('location-trainings.destroy');
        
        // Маршруты для локаций соревнований
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        
        // Маршруты для категорий соревнований
        Route::post('/competition-categories', [\App\Http\Controllers\CompetitionCategoryController::class, 'store'])->name('competition-categories.store');
        Route::put('/competition-categories/{category}', [\App\Http\Controllers\CompetitionCategoryController::class, 'update'])->name('competition-categories.update');
        Route::delete('/competition-categories/{category}', [\App\Http\Controllers\CompetitionCategoryController::class, 'destroy'])->name('competition-categories.destroy');
        
        // Заявки студентов на соревнование
        Route::post('/competitions/{competition}/applications/{application}/accept', [CompetitionController::class, 'acceptCompetitionApplication'])->name('competitions.applications.accept');
        Route::post('/competitions/{competition}/applications/{application}/reject', [CompetitionController::class, 'rejectCompetitionApplication'])->name('competitions.applications.reject');

        // Маршруты для управления участниками соревнований
        Route::post('/competitions/{competition}/participants', [CompetitionController::class, 'addParticipant'])->name('competitions.participants.add');
        Route::put('/competitions/{competition}/participants/{userId}/role', [CompetitionController::class, 'updateParticipantRole'])->name('competitions.participants.update-role');
        Route::delete('/competitions/{competition}/participants/{user}', [CompetitionController::class, 'removeParticipant'])->name('competitions.participants.remove');
        Route::get('/competitions/{competition}/search-students', [CompetitionController::class, 'searchStudents'])->name('competitions.search-students');
        
        // Маршруты для генерации приказов
        Route::post('/competitions/{competition}/generate-order-1', [CompetitionController::class, 'generateOrder1'])->name('competitions.generate-order-1');
        Route::post('/competitions/{competition}/generate-order-2', [CompetitionController::class, 'generateOrder2'])->name('competitions.generate-order-2');
        Route::post('/competitions/{competition}/generate-order-3', [CompetitionController::class, 'generateOrder3'])->name('competitions.generate-order-3');
    });
    
    // Маршрут для просмотра новости (доступен для всех) - должен быть ПОСЛЕ resource маршрутов
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show')->middleware(['auth', EnsureAllowedRoles::class]);
    
    // Маршрут для просмотра команды (должен быть ПОСЛЕ всех специфичных маршрутов)
    Route::get('/teams/{team}', function (Team $team) {
        $user = auth()->user();
        if ($user->role === 'teacher') {
            return app(TeamController::class)->show($team);
        } else {
            return app(TeamController::class)->showStudent($team);
        }
    })->name('teams.show')->middleware(['auth', EnsureAllowedRoles::class]);

});
