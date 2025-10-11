<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'video',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likeBy()
    {
        return $this->belongsToMany(User::class, 'video_likes')->withTimestamps();
    }
    
}
