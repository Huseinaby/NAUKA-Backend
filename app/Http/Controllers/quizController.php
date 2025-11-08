<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;

class quizController extends Controller
{
    public function show($id)
    {
        $quiz = Quiz::with('options')->find($id);

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        return response()->json([
            'message' => 'Quiz retrieved successfully',
            'quiz' => $quiz
        ]);
    }

    public function getBySubCategory($subCategoryId)
    {
        $quizzes = Quiz::where('sub_category_id', $subCategoryId)->with('options')->get();

        if ($quizzes->isEmpty()) {
            return response()->json(['message' => 'No quizzes found for this sub-category'], 404);
        }

        return response()->json([
            'message' => 'Quizzes retrieved successfully',
            'quizzes' => $quizzes
        ]);
    }
}
