<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'more_details',
        'date',
        'start_time',
        'end_time',
        'location',
        'school_year',
        'target_year_levels',
        'target_department',
        'target_users',
        'department',
    ];

    protected $casts = [
        'target_year_levels' => 'array',
        'target_department' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
