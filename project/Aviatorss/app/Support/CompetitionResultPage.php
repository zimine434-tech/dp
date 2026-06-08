<?php

namespace App\Support;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CompetitionResultPage
{
    public static function loadCompetition(Competition $competition): Competition
    {
        $competition->load([
            'sport',
            'team',
            'location',
            'category',
            'images',
            'participants.user',
            'participants.team.sport',
            'results' => fn ($q) => $q->whereNotNull('place')->where('place', '!=', ''),
            'results.team.sport',
        ]);

        $competition->participants = $competition->participants
            ->filter(fn ($participant) => $participant->user !== null)
            ->sortBy(function ($participant) {
                return trim(($participant->user->lastname ?? '').' '.($participant->user->firstname ?? ''));
            })
            ->values();

        return $competition;
    }

    public static function hasPublishedResults(Competition $competition): bool
    {
        return $competition->results()
            ->whereNotNull('place')
            ->where('place', '!=', '')
            ->exists();
    }

    public static function guestCanView(Competition $competition): bool
    {
        if ($competition->status === 'cancelled') {
            return false;
        }

        return self::hasPublishedResults($competition);
    }

    public static function teacherCanViewResultPage(Competition $competition): bool
    {
        if ($competition->status === 'cancelled') {
            return false;
        }

        return in_array($competition->status, ['finished', 'ongoing'], true);
    }

    public static function applyPublishedResultsQuery(Builder|Relation $query, bool $excludeArchived = false): Builder|Relation
    {
        $query->whereNotNull('place')->where('place', '!=', '');

        if ($excludeArchived) {
            $query->where('is_archive', false);
        }

        return $query;
    }

    /**
     * @param  Builder|Relation  $query
     */
    public static function applyStudentVisibleResultsQuery(Builder|Relation $query, int $userId, bool $excludeArchived = false): Builder|Relation
    {
        self::applyPublishedResultsQuery($query, $excludeArchived);

        return $query->where(function ($q) use ($userId) {
            $q->where(function ($team) use ($userId) {
                $team->where('result_type', 'team')
                    ->whereIn('competitions_id', function ($sub) use ($userId) {
                        $sub->select('competition_id')
                            ->from('competition_participants')
                            ->where('user_id', $userId);
                    });
            })->orWhere('user_id', $userId);
        });
    }

    /**
     * Завершённые соревнования студента: командные (место команды) и личные (своё место), все виды спорта.
     *
     * @return \Illuminate\Support\Collection<int, Competition>
     */
    public static function loadStudentFinishedCompetitions(int $userId, bool $excludeArchived = false)
    {
        $applyPublished = function ($query) use ($excludeArchived) {
            self::applyPublishedResultsQuery($query, $excludeArchived);
        };

        return Competition::with([
            'sport',
            'team',
            'location',
            'category',
            'images',
            'participants.user',
            'participants.team.sport',
            'results' => function ($q) use ($applyPublished) {
                $applyPublished($q);
                $q->with('team.sport');
            },
        ])
            ->where('status', 'finished')
            ->whereHas('participants', fn ($p) => $p->where('user_id', $userId))
            ->whereHas('results', function ($q) use ($userId, $applyPublished) {
                $applyPublished($q);
                $q->where(function ($inner) use ($userId) {
                    $inner->where(function ($team) use ($userId) {
                        $team->where('result_type', 'team')
                            ->whereIn('competitions_id', function ($sub) use ($userId) {
                                $sub->select('competition_id')
                                    ->from('competition_participants')
                                    ->where('user_id', $userId);
                            });
                    })->orWhere('user_id', $userId);
                });
            })
            ->latest('end_date')
            ->get()
            ->map(function (Competition $comp) use ($userId) {
                $visibleResults = $comp->results->filter(function ($result) use ($userId, $comp) {
                    if ($comp->isPersonalCompetition()) {
                        return (int) $result->user_id === (int) $userId;
                    }

                    return ($result->result_type ?? 'team') === 'team';
                })->values();

                $comp->setRelation('results', $visibleResults);

                return $comp;
            })
            ->filter(fn (Competition $comp) => $comp->results->isNotEmpty())
            ->values();
    }

    public static function studentCanView(Competition $competition, User $user): bool
    {
        if ($competition->status === 'cancelled') {
            return false;
        }

        if (! $competition->participants()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $published = fn ($q) => $q->whereNotNull('place')->where('place', '!=', '');

        if ($competition->isPersonalCompetition()) {
            return $competition->results()->where('user_id', $user->id)->where($published)->exists();
        }

        return $competition->results()->where('result_type', 'team')->where($published)->exists();
    }

    public static function resolveSportIdForUser(Competition $competition, ?int $userId = null): ?int
    {
        if ($competition->sport_id) {
            return (int) $competition->sport_id;
        }

        if ($userId && $competition->isPersonalCompetition()) {
            $participant = $competition->participants->first(fn ($p) => (int) $p->user_id === (int) $userId);
            if ($participant?->team?->sport_id) {
                return (int) $participant->team->sport_id;
            }

            $personalResult = $competition->results->first(
                fn ($r) => ($r->result_type ?? '') === 'personal' && (int) $r->user_id === (int) $userId
            );
            if ($personalResult?->team?->sport_id) {
                return (int) $personalResult->team->sport_id;
            }
        }

        if ($competition->team?->sport_id) {
            return (int) $competition->team->sport_id;
        }

        if ($competition->isPersonalCompetition()) {
            foreach ($competition->participants as $participant) {
                if ($participant->team?->sport_id) {
                    return (int) $participant->team->sport_id;
                }
            }
        }

        return null;
    }

    public static function resolveSportNameForUser(Competition $competition, ?int $userId = null): string
    {
        if ($competition->sport?->name) {
            return $competition->sport->name;
        }

        if ($userId && $competition->isPersonalCompetition()) {
            $participant = $competition->participants->first(fn ($p) => (int) $p->user_id === (int) $userId);
            if ($participant?->team?->sport?->name) {
                return $participant->team->sport->name;
            }
        }

        if ($competition->team?->sport?->name) {
            return $competition->team->sport->name;
        }

        if ($competition->isPersonalCompetition()) {
            foreach ($competition->participants as $participant) {
                if ($participant->team?->sport?->name) {
                    return $participant->team->sport->name;
                }
            }
        }

        return '—';
    }

    public static function isPersonalResultListing(Competition $competition, ?\App\Models\CompetitionResult $result = null): bool
    {
        if ($competition->isPersonalCompetition()) {
            return true;
        }

        return $result !== null && (int) ($result->user_id ?? 0) > 0;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\CompetitionResult>
     */
    public static function sortedResultsForListing(Competition $competition)
    {
        return $competition->results
            ->filter(fn ($r) => filled(trim((string) ($r->place ?? ''))))
            ->when($competition->isPersonalCompetition(), function ($collection) {
                return $collection->filter(
                    fn ($r) => (int) ($r->user_id ?? 0) > 0 || ($r->result_type ?? '') === 'personal'
                );
            })
            ->sortBy(function ($r) {
                if (is_numeric($r->place)) {
                    return (int) $r->place;
                }

                return 9999 + ord($r->place[0] ?? 'z');
            })
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  $competitions
     * @return \Illuminate\Support\Collection<int, array{competition: Competition, result: \App\Models\CompetitionResult}>
     */
    public static function expandCompetitionsToResultListingRows($competitions)
    {
        $rows = collect();

        foreach ($competitions as $competition) {
            foreach (self::sortedResultsForListing($competition) as $result) {
                $rows->push([
                    'competition' => $competition,
                    'result' => $result,
                ]);
            }
        }

        return $rows;
    }

    public static function formatResultParticipantName(Competition $competition, \App\Models\CompetitionResult $result): string
    {
        $user = $result->user;

        if (! $user && $result->user_id) {
            $user = $competition->participants
                ->first(fn ($p) => (int) $p->user_id === (int) $result->user_id)
                ?->user;
        }

        if (! $user) {
            return '—';
        }

        return trim($user->lastname.' '.$user->firstname.' '.($user->patronymic ?? ''));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  $competitions
     * @return \Illuminate\Support\Collection<int, Sport>
     */
    public static function collectSportsForCompetitionsFilter($competitions, ?int $userId = null)
    {
        $sportIds = collect();

        foreach ($competitions as $comp) {
            $sportIds->push(self::resolveSportIdForUser($comp, $userId));

            if ($userId) {
                $participant = $comp->participants->first(fn ($p) => (int) $p->user_id === (int) $userId);
                if ($participant?->team?->sport_id) {
                    $sportIds->push((int) $participant->team->sport_id);
                }
            }

            foreach ($comp->participants as $participant) {
                if ($participant->team?->sport_id) {
                    $sportIds->push((int) $participant->team->sport_id);
                }
            }

            foreach ($comp->results as $result) {
                if ($result->team?->sport_id) {
                    $sportIds->push((int) $result->team->sport_id);
                }
            }
        }

        $sportIds = $sportIds->filter()->unique()->values();

        if ($sportIds->isEmpty()) {
            return collect();
        }

        return Sport::query()->whereIn('id', $sportIds)->orderBy('name')->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  $competitions
     * @return \Illuminate\Support\Collection<int, Sport>
     */
    public static function collectSportsForStudentFilter($competitions, int $userId)
    {
        return self::collectSportsForCompetitionsFilter($competitions, $userId);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Competition>  $competitions
     * @return \Illuminate\Support\Collection<int, CompetitionCategory>
     */
    public static function collectCategoriesForCompetitionsFilter($competitions)
    {
        $categoryIds = $competitions
            ->pluck('competition_category_id')
            ->filter()
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return CompetitionCategory::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('name_category')
            ->get();
    }
}
