<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;

class quizController extends Controller
{
    public function show($id)
    {
        $quiz = Quiz::with('choices')->find($id);

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

    public function storeBatch(Request $request)
    {
        $request->validate([
            'quizzes' => 'required|array',
            'quizzes.*.question' => 'required|string',
            'quizzes.*.sub_category_id' => 'required|integer',
            'quizzes.*.options' => 'required|array|min:2',
            'quizzes.*.options.*.text' => 'required|string',
            'quizzes.*.options.*.is_correct' => 'required|boolean',
        ]);

        $createdQuizzes = [];

        foreach ($request->quizzes as $quizData) {
            $quiz = new Quiz();
            $quiz->question = $quizData['question'];
            $quiz->sub_category_id = $quizData['sub_category_id'];
            $quiz->save();

            foreach ($quizData['options'] as $optionData) {
                $quiz->options()->create([
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }

            $createdQuizzes[] = $quiz->load('options');
        }

        return response()->json([
            'message' => 'Quizzes created successfully',
            'quizzes' => $createdQuizzes
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::find($id);

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $request->validate([
            'question' => 'sometimes|required|string',
            'sub_category_id' => 'sometimes|required|integer',
        ]);

        if ($request->has('question')) {
            $quiz->question = $request->question;
        }
        if ($request->has('sub_category_id')) {
            $quiz->sub_category_id = $request->sub_category_id;
        }

        $quiz->save();

        return response()->json([
            'message' => 'Quiz updated successfully',
            'quiz' => $quiz
        ]);
    }
}
