<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'organizer',
    ];

    // Relationships
    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }
}