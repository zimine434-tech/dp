<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'id_adding',
        'joined_at',
        'out',
        'id_out',
        'type_user',
        'added_via',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'out' => 'datetime',
    ];

    /**
     * Только текущие участники (не удалённые из команды).
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('out');
    }

    /**
     * Для обратной совместимости: активен = не удалён (out === null).
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->out === null;
    }

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Кто добавил участника в команду */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'id_adding');
    }

    /** Кто удалил участника из команды */
    public function removedBy()
    {
        return $this->belongsTo(User::class, 'id_out');
    }
}