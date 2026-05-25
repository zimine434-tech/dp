<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleGroup extends Model
{
    protected $fillable = [
        'name',
        'remote_id',
        'course',
    ];
}

