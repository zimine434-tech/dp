<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionResult;
use App\Models\Sport;
use App\Models\User;
use App\Support\ParticipantListingDateFilter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class StudentPortfolioController extends Controller
{
    public static function participationKind(CompetitionResult $result): string
    {
        if ((string) ($result->result_type ?? '') === 'personal') {
            return 'personal';
        }

        if ((string) ($result->competition?->result_type ?? 'team') === 'personal') {
            return 'personal';
        }

        return 'team';
    }

    /**
     * @return array<string, mixed>
     */
    public static function portfolioListQueryFromRequest(Request $request): array
    {
        return array_filter([
            'q' => $request->query('q'),
            'sport_id' => $request->query('sport_id'),
            'participation_type' => $request->query('participation_type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'view' => $request->query('view'),
            'per_page' => $request->query('per_page'),
            'page' => $request->query('page'),
        ], static fn ($v) => $v !== null && $v !== '');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'sport_id' => ['nullable', 'integer', Rule::exists('sports', 'id')],
            'participation_type' => ['nullable', Rule::in(['personal', 'team'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['list', 'cards'])],
            'per_page' => ['nullable', 'integer'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $sportId = isset($validated['sport_id']) ? (int) $validated['sport_id'] : null;
        $participationType = (string) ($validated['participation_type'] ?? $request->query('participation_type', ''));
        if (! in_array($participationType, ['personal', 'team'], true)) {
            $participationType = '';
        }
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }
        $view = (($validated['view'] ?? 'list') === 'cards') ? 'cards' : 'list';

        $baseResults = $this->loadPortfolioResults($user, $participationType);

        $sportIds = $baseResults
            ->map(fn (CompetitionResult $r) => $this->resolvePortfolioSportId($r))
            ->filter()
            ->unique()
            ->values();

        $sportsForFilter = Sport::query()
            ->whereIn('id', $sportIds)
            ->orderBy('name')
            ->get();

        if ($sportId !== null && ! $sportsForFilter->contains('id', $sportId)) {
            $sportId = null;
        }

        $filteredResults = $baseResults
            ->filter(function (CompetitionResult $r) use ($q, $dateFrom, $dateTo, $sportId, $participationType) {
                if ($participationType !== '' && self::participationKind($r) !== $participationType) {
                    return false;
                }

                $c = $r->competition;
                if (! $c) {
                    return false;
                }

                if ($q !== '') {
                    $needle = mb_strtolower($q);
                    if (! str_contains(mb_strtolower((string) $c->name), $needle)) {
                        return false;
                    }
                }

                if ($sportId !== null && $this->resolvePortfolioSportId($r) !== $sportId) {
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
            ->sortByDesc(fn (CompetitionResult $r) => $r->competition?->end_date?->timestamp ?? $r->competition?->start_date?->timestamp ?? 0)
            ->values();

        $perPage = (int) ($validated['per_page'] ?? $request->query('per_page', 10));
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $results = new LengthAwarePaginator(
            $filteredResults->slice(($page - 1) * $perPage, $perPage)->values(),
            $filteredResults->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $portfolioListQuery = self::portfolioListQueryFromRequest($request);

        return view('profile.portfolio', compact(
            'user',
            'results',
            'q',
            'sportId',
            'participationType',
            'sportsForFilter',
            'dateFrom',
            'dateTo',
            'view',
            'perPage',
            'portfolioListQuery',
        ));
    }

    public function show(Request $request, Competition $competition)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'participation' => ['required', Rule::in(['personal', 'team'])],
        ]);

        $participation = $validated['participation'];

        $result = $this->loadPortfolioResults($user, $participation)
            ->first(fn (CompetitionResult $r) => (int) $r->competitions_id === (int) $competition->id
                && self::participationKind($r) === $participation);

        if (! $result) {
            abort(404);
        }

        $competition->load(['sport', 'team', 'location', 'category']);
        $result->load(['team.sport']);

        $portfolioListQuery = self::portfolioListQueryFromRequest($request);
        $isPersonal = $participation === 'personal';

        $sportName = $isPersonal
            ? ($result->team?->sport?->name ?? $competition->sport?->name)
            : ($competition->sport?->name ?? $result->team?->sport?->name ?? $competition->team?->sport?->name);

        $datesLabel = $this->formatCompetitionDates($competition);

        return view('profile.portfolio-show', compact(
            'user',
            'competition',
            'result',
            'participation',
            'isPersonal',
            'sportName',
            'datesLabel',
            'portfolioListQuery',
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, CompetitionResult>
     */
    private function loadPortfolioResults(User $user, string $participationType = '')
    {
        $personalResults = collect();
        $teamResults = collect();

        if ($participationType !== 'team') {
            $personalResults = CompetitionResult::query()
                ->with(['competition.sport', 'competition.team', 'competition.category', 'competition.location', 'team.sport'])
                ->where('result_type', 'personal')
                ->where('user_id', $user->id)
                ->whereIn('place', ['1', '2', '3'])
                ->whereHas('competition', fn ($c) => $c->where('status', 'finished'))
                ->get();
        }

        if ($participationType !== 'personal') {
            $teamResults = CompetitionResult::query()
                ->with(['competition.sport', 'competition.team', 'competition.category', 'competition.location', 'team.sport'])
                ->where('result_type', 'team')
                ->whereIn('place', ['1', '2', '3'])
                ->whereHas('competition', function ($c) use ($user) {
                    $c->where('status', 'finished')
                        ->where('result_type', '!=', 'personal')
                        ->whereHas('participants', fn ($p) => $p->where('user_id', $user->id));
                })
                ->get();
        }

        return $personalResults
            ->concat($teamResults)
            ->filter(function (CompetitionResult $r) {
                $place = trim((string) ($r->place ?? ''));

                return is_numeric($place) && (int) $place >= 1 && (int) $place <= 3;
            })
            ->unique(fn (CompetitionResult $r) => ($r->competitions_id ?? 0).':'.($r->result_type ?? ''))
            ->values();
    }

    private function resolvePortfolioSportId(CompetitionResult $r): ?int
    {
        if (($r->result_type ?? '') === 'personal') {
            if ($r->team?->sport_id) {
                return (int) $r->team->sport_id;
            }
            $c = $r->competition;
            if ($c?->sport_id) {
                return (int) $c->sport_id;
            }

            return null;
        }

        $c = $r->competition;
        if ($c?->sport_id) {
            return (int) $c->sport_id;
        }
        if ($r->team?->sport_id) {
            return (int) $r->team->sport_id;
        }
        if ($c?->team?->sport_id) {
            return (int) $c->team->sport_id;
        }

        return null;
    }

    private function formatCompetitionDates(Competition $competition): string
    {
        if (! $competition->start_date || ! $competition->end_date) {
            return '—';
        }

        if ($competition->start_date->format('d.m.Y') === $competition->end_date->format('d.m.Y')) {
            return $competition->start_date->format('d.m.Y');
        }

        return $competition->start_date->format('d.m.Y').' – '.$competition->end_date->format('d.m.Y');
    }
}
