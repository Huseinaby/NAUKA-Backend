<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = [
        'user_id',
        'sub_category_id',
        'score',
        'correct',
        'wrong',
        'total_questions',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(QuizSubCategories::class, 'sub_category_id');
    }
}
