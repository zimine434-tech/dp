<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionForm extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'competition_id',
        'user_id',
        'form_issued',
        'form_type_id',
        'form_view',
        'form_number',
        'form_status',
        'issued_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'form_issued' => 'boolean',
            'issued_at' => 'date',
            'submitted_at' => 'date',
        ];
    }

    public function formattedIssuedAt(): ?string
    {
        return $this->issued_at?->format('d.m.Y');
    }

    public function formattedSubmittedAt(): ?string
    {
        return $this->submitted_at?->format('d.m.Y');
    }

    public function isIssued(): bool
    {
        return (bool) $this->form_issued;
    }

    public function isSubmitted(): bool
    {
        return $this->form_status === self::STATUS_SUBMITTED;
    }

    public function issuedLabel(): string
    {
        return $this->isIssued() ? 'Выдана' : 'Не выдана';
    }

    public function statusLabel(): string
    {
        return $this->isSubmitted() ? 'Сдал' : 'Не сдал';
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formType()
    {
        return $this->belongsTo(CompetitionFormType::class, 'form_type_id');
    }
}

