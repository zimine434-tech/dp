<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'team_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
        'locations_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Boot метод для автоматического обновления статуса при загрузке модели
     */
    protected static function boot()
    {
        parent::boot();

        // Автоматически обновляем статус при загрузке модели
        static::retrieved(function ($session) {
            // Проверяем, не обновляется ли уже статус (защита от рекурсии)
            if (!isset($session->_updatingStatus)) {
                $session->updateStatusIfNeeded();
            }
        });
    }

    /**
     * Автоматически обновляет статус тренировки на основе текущего времени
     * Использует Carbon и часовой пояс из .env (по умолчанию Иркутск)
     */
    public function updateStatusIfNeeded(): void
    {
        // Не обновляем отмененные тренировки
        if ($this->status === 'cancelled') {
            return;
        }

        // Защита от рекурсии
        if (isset($this->_updatingStatus) && $this->_updatingStatus) {
            return;
        }
        $this->_updatingStatus = true;

        try {
            // Получаем часовой пояс из .env (по умолчанию Иркутск)
            $timezone = env('APP_TIMEZONE', 'Asia/Irkutsk');
            
            // Получаем текущее время в часовом поясе приложения через Carbon
            $now = Carbon::now($timezone);

            // Получаем время начала и окончания в часовом поясе приложения через Carbon
            $startTime = Carbon::parse($this->getRawOriginal('start_time'))->setTimezone($timezone);
            $endTime = Carbon::parse($this->getRawOriginal('end_time'))->setTimezone($timezone);

            // Определяем новый статус на основе текущего времени
            $newStatus = null;

            if ($endTime->lte($now)) {
                // Время окончания прошло - тренировка завершена
                $newStatus = 'completed';
            } elseif ($startTime->lte($now) && $endTime->gt($now)) {
                // Время между началом и окончанием - тренировка идет
                $newStatus = 'in_progress';
            } elseif ($startTime->gt($now)) {
                // Время начала еще не наступило - тренировка запланирована
                $newStatus = 'scheduled';
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

    /**
     * Тренировка завершена для отображения в истории студента: не отменена и (статус completed или время окончания уже прошло).
     */
    public function isParticipantHistoryFinished(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        return $this->status === 'completed'
            || ($this->end_time !== null && $this->end_time->lt(now()));
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

    public function location()
    {
        return $this->belongsTo(LocationTraining::class, 'locations_id');
    }

    public function registrations()
    {
        return $this->hasMany(TrainingRegistration::class, 'training_id');
    }
}