<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'quiz_category_id',
        'quiz_sub_category_id',
        'quiz_text',
        'quiz_image',
    ];

    public function category()
    {
        return $this->belongsTo(QuizCategories::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(QuizSubCategories::class, 'sub_category_id');
    }
    
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}
