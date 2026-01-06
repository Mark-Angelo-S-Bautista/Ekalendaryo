<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = ['school_year', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
