<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;



class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'more_details',
        'report_path',
        'date',
        'start_time',
        'end_time',
        'status',
        'location',
        'school_year',
        'target_year_levels',
        'target_department',
        'target_users',
        'target_faculty',
        'target_sections',
        'department',
    ];

    protected $casts = [
        'target_year_levels' => 'array',
        'target_department' => 'array',
        'target_faculty' => 'array',
        'target_sections' => 'array',
    ];

    protected $appends = ['computed_status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_attendees', 'event_id', 'user_id')->withTimestamps();
    }

    public function getComputedStatusAttribute()
    {
        // Cancelled should ALWAYS win
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $today = Carbon::today('Asia/Manila')->toDateString();

        if ($this->date > $today) {
            return 'upcoming';
        }

        if ($this->date == $today) {
            return 'ongoing';
        }

        return 'completed';
    }
}
