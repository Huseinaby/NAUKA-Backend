<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'sub_category_id',
        'created_by',
    ];

    public function subCategory()
    {
        return $this->belongsTo(QuizSubCategories::class, 'sub_category_id');
    }
    
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}
