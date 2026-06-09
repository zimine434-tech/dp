<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationCompetition extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    public const REASON_FINISHED = 'Соревнование завершено, заявка не была рассмотрена';

    public const REASON_REMOVED_FROM_PARTICIPANTS = 'Удалены из участников соревнования';

    protected $table = 'application_competition';

    protected $fillable = [
        'user_id',
        'competition_id',
        'accepted_by_user_id',
        'status',
        'rejection_reason',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public static function expirationReasonForCompetitionStatus(string $competitionStatus): string
    {
        return match ($competitionStatus) {
            'finished' => self::REASON_FINISHED,
            'cancelled' => 'Соревнование отменено, заявка не была рассмотрена',
            'ongoing' => 'Соревнование началось, заявка не была рассмотрена',
            default => 'Заявка не была рассмотрена',
        };
    }

    /**
     * Закрывает все pending-заявки по соревнованию, если оно уже не предстоящее.
     */
    public static function expirePendingForCompetition(int $competitionId, string $competitionStatus): int
    {
        if ($competitionStatus === 'upcoming') {
            return 0;
        }

        return (int) static::query()
            ->where('competition_id', $competitionId)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status' => self::STATUS_EXPIRED,
                'rejection_reason' => self::expirationReasonForCompetitionStatus($competitionStatus),
                'accepted_by_user_id' => null,
                'accepted_at' => null,
            ]);
    }

    /**
     * Разовая очистка «зависших» заявок по всем непредстоящим соревнованиям.
     */
    public static function expireAllStalePending(): int
    {
        $total = 0;

        Competition::query()
            ->where('status', '!=', 'upcoming')
            ->select(['id', 'status'])
            ->orderBy('id')
            ->chunkById(100, function ($competitions) use (&$total) {
                foreach ($competitions as $competition) {
                    $total += self::expirePendingForCompetition((int) $competition->id, (string) $competition->status);
                }
            });

        return $total;
    }
}
