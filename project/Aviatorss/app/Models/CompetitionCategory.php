<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_category',
        'description',
    ];

    protected $table = 'competition_categories';

    // Relationships
    public function competitions()
    {
        return $this->hasMany(Competition::class, 'competition_category_id');
    }
}