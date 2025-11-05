<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'school_year',
        'target_year_levels',
        'department',
    ];

    protected $casts = [
        'target_year_levels' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
