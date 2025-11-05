<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'school_year',
        'target_year_levels',
    ];

    protected $casts = [
        'target_year_levels' => 'array',
    ];
}
