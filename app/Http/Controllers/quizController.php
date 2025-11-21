<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizResource;
use App\Models\Choice;
use App\Models\Quiz;
use App\Models\QuizCategories;
use App\Models\QuizSubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Storage;

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
            'quiz' => QuizResource::make($quiz)
        ]);
    }

    public function getBySubCategory($subCategoryId)
    {
        $quizzes = Quiz::where('quiz_sub_category_id', $subCategoryId)->with('choices')->get();

        if ($quizzes->isEmpty()) {
            return response()->json(['message' => 'No quizzes found for this sub-category'], 404);
        }

        return response()->json([
            'message' => 'Quizzes retrieved successfully',
            'quizzes' => QuizResource::collection($quizzes)
        ]);
    }

    public function storeBatch(Request $request)
    {

        $validateData = $request->validate([
            'category_id' => 'required|integer|exists:quiz_categories,id',
            'sub_category_id' => 'required|integer|exists:quiz_sub_categories,id',
            'quizzes' => 'required|array',
            'quizzes.*.quiz_text' => 'required|string',
            'quizzes.*.quiz_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'quizzes.*.choices' => 'required|array|min:2',
            'quizzes.*.choices.*.choice_text' => 'nullable|string',
            'quizzes.*.choices.*.choice_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'quizzes.*.choices.*.is_correct' => 'required|boolean',
        ]);

        
        $category = QuizCategories::find($request->category_id);        
        $subCategory = QuizSubCategories::find($request->sub_category_id);
        if (!$category || !$subCategory || $subCategory->quiz_category_id !== $category->id) {
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

                $correctCount = collect($quizData['choices'])->where('is_correct', true)->count();
                if ($correctCount !== 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'Each quiz must have exactly one correct choice'], 400);
                }

                foreach ($quizData['choices'] as $choiceData) {
                    $choiceImagePath = null;
                    if (isset($choiceData['choice_image'])) {
                        $path = $choiceData['choice_image']->store('choice_images', 'public');
                        $choiceImagePath = 'Storage/' . $path;
                    }                    
                    Choice::create([
                        'quiz_id' => $quiz->id,
                        'choice_text' => $choiceData['choice_text'] ?? '',
                        'choice_image' => $choiceImagePath,
                        'is_correct' => $choiceData['is_correct'],
                    ]);
                }
                $createdQuizzes[] = $quiz->load('choices');
            }
            DB::commit();
            return response()->json([
                'message' => 'Quizzes created successfully',
                'quizzes' => QuizResource::collection(collect($createdQuizzes))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quizzes', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'category_id' => 'required|integer|exists:quiz_categories,id',
            'sub_category_id' => 'required|integer|exists:quiz_sub_categories,id',
            'quiz_text' => 'required|string',
            'quiz_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'choices' => 'required|array|min:2',
            'choices.*.choice_text' => 'nullable|string',
            'choices.*.choice_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'choices.*.is_correct' => 'required|boolean',
        ]);
        

        $category = QuizCategories::find($request->category_id);
        $subCategory = QuizSubCategories::find($request->sub_category_id);
        if (!$category || !$subCategory || $subCategory->quiz_category_id !== $category->id) {
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

            $correctCount = collect($validateData['choices'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                DB::rollBack();
                return response()->json(['message' => 'Each quiz must have exactly one correct choice'], 400);
            }

            foreach ($validateData['choices'] as $choiceData) {                
                $choiceImagePath = null;
                if (isset($choiceData['choice_image'])) {
                    $path = $choiceData['choice_image']->store('choice_images', 'public');
                    $choiceImagePath = 'Storage/' . $path;
                }

                Choice::create([
                    'quiz_id' => $quiz->id,
                    'choice_text' => $choiceData['choice_text'] ?? '',
                    'choice_image' => $choiceImagePath,
                    'is_correct' => $choiceData['is_correct'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Quiz created successfully',
                'quiz' => QuizResource::make($quiz->load('choices'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quiz', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || !$user->role === 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $validateData = $request->validate([
            'quiz_text' => 'sometimes|required|string',
            'quiz_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'choices' => 'sometimes|required|array|min:2|max:4',
            'choices.*.id' => 'sometimes|integer|exists:choices,id',
            'choices.*.choice_text' => 'sometimes|required|string',
            'choices.*.choice_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'choices.*.is_correct' => 'sometimes|required|boolean',
        ]);

        DB::beginTransaction();

        try {
            if ($request->filled('quiz_text')) {
                $quiz->quiz_text = $validateData['quiz_text'];
            }

            if ($request->hasFile('quiz_image')) {
                if ($quiz->quiz_image) {
                    Storage::disk('public')->delete(str_replace('Storage/', '', $quiz->quiz_image));
                }
                $path = $validateData['quiz_image']->store('quiz_images', 'public');
                $quiz->quiz_image = 'Storage/' . $path;
            }

            $quiz->save();

            if ($request->has('choices')) {

                $choices = $validateData['choices'];

                if (collect($choices)->where('is_correct', true)->count() !== 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'Each quiz must have exactly one correct choice'], 400);
                }

                $existingChoiceIds = $quiz->choices->keyBy('id');

                foreach ($choices as $index => $cho) {

                    if (isset($cho['id'])) {
                        $choice = $existingChoiceIds[$cho['id']];

                        $choice->choice_text = $cho['choice_text'] ?? $choice->choice_text;
                        $choice->is_correct = $cho['is_correct'];

                        if ($request->hasFile("choices.$index.choice_image")) {
                            if ($choice->choice_image) {
                                Storage::disk('public')->delete(str_replace('Storage/', '', $choice->choice_image));
                            }
                            $path = $cho['choice_image']->store('choice_images', 'public');
                            $choice->choice_image = 'Storage/' . $path;
                        }
                        $choice->save();
                    } else {
                        $newChoice = new Choice([
                            'quiz_id' => $quiz->id,
                            'choice_text' => $cho['choice_text'],
                            'is_correct' => $cho['is_correct'],
                        ]);

                        if ($request->hasFile("choices.$index.choices_image")) {
                            $path = $cho['choice_image']->store('choice_images', 'public');
                            $newChoice->choice_image = 'Storage/' . $path;
                        }

                        $newChoice->save();
                    }
                }
            }
            DB::commit();

            return response()->json([
                'message' => 'Quiz updated successfully',
                'quiz' => QuizResource::make($quiz->load('choices'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update quiz', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user || !$user->role === 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        if ($quiz->quiz_image) {
            Storage::disk('public')->delete(str_replace('Storage/', '', $quiz->quiz_image));
        }

        foreach ($quiz->choices as $choice) {
            if ($choice->choice_image) {
                Storage::disk('public')->delete(str_replace('Storage/', '', $choice->choice_image));
            }
        }

        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully'], 200);
    }
}
