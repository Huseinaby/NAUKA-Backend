<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizCategories extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
}
