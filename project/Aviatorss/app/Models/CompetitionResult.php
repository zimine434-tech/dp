<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'place',
        'result_type',
        'competitions_id',
        'user_id',
        'teams_id',
        'is_archive',
    ];

    protected $casts = [
        'is_archive' => 'boolean',
    ];

    // Relationships
    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competitions_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'teams_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}