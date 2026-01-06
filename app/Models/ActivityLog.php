<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'model_type',
        'model_id',
        'description',
    ];

    protected $casts = [
        'description' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}