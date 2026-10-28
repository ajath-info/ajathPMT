<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'question',
        'frequency',
        'time',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function answers()
    {
        return $this->hasMany(CheckinAnswer::class);
    }
}

