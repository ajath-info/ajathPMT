<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role', 'department', 'client_company', 'joined_at')
            ->withTimestamps();
    }

    public function organizationMembers()
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function invitations()
    {
        return $this->hasMany(OrganizationInvitation::class);
    }
}

