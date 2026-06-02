<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsImage extends Model
{
    protected $fillable = [
        'news_id',
        'path',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Публичный URL файла (disk public).
     */
    public function getUrlAttribute(): string
    {
        $path = str_replace('\\', '/', $this->path);
        $path = ltrim($path, '/');

        return '/storage/'.$path;
    }
}
