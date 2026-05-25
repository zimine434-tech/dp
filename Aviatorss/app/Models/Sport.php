<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'sport_id');
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class);
    }
}