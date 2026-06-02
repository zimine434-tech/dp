<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionImage extends Model
{
    protected $fillable = [
        'competition_id',
        'path',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function getUrlAttribute(): string
    {
        $path = str_replace('\\', '/', $this->path);
        $path = ltrim($path, '/');

        return '/storage/'.$path;
    }

    public function getFilenameAttribute(): string
    {
        return basename(str_replace('\\', '/', $this->path));
    }
}
