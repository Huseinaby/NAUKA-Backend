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
        'file',
        'video',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likeBy()
    {
        return $this->belongsToMany(User::class, 'material_likes')->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
