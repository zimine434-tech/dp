<?php

namespace App\Services;

use App\Models\MaxBotSubscriber;
use App\Models\Competition;
use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaxBotTrainingNotifier
{
    /**
     * HTML из WYSIWYG → обычный текст для мессенджера MAX.
     */
    private function plainText(?string $html, int $maxLength = 0): string
    {
        $text = (string) $html;
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\/p>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\/div>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\/li>/i', "\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        $text = trim($text);

        if ($maxLength > 0 && mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength);
        }

        return $text;
    }

    /**
     * Подписчики MAX, у которых в JSON-массиве есть sport_id (надёжнее, чем whereJsonContains).
     *
     * @return EloquentCollection<int, MaxBotSubscriber>
     */
    private function subscribersWithSportId(string $column, int $sportId): EloquentCollection
    {
        $allowed = ['training_sport_ids', 'competition_sport_ids'];
        if (! in_array($column, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid sport ids column');
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MariaDB/MySQL: COALESCE(..., CAST("[]" AS JSON)) ломает разбор; IFNULL(..., JSON_ARRAY()) стабильнее
            $needle = json_encode($sportId, JSON_THROW_ON_ERROR);
            $col = '`' . str_replace('`', '', $column) . '`';

            /** @var EloquentCollection<int, MaxBotSubscriber> $rows */
            $rows = MaxBotSubscriber::query()
                ->whereRaw('JSON_CONTAINS(IFNULL(' . $col . ', JSON_ARRAY()), ?)', [$needle])
                ->get(['max_user_id', 'chat_id']);

            return $rows;
        }

        $sportId = max(0, $sportId);

        return MaxBotSubscriber::query()
            ->get(['max_user_id', 'chat_id', $column])
            ->filter(function (MaxBotSubscriber $row) use ($column, $sportId) {
                $ids = $row->{$column} ?? [];
                if (! is_array($ids)) {
                    $ids = [];
                }

                return in_array($sportId, array_map('intval', $ids), true);
            })
            ->values();
    }

    public function notifyNewScheduledTraining(TrainingSession $trainingSession): void
    {
        $url = config('services.max_bot.notify_url');
        $secret = config('services.max_bot.notify_secret');

        if (! is_string($url) || $url === '' || ! is_string($secret) || $secret === '') {
            Log::info('MAX bot notify skipped: not configured (training)');
            return;
        }

        $trainingSession->loadMissing(['sport', 'location']);

        $sportId = $trainingSession->sport_id;
        if ($sportId === null) {
            return;
        }

        $sportIdInt = (int) $sportId;
        $subscribers = $this->subscribersWithSportId('training_sport_ids', $sportIdInt);

        if ($subscribers->isEmpty()) {
            Log::info('MAX bot notify skipped: no subscribers (training)', [
                'sport_id' => $sportId,
                'training_id' => $trainingSession->id,
            ]);
            return;
        }

        $timezone = config('app.timezone', 'Asia/Irkutsk');
        $start = Carbon::parse($trainingSession->start_time)->timezone($timezone);
        $end = Carbon::parse($trainingSession->end_time)->timezone($timezone);

        $sportName = $trainingSession->sport?->name ?? '—';
        $place = $trainingSession->location?->location ?? '—';

        $title = $this->plainText($trainingSession->title);
        $desc = $this->plainText($trainingSession->description, 1000);

        $textLines = [
            '🏃 Новая тренировка',
            '',
            'Название: ' . ($title !== '' ? $title : '—'),
            'Вид спорта: ' . $sportName,
            'Дата и время: ' . $start->format('d.m.Y H:i') . ' – ' . $end->format('d.m.Y H:i'),
            'Локация: ' . $place,
        ];

        if ($desc !== '') {
            $textLines[] = '';
            $textLines[] = 'Описание:';
            $textLines[] = $desc;
        }

        $text = implode("\n", $textLines);

        $recipients = $subscribers->map(fn ($row) => [
            'max_user_id' => (int) $row->max_user_id,
            'chat_id' => $row->chat_id !== null ? (int) $row->chat_id : null,
            'text' => $text,
        ])->values()->all();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Bot-Notify-Secret' => $secret,
                    'Accept' => 'application/json',
                ])
                ->asJson()
                ->post($url, ['recipients' => $recipients]);

            if (! $response->successful()) {
                Log::warning('MAX bot notify failed', [
                    'kind' => 'training',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } else {
                Log::info('MAX bot notify sent', [
                    'kind' => 'training',
                    'recipients' => count($recipients),
                    'training_id' => $trainingSession->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('MAX bot notify error: ' . $e->getMessage());
        }
    }

    public function notifyNewUpcomingCompetition(Competition $competition): void
    {
        $url = config('services.max_bot.notify_url');
        $secret = config('services.max_bot.notify_secret');

        if (! is_string($url) || $url === '' || ! is_string($secret) || $secret === '') {
            Log::info('MAX bot notify skipped: not configured (competition)');
            return;
        }

        $competition->loadMissing(['sport', 'location']);

        $sportId = $competition->sport_id;
        if ($sportId === null) {
            return;
        }

        $sportIdInt = (int) $sportId;
        $subscribers = $this->subscribersWithSportId('competition_sport_ids', $sportIdInt);

        if ($subscribers->isEmpty()) {
            Log::info('MAX bot notify skipped: no subscribers (competition)', [
                'sport_id' => $sportId,
                'competition_id' => $competition->id,
            ]);
            return;
        }

        $sportName = $competition->sport?->name ?? '—';
        $place = $competition->location?->location ?? '—';

        $competitionName = $this->plainText($competition->name);
        $competitionDesc = $this->plainText($competition->description, 500);

        $textLines = [
            'Новое соревнование',
            $competitionName !== '' ? $competitionName : '—',
            '',
            'Вид спорта: ' . $sportName,
            'Даты: ' . Carbon::parse($competition->start_date)->format('d.m.Y') . '–' . Carbon::parse($competition->end_date)->format('d.m.Y'),
            'Место: ' . $place,
        ];

        if ($competitionDesc !== '') {
            $textLines[] = '';
            $textLines[] = $competitionDesc;
        }

        $text = implode("\n", $textLines);

        $recipients = $subscribers->map(fn ($row) => [
            'max_user_id' => (int) $row->max_user_id,
            'chat_id' => $row->chat_id !== null ? (int) $row->chat_id : null,
            'text' => $text,
        ])->values()->all();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Bot-Notify-Secret' => $secret,
                    'Accept' => 'application/json',
                ])
                ->asJson()
                ->post($url, ['recipients' => $recipients]);

            if (! $response->successful()) {
                Log::warning('MAX bot notify failed', [
                    'kind' => 'competition',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } else {
                Log::info('MAX bot notify sent', [
                    'kind' => 'competition',
                    'recipients' => count($recipients),
                    'competition_id' => $competition->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('MAX bot notify error: ' . $e->getMessage());
        }
    }
}
