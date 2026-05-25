<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sport_id',
    ];

    // Relationships
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    /** Только текущие участники (не удалённые). */
    public function currentMembers()
    {
        return $this->hasMany(TeamMember::class)->whereNull('out');
    }

    public function joinRequests()
    {
        return $this->hasMany(TeamJoinRequest::class);
    }

    public function competitionResults()
    {
        return $this->hasMany(CompetitionResult::class, 'teams_id');
    }
}