<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Quiz;
use App\Models\QuizCategories;
use App\Models\QuizSubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $validateData = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required|integer|exists:sub_categories,id',
            'quizzes' => 'required|array',
            'quizzes.*.quiz_text' => 'required|string',
            'quizzes.*.quiz_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'quizzes.*.choises' => 'required|array|min:2',
            'quizzes.*.choises.*.choises_text' => 'required|string',
            'quizzes.*.choises.*.choises_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'quizzes.*.choises.*.is_correct' => 'required|boolean',
        ]);

        $category = QuizCategories::find($request->category_id);
        $subCategory = QuizSubCategories::find($request->sub_category_id);
        if (!$category || !$subCategory || $subCategory->category_id !== $category->id) {
            return response()->json(['message' => 'Invalid category or sub-category'], 400);
        }

        DB::beginTransaction();

        try {
            $createdQuizzes = [];

            foreach ($validateData['quizzes'] as $quizData) {
                $quizImagePath = null;
                if (isset($quizData['quiz_image'])) {
                    $path = $quizData['quiz_image']->store('quiz_images', 'public');
                    $quizImagePath = 'Storage/' . $path;
                }

                $quiz = Quiz::create([
                    'quiz_category_id' => $category->id,
                    'quiz_sub_category_id' => $subCategory->id,
                    'quiz_text' => $quizData['quiz_text'],
                    'quiz_image' => $quizImagePath,
                ]);

                $correctCount = collect($quizData['choises'])->where('is_correct', true)->count();
                if ($correctCount !== 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'Each quiz must have exactly one correct choice'], 400);
                }

                foreach ($quizData['choises'] as $choiceData) {
                    $choiceImagePath = null;
                    if (isset($choiceData['choises_image'])) {
                        $path = $choiceData['choises_image']->store('choice_images', 'public');
                        $choiceImagePath = 'Storage/' . $path;
                    }

                    Choice::create([
                        'quiz_id' => $quiz->id,
                        'choice_text' => $choiceData['choises_text'],
                        'choice_image' => $choiceImagePath,
                        'is_correct' => $choiceData['is_correct'],
                    ]);
                }
                $createdQuizzes[] = $quiz->load('choices');
            }
            DB::commit();
            return response()->json([
                'message' => 'Quizzes created successfully',
                'quizzes' => $createdQuizzes
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quizzes', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) 
    {
        $validateData = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required|integer|exists:sub_categories,id',
            'quiz_text' => 'required|string',
            'quiz_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',    
            'choises' => 'required|array|min:2',
            'choises.*.choises_text' => 'required|string',
            'choises.*.choises_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'choises.*.is_correct' => 'required|boolean',
        ]);

        $category = QuizCategories::find($request->category_id);
        $subCategory = QuizSubCategories::find($request->sub_category_id);
        if (!$category || !$subCategory || $subCategory->category_id !== $category->id) {
            return response()->json(['message' => 'Invalid category or sub-category'], 400);
        }

        DB::beginTransaction();

        try {
            $quizImagePath = null;
            if (isset($validateData['quiz_image'])) {
                $path = $validateData['quiz_image']->store('quiz_images', 'public');
                $quizImagePath = 'Storage/' . $path;
            }

            $quiz = Quiz::create([
                'quiz_category_id' => $category->id,
                'quiz_sub_category_id' => $subCategory->id,
                'quiz_text' => $validateData['quiz_text'],
                'quiz_image' => $quizImagePath,
            ]);

            $correctCount = collect($validateData['choises'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                DB::rollBack();
                return response()->json(['message' => 'Each quiz must have exactly one correct choice'], 400);
            }

            foreach ($validateData['choises'] as $choiceData) {
                $choiceImagePath = null;
                if (isset($choiceData['choises_image'])) {
                    $path = $choiceData['choises_image']->store('choice_images', 'public');
                    $choiceImagePath = 'Storage/' . $path;
                }

                Choice::create([
                    'quiz_id' => $quiz->id,
                    'choice_text' => $choiceData['choises_text'],
                    'choice_image' => $choiceImagePath,
                    'is_correct' => $choiceData['is_correct'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Quiz created successfully',
                'quiz' => $quiz->load('choices')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quiz', 'error' => $e->getMessage()], 500);
        }
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
