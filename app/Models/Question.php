<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'material_id',
        'question_text',
        'question_image',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
