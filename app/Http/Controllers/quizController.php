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
}
