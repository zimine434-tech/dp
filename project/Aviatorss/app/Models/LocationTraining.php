<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'description',
    ];

    protected $table = 'locations_training';

    // Relationships
    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class, 'locations_id');
    }
}