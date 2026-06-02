<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionParticipant extends Model
{
    use HasFactory;

    public const MEDICAL_ADMISSION_PENDING = 'pending';

    public const MEDICAL_ADMISSION_ADMITTED = 'admitted';

    public const MEDICAL_ADMISSION_NOT_ADMITTED = 'not_admitted';

    public const MEDICAL_ADMISSION_STATUSES = [
        self::MEDICAL_ADMISSION_PENDING,
        self::MEDICAL_ADMISSION_ADMITTED,
        self::MEDICAL_ADMISSION_NOT_ADMITTED,
    ];

    protected $fillable = [
        'competition_id',
        'user_id',
        'team_id',
        'role',
        'medical_admission_status',
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

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}