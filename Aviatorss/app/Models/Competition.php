<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'team_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'location_id',
        'competition_category_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Boot метод для автоматического обновления статуса при загрузке модели
     */
    protected static function boot()
    {
        parent::boot();

        // Автоматически обновляем статус при загрузке модели
        static::retrieved(function ($competition) {
            // Проверяем, не обновляется ли уже статус (защита от рекурсии)
            if (!isset($competition->_updatingStatus)) {
                $competition->updateStatusIfNeeded();
            }
        });
    }

    /**
     * Автоматически обновляет статус соревнования на основе текущей даты
     */
    public function updateStatusIfNeeded(): void
    {
        // Не обновляем отмененные соревнования
        if ($this->status === 'cancelled') {
            return;
        }

        // Защита от рекурсии
        if (isset($this->_updatingStatus) && $this->_updatingStatus) {
            return;
        }
        $this->_updatingStatus = true;

        try {
            // Получаем текущую дату
            $today = Carbon::today();

            // Получаем даты начала и окончания
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);

            // Определяем новый статус на основе текущей даты
            $newStatus = null;

            if ($endDate->lt($today)) {
                // Дата окончания прошла - соревнование завершено
                $newStatus = 'finished';
            } elseif ($startDate->lte($today) && $endDate->gte($today)) {
                // Текущая дата между началом и окончанием - соревнование идет
                $newStatus = 'ongoing';
            } elseif ($startDate->gt($today)) {
                // Дата начала еще не наступила - соревнование предстоящее
                $newStatus = 'upcoming';
            }

            // Обновляем статус только если он изменился
            if ($newStatus && $this->status !== $newStatus) {
                // Используем прямое обновление через DB чтобы избежать событий и рекурсии
                static::where('id', $this->id)->update(['status' => $newStatus]);
                // Обновляем локальный атрибут
                $this->status = $newStatus;
            }
        } finally {
            // Снимаем флаг защиты от рекурсии
            unset($this->_updatingStatus);
        }
    }

    // Relationships
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function category()
    {
        return $this->belongsTo(CompetitionCategory::class, 'competition_category_id');
    }

    public function participants()
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function results()
    {
        return $this->hasMany(CompetitionResult::class, 'competitions_id');
    }

    public function images()
    {
        return $this->hasMany(CompetitionImage::class);
    }

    public function competitionApplications()
    {
        return $this->hasMany(ApplicationCompetition::class);
    }
}