<?php

namespace App\Support;

use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TrainingSessionListingSort
{
    public const FIELDS = ['name', 'start_time'];

    public const PREFIX_CARDS = 'cards';

    public const PREFIX_LIST = 'list';

    /** @var list<string> */
    public const LIST_FIELDS = ['name', 'start_time'];

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

        return $prefix === self::PREFIX_LIST
            ? self::defaultListStack()
            : self::defaultStack();
    }

    /**
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

        return self::defaultListStack();
    }

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function defaultStack(): array
    {
        return [['field' => 'start_time', 'order' => 'desc']];
    }

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function defaultListStack(): array
    {
        return [];
    }

    public static function isOnlyDefaultStack(array $stack): bool
    {
        return $stack === self::defaultStack();
    }

    /**
     * @param  Builder<TrainingSession>  $query
     * @param  array<int, array{field: string, order: string}>  $stack
     */
    public static function applyToQuery(Builder $query, array $stack): void
    {
        if ($stack === []) {
            if (empty($query->getQuery()->columns)) {
                $query->select('training_sessions.*');
            }
            $query->orderBy('training_sessions.id', 'desc');

            return;
        }

        if (empty($query->getQuery()->columns)) {
            $query->select('training_sessions.*');
        }

        foreach ($stack as $item) {
            $dir = $item['order'] === 'asc' ? 'asc' : 'desc';

            if ($item['field'] === 'name') {
                $query->orderBy('training_sessions.title', $dir);
            } elseif ($item['field'] === 'start_time') {
                $query->orderBy('training_sessions.start_time', $dir);
            }
        }

        $tieDir = $stack[0]['order'] === 'asc' ? 'asc' : 'desc';
        $query->orderBy('training_sessions.id', $tieDir);
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

        $oppositeOrder = $defaultOrder === 'desc' ? 'asc' : 'desc';

        if ($currentOrder === $defaultOrder) {
            $stack[$index]['order'] = $oppositeOrder;

            return $stack;
        }

        array_splice($stack, $index, 1);

        return array_values($stack);
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<string, array<int, string>|string>
     */
    public static function queryParamsForPrefix(string $prefix, array $stack): array
    {
        if ($stack === []) {
            if ($prefix === self::PREFIX_LIST) {
                return [];
            }

            return [$prefix.'_sort' => 'none'];
        }

        $default = $prefix === self::PREFIX_LIST
            ? self::defaultListStack()
            : self::defaultStack();
        if ($default !== []
            && count($stack) === 1
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
     * @param  array<string, mixed>  $base
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
}
