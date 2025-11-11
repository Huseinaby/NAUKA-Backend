<?php

namespace App\Http\Controllers;

use App\Models\QuizSubCategories;
use Illuminate\Http\Request;

class quizSubCategoryController extends Controller
{
    public function index()
    {
        $quizSubCategories = QuizSubCategories::all();

        if ($quizSubCategories->isEmpty()) {
            return response()->json(['message' => 'No quiz sub-categories found'], 404);
        }

        return response()->json([
            'message' => 'Quiz sub-categories retrieved successfully',
            'data' => $quizSubCategories
        ]);
    }

    public function show($id)
    {
        $quizSubCategory = QuizSubCategories::find($id);

        if (!$quizSubCategory) {
            return response()->json(['message' => 'Quiz sub-category not found'], 404);
        }

        return response()->json([
            'message' => 'Quiz sub-category retrieved successfully',
            'data' => $quizSubCategory
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'quiz_category_id' => 'required|exists:quiz_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $quizSubCategory = QuizSubCategories::create($request->all());

        return response()->json([
            'message' => 'Quiz sub-category created successfully',
            'data' => $quizSubCategory
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $quizSubCategory = QuizSubCategories::find($id);

        if (!$quizSubCategory) {
            return response()->json(['message' => 'Quiz sub-category not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $quizSubCategory->update($request->all());

        return response()->json([
            'message' => 'Quiz sub-category updated successfully',
            'data' => $quizSubCategory
        ]);
    }

    public function destroy($id)
    {
        $quizSubCategory = QuizSubCategories::find($id);

        if (!$quizSubCategory) {
            return response()->json(['message' => 'Quiz sub-category not found'], 404);
        }

        $quizSubCategory->delete();

        return response()->json(['message' => 'Quiz sub-category deleted successfully']);
    }
}
