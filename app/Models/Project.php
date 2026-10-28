<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'cover_url',
        'status',
        'visibility',
        'start_date',
        'created_by',
        'color_theme',
        'is_pinned',
        'client_subtitle',
        'badge_text',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }
}

