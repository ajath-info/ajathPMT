<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'author_id',
        'content',
    ];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

