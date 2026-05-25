<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionResult;
use App\Models\News;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamJoinRequest;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Http\Request;
use App\Support\HomeGallery;

class GuestController extends Controller
{
    /**
     * Главная страница для гостей
     */
    public function index()
    {
        // Публичные новости (+ фильтры для главной)
        $newsQ = trim((string) request()->query('news_q', ''));
        $newsDateFrom = request()->query('news_date_from');
        $newsDateTo = request()->query('news_date_to');

        $publishedNews = News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->when($newsQ !== '', function ($query) use ($newsQ) {
                $query->where(function ($q2) use ($newsQ) {
                    $q2->where('name', 'like', '%'.$newsQ.'%')
                        ->orWhere('description', 'like', '%'.$newsQ.'%');
                });
            })
            ->when(! empty($newsDateFrom), fn ($query) => $query->whereDate('date', '>=', $newsDateFrom))
            ->when(! empty($newsDateTo), fn ($query) => $query->whereDate('date', '<=', $newsDateTo))
            ->latest('date')
            ->take(12)
            ->get();
        
        // Виды спорта (на главной показываем все, без пагинации/слайдера)
        $sports = Sport::query()
            ->orderBy('name')
            ->get();
        
        // Команды (только текущий состав: не выбывшие)
        $teams = Team::with([
            'sport',
            'members' => fn ($q) => $q->whereNull('out')->with('user'),
        ])
            ->orderBy('name')
            ->get();
        
        // Предстоящие соревнования
        $upcomingCompetitions = Competition::with(['sport', 'team', 'location', 'images'])
            ->where('status', 'upcoming')
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->take(6)
            ->get();
        
        // Последние результаты соревнований
        $latestResults = CompetitionResult::with([
            'competition.images',
            'competition.participants',
            'competition.sport', 'competition.team',
            'competition.location',
            'competition.category',
            'team',
        ])
            ->whereHas('competition', function($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->latest('created_at')
            ->take(6)
            ->get();

        // Фотографии карусели на главной — storage/app/public/home (+ порядок в order.json)
        $latestCompetitionPhotos = HomeGallery::photos();
        
        return view('guest.index', compact(
            'publishedNews',
            'newsQ',
            'newsDateFrom',
            'newsDateTo',
            'sports',
            'teams',
            'upcomingCompetitions',
            'latestResults',
            'latestCompetitionPhotos'
        ));
    }

    /**
     * Публичная лента новостей для гостей
     */
    public function news(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $publishedNews = News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            })
            ->when(! empty($dateFrom), fn ($query) => $query->whereDate('date', '>=', $dateFrom))
            ->when(! empty($dateTo), fn ($query) => $query->whereDate('date', '<=', $dateTo))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();
        
        // Если это AJAX-запрос, возвращаем JSON
        if ($request->ajax() || $request->has('ajax')) {
            $html = '';
            $pagination = '';
            
            if ($publishedNews->count() > 0) {
                $html = view('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'guest'])->render();
                if ($publishedNews->hasPages()) {
                    $pagination = $publishedNews->links()->render();
                }
            } else {
                $html = '<div class="px-6 py-12 text-center"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><p class="mt-2 text-sm text-gray-500">Нет опубликованных новостей</p></div>';
            }
            
            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
            ]);
        }
        
        return view('guest.news', compact('publishedNews', 'q', 'dateFrom', 'dateTo'));
    }

    /**
     * Просмотр отдельной новости для гостей
     */
    public function showNews(News $news)
    {
        // Гости могут видеть только опубликованные новости
        if ($news->status !== 'Published') {
            abort(404);
        }
        
        $news->load(['creator', 'images']);

        return view('guest.news-show', compact('news'));
    }

    /**
     * Список видов спорта для гостей
     */
    public function sports(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $sports = Sport::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%');
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();
        
        return view('guest.sports', compact('sports', 'q'));
    }

    /**
     * Просмотр вида спорта для гостей
     */
    public function showSport(Sport $sport)
    {
        $sport->load([
            'teams' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('guest.sport-show', compact('sport'));
    }

    /**
     * Список команд для гостей
     */
    public function teams()
    {
        $teams = Team::with([
            'sport',
            'members' => fn ($q) => $q->whereNull('out')->with('user'),
        ])
            ->orderBy('name')
            ->get();

        $sportIds = $teams->pluck('sport_id')->filter()->unique()->values();
        $sportsForFilter = $sportIds->isEmpty()
            ? collect()
            : Sport::query()->whereIn('id', $sportIds)->orderBy('name')->get();

        // Одна строка в списке на уникальное название (несколько id — если в БД дубли по имени)
        $sportFilterOptions = $sportsForFilter
            ->groupBy(fn (Sport $s) => $s->name)
            ->map(fn ($group) => [
                'name' => $group->first()->name,
                'ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return view('guest.teams', compact('teams', 'sportFilterOptions'));
    }

    /**
     * Просмотр команды для гостей (только публичные составы)
     */
    public function showTeam(Team $team)
    {
        $team->load([
            'members' => fn ($q) => $q->with('user')->orderBy('joined_at', 'desc'),
        ]);

        $joinRequest = null;
        if (auth()->check() && auth()->user()?->role === 'student') {
            $joinRequest = TeamJoinRequest::query()
                ->where('team_id', $team->id)
                ->where('user_id', auth()->id())
                ->where('status', 'pending')
                ->latest('created_at')
                ->first();
        }

        return view('guest.team-show', compact('team', 'joinRequest'));
    }

    /**
     * Список соревнований для гостей
     */
    public function competitions()
    {
        $competitions = Competition::with(['sport', 'team', 'location'])
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(10);
        
        return view('guest.competitions', compact('competitions'));
    }

    /**
     * Просмотр соревнования для гостей
     */
    public function showCompetition(Competition $competition)
    {
        // Гости не могут видеть отмененные соревнования
        if ($competition->status === 'cancelled') {
            abort(404);
        }
        
        $competition->load(['sport', 'team', 'location', 'participants.user', 'images']);
        
        // Убираем "битые" записи без связанного пользователя и сортируем оставшихся
        $competition->participants = $competition->participants
            ->filter(fn ($participant) => $participant->user !== null)
            ->sortBy(function ($participant) {
                return trim(($participant->user->lastname ?? '').' '.($participant->user->firstname ?? ''));
            })
            ->values();
        
        return view('guest.competition-show', compact('competition'));
    }

    /**
     * Публичный профиль участника (доступен при участии в неотменённых соревнованиях;
     * в блоке «Соревнования» выводятся только завершённые).
     */
    public function showUserProfile(User $user)
    {
        $visibleByCompetition = CompetitionParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('competition', function ($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->exists();

        $visibleByTeam = TeamMember::query()
            ->where('user_id', $user->id)
            ->whereNull('out')
            ->exists();

        if (! $visibleByCompetition && ! $visibleByTeam) {
            abort(404);
        }

        $participations = CompetitionParticipant::query()
            ->with(['competition.sport'])
            ->where('user_id', $user->id)
            ->whereHas('competition', function ($q) {
                $q->where('status', 'finished');
            })
            ->get()
            ->sortByDesc(function (CompetitionParticipant $p) {
                $c = $p->competition;

                return $c->end_date?->timestamp
                    ?? $c->start_date?->timestamp
                    ?? 0;
            })
            ->values();

        $view = $user->role === 'teacher' ? 'guest.profile-teacher' : 'guest.profile-student';

        $payload = [
            'user' => $user,
            'participations' => $participations,
        ];

        if ($user->role !== 'teacher') {
            $teamIds = TeamMember::query()
                ->where('user_id', $user->id)
                ->whereNull('out')
                ->pluck('team_id');

            $teamCoaches = $teamIds->isEmpty()
                ? collect()
                : TeamMember::query()
                    ->whereIn('team_id', $teamIds)
                    ->whereNull('out')
                    ->where('type_user', 'coach')
                    ->with('user')
                    ->get()
                    ->pluck('user')
                    ->filter()
                    ->unique('id')
                    ->values();

            $payload['teamCoaches'] = $teamCoaches;
        }

        return view($view, $payload);
    }

    /**
     * Список тренировок для гостей
     */
    public function trainingSessions()
    {
        $trainingSessions = TrainingSession::with(['sport', 'team', 'location'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->paginate(10);
        
        return view('guest.training-sessions', compact('trainingSessions'));
    }

    /**
     * Просмотр тренировки для гостей
     */
    public function showTrainingSession(TrainingSession $trainingSession)
    {
        // Гости не могут видеть отмененные или завершенные тренировки
        if ($trainingSession->status === 'cancelled' || $trainingSession->status === 'completed') {
            abort(404);
        }
        
        $trainingSession->load(['sport', 'team', 'location']);
        
        return view('guest.training-session-show', compact('trainingSession'));
    }

    /**
     * Все результаты соревнований для гостей
     */
    public function results(Request $request)
    {
        $sportIdsWithResults = Competition::query()
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('sport_id')
            ->whereHas('results')
            ->distinct()
            ->pluck('sport_id');

        $sportsForFilter = $sportIdsWithResults->isEmpty()
            ? collect()
            : Sport::query()->whereIn('id', $sportIdsWithResults)->orderBy('name')->get();

        $sportFilterOptions = $sportsForFilter
            ->groupBy(fn (Sport $s) => $s->name)
            ->map(fn ($group) => [
                'name' => $group->first()->name,
                'ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            ])
            ->sortBy('name')
            ->values()
            ->all();

        $allowedSportIds = collect($sportFilterOptions)->pluck('ids')->flatten()->unique()->values()->all();

        $searchQ = mb_substr(trim((string) $request->query('q', '')), 0, 200);

        $rawSport = trim((string) $request->query('sport', ''));
        $filterSportIds = [];
        if ($rawSport !== '') {
            $parsed = array_values(array_unique(array_filter(array_map('intval', explode(',', $rawSport)))));
            $filterSportIds = array_values(array_intersect($parsed, $allowedSportIds));
        }

        $query = CompetitionResult::with([
            'competition.images',
            'competition.participants',
            'competition.sport', 'competition.team',
            'competition.location',
            'competition.category',
            'team',
        ])
            ->whereHas('competition', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });

        if (count($filterSportIds) > 0) {
            $query->whereHas('competition', fn ($q) => $q->whereIn('sport_id', $filterSportIds));
        }

        if ($searchQ !== '') {
            $safe = '%'.addcslashes($searchQ, '%_\\').'%';
            $query->whereHas('competition', fn ($q) => $q->where('name', 'like', $safe));
        }

        $results = $query->latest('created_at')->paginate(20)->withQueryString();

        $selectedSportLabel = 'Все';
        $selectedSportQuery = '';
        if (count($filterSportIds) > 0) {
            $sortedRequest = $filterSportIds;
            sort($sortedRequest);
            $matched = false;
            foreach ($sportFilterOptions as $opt) {
                $ids = $opt['ids'];
                sort($ids);
                if ($ids === $sortedRequest) {
                    $selectedSportLabel = $opt['name'];
                    $selectedSportQuery = implode(',', $ids);
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                sort($filterSportIds);
                $selectedSportQuery = implode(',', $filterSportIds);
                $names = Sport::query()->whereIn('id', $filterSportIds)->orderBy('name')->pluck('name')->unique()->values();
                $selectedSportLabel = $names->isNotEmpty() ? $names->implode(', ') : 'Спорт';
            }
        }

        return view('guest.results', compact(
            'results',
            'sportFilterOptions',
            'selectedSportLabel',
            'selectedSportQuery',
            'searchQ',
        ));
    }
}
