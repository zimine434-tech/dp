<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NewsListingSort
{
    public const FIELDS = ['name', 'date'];

    public const PREFIX_CARDS = 'cards';

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function parseStack(Request $request, string $prefix = self::PREFIX_CARDS): array
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

    /**
     * @return array<int, array{field: string, order: string}>
     */
    public static function defaultStack(): array
    {
        return [['field' => 'date', 'order' => 'desc']];
    }

    public static function isOnlyDefaultStack(array $stack): bool
    {
        return $stack === self::defaultStack();
    }

    /**
     * @param  Builder<\App\Models\News>  $query
     * @param  array<int, array{field: string, order: string}>  $stack
     */
    public static function applyToQuery(Builder $query, array $stack): void
    {
        if ($stack === []) {
            if (empty($query->getQuery()->columns)) {
                $query->select('news.*');
            }
            $query->orderBy('news.id', 'desc');

            return;
        }

        if (empty($query->getQuery()->columns)) {
            $query->select('news.*');
        }

        foreach ($stack as $item) {
            $dir = $item['order'] === 'asc' ? 'asc' : 'desc';

            if ($item['field'] === 'name') {
                $query->orderBy('news.name', $dir);
            } elseif ($item['field'] === 'date') {
                $query->orderBy('news.date', $dir);
            }
        }

        $tieDir = $stack[0]['order'] === 'asc' ? 'asc' : 'desc';
        $query->orderBy('news.id', $tieDir);
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $stack
     * @return array<int, array{field: string, order: string}>
     */
    public static function toggleCriterion(array $stack, string $field, string $order): array
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
     * @return array<string, array<int, string>|string>
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
     * @param  array<string, mixed>  $base
     * @param  array<int, array{field: string, order: string}>  $stack
     * @param  array<string, mixed>  $extra
     */
    public static function listingUrl(string $routeName, array $base, array $stack, array $extra = []): string
    {
        $params = array_merge(
            $base,
            self::queryParamsForPrefix(self::PREFIX_CARDS, $stack),
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
