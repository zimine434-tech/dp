<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class ParticipantListingDateFilter
{
    /**
     * Границы выбора: одна дата — весь календарный день; две — от начала первого до конца второго.
     * Если «с» позже «по» — интервал переворачивается.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public static function selectionBounds(?string $dateFrom, ?string $dateTo): ?array
    {
        if (! $dateFrom && ! $dateTo) {
            return null;
        }

        if ($dateFrom && $dateTo) {
            $rangeAStart = Carbon::parse($dateFrom)->startOfDay();
            $rangeAEnd = Carbon::parse($dateFrom)->endOfDay();
            $rangeBStart = Carbon::parse($dateTo)->startOfDay();
            $rangeBEnd = Carbon::parse($dateTo)->endOfDay();

            return $rangeAStart->lte($rangeBStart)
                ? [$rangeAStart, $rangeBEnd]
                : [$rangeBStart, $rangeAEnd];
        }

        $only = $dateFrom ?? $dateTo;
        $day = Carbon::parse($only)->startOfDay();

        return [$day, $day->copy()->endOfDay()];
    }

    /**
     * @param  Builder<\App\Models\TrainingSession>  $query
     */
    public static function applyToTrainingSessionQuery(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        $bounds = self::selectionBounds($dateFrom, $dateTo);
        if (! $bounds) {
            return;
        }
        [$rangeStart, $rangeEnd] = $bounds;
        $query->where('start_time', '<=', $rangeEnd)
            ->where('end_time', '>=', $rangeStart);
    }

    public static function trainingSessionIntervalsOverlap(
        Carbon $sessionStart,
        Carbon $sessionEnd,
        ?string $dateFrom,
        ?string $dateTo
    ): bool {
        $bounds = self::selectionBounds($dateFrom, $dateTo);
        if (! $bounds) {
            return true;
        }
        [$rangeStart, $rangeEnd] = $bounds;

        return $sessionStart <= $rangeEnd && $sessionEnd >= $rangeStart;
    }

    /**
     * @param  Builder<\App\Models\Competition>  $query
     */
    public static function applyToCompetitionQuery(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        $bounds = self::selectionBounds($dateFrom, $dateTo);
        if (! $bounds) {
            return;
        }
        [$rangeStart, $rangeEnd] = $bounds;
        $rs = $rangeStart->toDateString();
        $re = $rangeEnd->toDateString();

        $query->whereDate('start_date', '<=', $re)
            ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$rs]);
    }

    /**
     * @param  \Carbon\CarbonInterface|null  $competitionStart
     * @param  \Carbon\CarbonInterface|null  $competitionEnd
     */
    public static function competitionIntervalsOverlap(
        $competitionStart,
        $competitionEnd,
        ?string $dateFrom,
        ?string $dateTo
    ): bool {
        $bounds = self::selectionBounds($dateFrom, $dateTo);
        if (! $bounds) {
            return true;
        }
        if (! $competitionStart) {
            return false;
        }

        [$rangeStart, $rangeEnd] = $bounds;

        $eventStart = Carbon::parse($competitionStart)->startOfDay();
        $eventEnd = $competitionEnd
            ? Carbon::parse($competitionEnd)->startOfDay()
            : $eventStart->copy();

        return $eventStart->lte($rangeEnd) && $eventEnd->gte($rangeStart);
    }
}
