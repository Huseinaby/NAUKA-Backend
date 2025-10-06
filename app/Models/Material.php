<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'likes',
        'file_url',
        'video_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
