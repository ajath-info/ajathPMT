<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'organization_id',
        'sender_id',
        'recipient_id',
        'content',
        'type',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function user()
    {
        return $this->sender();
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}

