<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
