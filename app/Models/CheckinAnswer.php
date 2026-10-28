<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkin_id',
        'user_id',
        'answer',
    ];

    public function checkin()
    {
        return $this->belongsTo(Checkin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

