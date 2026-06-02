<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaxBotSubscriber extends Model
{
    protected $fillable = [
        'max_user_id',
        'chat_id',
        'training_sport_ids',
        'competition_sport_ids',
    ];

    protected function casts(): array
    {
        return [
            'training_sport_ids' => 'array',
            'competition_sport_ids' => 'array',
        ];
    }
}
