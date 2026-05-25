<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'user_id',
        'role',
    ];

    protected $primaryKey = ['competition_id', 'user_id'];
    public $incrementing = false;

    // Relationships
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}