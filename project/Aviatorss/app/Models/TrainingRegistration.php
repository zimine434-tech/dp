<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'user_id',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    // Relationships
    public function training()
    {
        return $this->belongsTo(TrainingSession::class, 'training_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}