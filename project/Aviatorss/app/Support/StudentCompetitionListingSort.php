<?php

namespace App\Support;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentCompetitionListingSort
{
    public const FIELDS = ['name', 'sport', 'start_date', 'location'];

    public const PREFIX_CARDS = 'cards';

    public const PREFIX_LIST = 'list';

    /** @var list<string> */
    public const LIST_FIELDS = ['name', 'start_date'];

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function parseStack(Request $request, string $prefix): array
    {
        $rawSort = $request->input($prefix.'_sort');

        if ($rawSort === 'none') {
            return [];
        }

        $fields = $rawSort ?? [];
        $orders = $request->input($prefix.'_order', []);

        if (! is_array($fields)) {
            $fields = $fields !== null && $fields !== '' ? [(string) $fields] : [];
        }
        if (! is_array($orders)) {
            $orders = $orders !== null && $orders !== '' ? [(string) $orders] : [];
        }

        $stack = [];
        foreach (array_values($fields) as $index => $field) {
            $field = (string) $field;
            if (! in_array($field, self::FIELDS, true)) {
                continue;
            }

            $order = strtolower((string) ($orders[$index] ?? 'asc'));
            if (! in_array($order, ['asc', 'desc'], true)) {
                $order = 'asc';
            }

            $stack[] = ['field' => $field, 'order' => $order];
        }

        if ($stack !== []) {
            return $stack;
        }

        if ($request->query->has($prefix.'_sort') || $request->query->has($prefix.'_order')) {
            return [];
        }

        return self::defaultStack();
    }

    public static function isCleared(array $stack): bool
    {
        return $stack === [];
    }

    /**
     * В таблице — сортировка по названию и дате.
     *
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<int, array{field: string, order: string}>
     */
    public static function normalizeListStack(array $stack): array
    {
        if ($stack === []) {
            return [];
        }

        $filtered = array_values(array_filter(
            $stack,
            static fn (array $item): bool => in_array($item['field'], self::LIST_FIELDS, true)
        ));

        if ($filtered !== []) {
            return $filtered;
        }

        return self::defaultStack();
    }

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function defaultStack(): array
    {
        return [['field' => 'start_date', 'order' => 'desc']];
    }

    public static function isOnlyDefaultStack(array $stack): bool
    {
        return $stack === self::defaultStack();
    }

    /**
     * @param  Builder<\App\Models\Competition>  $query
     * @param  array<int, array{field: string, order: string}>  $stack
     */
    public static function applyToQuery(Builder $query, array $stack): void
    {
        if ($stack === []) {
            if (empty($query->getQuery()->columns)) {
                $query->select('competitions.*');
            }
            $query->orderBy('competitions.id', 'desc');

            return;
        }

        if (empty($query->getQuery()->columns)) {
            $query->select('competitions.*');
        }

        $fields = array_column($stack, 'field');
        $needsLocation = in_array('location', $fields, true);
        $needsSport = in_array('sport', $fields, true);

        if ($needsLocation) {
            $query->leftJoin('locations as sort_locations', 'competitions.location_id', '=', 'sort_locations.id');
        }

        if ($needsSport) {
            $query->leftJoin('teams as sort_teams', 'competitions.team_id', '=', 'sort_teams.id')
                ->leftJoin('sports as sort_sports_direct', 'competitions.sport_id', '=', 'sort_sports_direct.id')
                ->leftJoin('sports as sort_sports_team', 'sort_teams.sport_id', '=', 'sort_sports_team.id');
        }

        foreach ($stack as $item) {
            $dir = $item['order'] === 'asc' ? 'asc' : 'desc';

            if ($item['field'] === 'name') {
                $query->orderBy('competitions.name', $dir);
            } elseif ($item['field'] === 'location') {
                $query->orderBy('sort_locations.location', $dir);
            } elseif ($item['field'] === 'sport') {
                $query->orderByRaw('COALESCE(sort_sports_direct.name, sort_sports_team.name) '.$dir);
            } elseif ($item['field'] === 'start_date') {
                $query->orderBy('competitions.start_date', $dir);
            }
        }

        $tieDir = $stack[0]['order'] === 'asc' ? 'asc' : 'desc';
        $query->orderBy('competitions.id', $tieDir);
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<int, array{field: string, order: string}>
     */
    public static function toggleCardCriterion(array $stack, string $field, string $order): array
    {
        if (! in_array($field, self::FIELDS, true)) {
            return $stack;
        }

        $order = $order === 'desc' ? 'desc' : 'asc';

        $wasActive = false;
        foreach ($stack as $item) {
            if ($item['field'] === $field && $item['order'] === $order) {
                $wasActive = true;
                break;
            }
        }

        $stack = array_values(array_filter(
            $stack,
            fn (array $item): bool => $item['field'] !== $field
        ));

        if ($wasActive) {
            return $stack;
        }

        if ($stack === [] || self::isOnlyDefaultStack($stack)) {
            return [['field' => $field, 'order' => $order]];
        }

        $stack[] = ['field' => $field, 'order' => $order];

        return $stack;
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<int, array{field: string, order: string}>
     */
    public static function cycleTableColumn(array $stack, string $field, string $defaultOrder = 'asc'): array
    {
        if (! in_array($field, self::FIELDS, true)) {
            return $stack;
        }

        $defaultOrder = $defaultOrder === 'desc' ? 'desc' : 'asc';
        $index = null;
        $currentOrder = null;

        foreach ($stack as $i => $item) {
            if ($item['field'] === $field) {
                $index = $i;
                $currentOrder = $item['order'];
                break;
            }
        }

        if ($index === null) {
            if ($stack === [] || self::isOnlyDefaultStack($stack)) {
                return [['field' => $field, 'order' => $defaultOrder]];
            }

            $stack[] = ['field' => $field, 'order' => $defaultOrder];

            return $stack;
        }

        if ($currentOrder === 'asc') {
            $stack[$index]['order'] = 'desc';

            return $stack;
        }

        array_splice($stack, $index, 1);

        return array_values($stack);
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<string, array<int, string>>
     */
    public static function queryParamsForPrefix(string $prefix, array $stack): array
    {
        if ($stack === []) {
            return [$prefix.'_sort' => 'none'];
        }

        $default = self::defaultStack();
        if (count($stack) === 1
            && $stack[0]['field'] === $default[0]['field']
            && $stack[0]['order'] === $default[0]['order']) {
            return [];
        }

        return [
            $prefix.'_sort' => array_column($stack, 'field'),
            $prefix.'_order' => array_column($stack, 'order'),
        ];
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $cardsStack
     * @param  array<int, array{field: string, order: string}>  $listStack
     * @return array<string, mixed>
     */
    public static function mergeQueryParams(array $base, array $cardsStack, array $listStack): array
    {
        return array_merge(
            $base,
            self::queryParamsForPrefix(self::PREFIX_CARDS, $cardsStack),
            self::queryParamsForPrefix(self::PREFIX_LIST, $listStack)
        );
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<int, array{field: string, order: string}>  $cardsStack
     * @param  array<int, array{field: string, order: string}>  $listStack
     * @param  array<string, mixed>  $extra
     */
    public static function listingUrl(string $routeName, array $base, array $cardsStack, array $listStack, array $extra = []): string
    {
        $params = array_merge(
            self::mergeQueryParams($base, $cardsStack, $listStack),
            $extra
        );

        $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return route($routeName).($query !== '' ? '?'.$query : '');
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     */
    public static function orderForField(array $stack, string $field): ?string
    {
        foreach ($stack as $item) {
            if ($item['field'] === $field) {
                return $item['order'];
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, Competition>  $competitions
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return Collection<int, Competition>
     */
    public static function sortCompetitionCollection(Collection $competitions, array $stack): Collection
    {
        if ($stack === []) {
            return $competitions->sortByDesc(fn (Competition $c) => $c->id)->values();
        }

        return $competitions
            ->sort(function (Competition $a, Competition $b) use ($stack): int {
                foreach ($stack as $item) {
                    $cmp = self::compareCompetitions($a, $b, $item['field']);
                    if ($cmp !== 0) {
                        return $item['order'] === 'desc' ? -$cmp : $cmp;
                    }
                }

                return $a->id <=> $b->id;
            })
            ->values();
    }

    private static function compareCompetitions(Competition $a, Competition $b, string $field): int
    {
        $va = self::competitionSortValue($a, $field);
        $vb = self::competitionSortValue($b, $field);

        if (is_int($va) || is_float($va)) {
            return $va <=> $vb;
        }

        return strcmp((string) $va, (string) $vb);
    }

    private static function competitionSortValue(Competition $competition, string $field): int|float|string
    {
        return match ($field) {
            'name' => mb_strtolower($competition->name ?? ''),
            'sport' => mb_strtolower(
                $competition->sport?->name
                ?? $competition->team?->sport?->name
                ?? ''
            ),
            'start_date' => self::competitionDateSortValue($competition),
            'location' => mb_strtolower($competition->location?->location ?? ''),
            default => '',
        };
    }

    private static function competitionDateSortValue(Competition $competition): string
    {
        $date = $competition->status === 'finished'
            ? ($competition->end_date ?? $competition->start_date)
            : $competition->start_date;

        if ($date === null) {
            return '';
        }

        return $date->format('Y-m-d');
    }

}
