<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Auth;
use DB;
use Illuminate\Http\Request;

class questionController extends Controller
{
    public function storeBatch(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validateData = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'questions' => 'required|array|max:5',
            'questions.*.question_text' => 'nullable|string',
            'questions.*.question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options' => 'required|array|min:2|max:4',
            'questions.*.options.*.option_text' => 'nullable|string',
            'questions.*.options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $materialId = $validateData['material_id'];

        $existingCount = Question::where('material_id', $materialId)->count();
        if($existingCount + count($validateData['questions']) > 5 ) {
            return response()->json(['message' => 'Adding these questions would exceed the maximum number of questions (5) for this material.'], 422);
        }

        DB::beginTransaction();
        try {
            $createdQuestions = [];

            foreach ($validateData['questions'] as $questionData) {
                $questionImagePath = null;                
                if (isset($questionData['question_image'])) {                    
                    $path = $questionData['question_image']->store('questions', 'public');
                    $questionImagePath = 'storage/' . $path;
                }

                $question = Question::create([
                    'material_id' => $materialId,
                    'question_text' => $questionData['question_text'] ?? null,
                    'question_image' => $questionImagePath,
                ]);

                $correctCount = collect($questionData['options'])->where('is_correct', true)->count();
                if ($correctCount !== 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'Each question must have exactly one correct option.'], 422);
                }

                foreach ($questionData['options'] as $optionData) {
                    $optionImagePath = null;
                    if (isset($optionData['option_image'])) {
                        $path = $optionData['option_image']->store('options', 'public');
                        $optionImagePath = 'storage/' . $path;
                    }

                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'] ?? null,
                        'option_image' => $optionImagePath,
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }

                $createdQuestions[] = $question->load('options');
            }

            DB::commit();

            return response()->json(['message' => 'Questions created successfully', 'questions' => $createdQuestions], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating questions.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        if($question->question_image) {
            $imagePath = str_replace('storage/', 'public/', $question->question_image);
            if (\Storage::exists($imagePath)) {
                \Storage::delete($imagePath);
            }
        }
        foreach ($question->options as $option) {
            if ($option->option_image) {
                $optionImagePath = str_replace('storage/', 'public/', $option->option_image);
                if (\Storage::exists($optionImagePath)) {
                    \Storage::delete($optionImagePath);
                }
            }
        }
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully'], 200);
    }
}
