<?php

namespace App\Http\Controllers;

use App\Models\QuizCategories;
use App\Models\QuizSubCategories;
use Illuminate\Http\Request;

class quizCategoryController extends Controller
{
    public function index()
    {
        $quizCategories = QuizCategories::all();

        if ($quizCategories->isEmpty()) {
            return response()->json([
                'message' => 'No quiz categories found',
            ], 404);
        }

        return response()->json([
            'message' => 'Quiz categories retrieved successfully',
            'data' => $quizCategories,
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:quiz_categories,name',
            'description' => 'nullable|string',
        ]);

        $quizCategory = QuizCategories::create($validatedData);

        return response()->json([
            'message' => 'Quiz category created successfully',
            'data' => $quizCategory,
        ], 201);
    }

    public function show($id)
    {
        $quizCategory = QuizCategories::find($id);

        if (!$quizCategory) {
            return response()->json([
                'message' => 'Quiz category not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Quiz category retrieved successfully',
            'data' => $quizCategory,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $quizCategory = QuizCategories::find($id);

        if (!$quizCategory) {
            return response()->json([
                'message' => 'Quiz category not found',
            ], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:quiz_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $quizCategory->update($validatedData);

        return response()->json([
            'message' => 'Quiz category updated successfully',
            'data' => $quizCategory,
        ], 200);
    }

    public function destroy($id)
    {
        $quizCategory = QuizCategories::find($id);

        if (!$quizCategory) {
            return response()->json([
                'message' => 'Quiz category not found',
            ], 404);
        }

        $quizCategory->delete();

        return response()->json([
            'message' => 'Quiz category deleted successfully',
        ], 200);
    }
}
