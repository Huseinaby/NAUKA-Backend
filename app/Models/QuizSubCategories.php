<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSubCategories extends Model
{
    protected $fillable = [
        'quiz_category_id',
        'name',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(QuizCategories::class, 'quiz_category_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'sub_category_id');
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class, 'sub_category_id');
    }
}
