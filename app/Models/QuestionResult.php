<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class questionResult extends Model
{
    protected $fillable = [
        'user_id',
        'material_id',
        'score',
        'correct',
        'wrong',
        'total_question'
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function materials()
    {
        return $this->belongsTo(Material::class);
    }

}
