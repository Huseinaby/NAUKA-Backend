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
}
