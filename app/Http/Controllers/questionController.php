<?php

namespace App\Http\Controllers;

use App\Http\Resources\questionResource;
use App\Models\Material;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class questionController extends Controller
{
    public function storeBatch($materialId, Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validateData = $request->validate([            
            'questions' => 'required|array|max:5',
            'questions.*.question_text' => 'nullable|string',
            'questions.*.question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options' => 'required|array|min:2|max:4',
            'questions.*.options.*.option_text' => 'nullable|string',
            'questions.*.options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);


        $material = Material::find($materialId);
        if (!$material || $material->user_id !== $user->id) {
            return response()->json(['message' => 'You can only add questions to your own materials'], 403);
        }

        $existingCount = Question::where('material_id', $materialId)->count();
        if ($existingCount + count($validateData['questions']) > 5) {
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

            return response()->json([
                'message' => 'Questions created successfully',
                'questions' => questionResource::collection($createdQuestions),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating questions.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $question = Question::with('options')->find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        return response()->json([
            'message' => 'Question retrieved successfully',
            'question' => new questionResource($question)
        ]);
    }

    public function getByMaterial($materialId)
    {
        $questions = Question::with('options')->where('material_id', $materialId)->get();

        return response()->json([
            'message' => 'Questions retrieved successfully',
            'questions' => questionResource::collection($questions)
        ]);
    }

    public function update($id, Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        if ($question->material->user_id !== $user->id) {
            return response()->json(['message' => 'You can only update questions from your own materials'], 403);
        }

        $validated = $request->validate([
            'questions' => 'required|array|max:1',
            'question_text' => 'sometimes|string',
            'question_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'options' => 'sometimes|array|min:2|max:4',
            'options.*.id' => 'sometimes|exists:options,id',
            'options.*.option_text' => 'nullable|string',
            'options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'options.*.is_correct' => 'required_with:options|boolean'
        ]);

        $questionData = $validated['questions'][0];

        DB::beginTransaction();
        try {

            if (isset($questionData['question_text'])) {
                $question->question_text = $questionData['question_text'];
            }

            if ($request->hasFile('questions.0.question_image')) {
                if ($question->question_image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $question->question_image));
                }

                $path = $request->file('questions.0.question_image')->store('questions', 'public');
                $question->question_image = 'storage/' . $path;
            }

            $question->save();
            if (isset($questionData['options'])) {
                $options = $questionData['options'];
                $correctCount = collect($options)->where('is_correct', true)->count();

                if ($correctCount !== 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'There must be exactly one correct option.'], 422);
                }

                foreach ($options as $index => $optionData) {
                    // Jika opsi sudah ada (update)
                    if (isset($optionData['id'])) {
                        $option = Option::where('id', $optionData['id'])
                            ->where('question_id', $question->id)
                            ->first();

                        if ($option) {
                            if (isset($optionData['option_text'])) {
                                $option->option_text = $optionData['option_text'];
                            }
                            if (isset($optionData['is_correct'])) {
                                $option->is_correct = $optionData['is_correct'];
                            }

                            // Gambar baru untuk option
                            $fileKey = "questions.0.options.$index.option_image";
                            if ($request->hasFile($fileKey)) {
                                if ($option->option_image) {
                                    Storage::disk('public')->delete(str_replace('storage/', '', $option->option_image));
                                }
                                $path = $request->file($fileKey)->store('options', 'public');
                                $option->option_image = 'storage/' . $path;
                            }

                            $option->save();
                        }
                    } else {
                        $newOption = new Option([
                            'question_id' => $question->id,
                            'option_text' => $optionData['option_text'] ?? null,
                            'is_correct' => $optionData['is_correct'],
                        ]);

                        $fileKey = "questions.0.options.$index.option_image";
                        if ($request->hasFile($fileKey)) {
                            $path = $request->file($fileKey)->store('options', 'public');
                            $newOption->option_image = 'storage/' . $path;
                        }

                        $newOption->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Question updated successfully',
                'question' => new questionResource($question->load('options')),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while updating the question.',
                'error' => $e->getMessage(),
            ], 500);
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

        if ($question->material->user_id !== $user->id) {
            return response()->json(['message' => 'You can only delete questions from your own materials'], 403);
        }

        if ($question->question_image) {
            Storage::disk('public')->delete(str_replace('storage/', '', $question->question_image));
        }
        foreach ($question->options as $option) {
            if ($option->option_image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $option->option_image));
            }
        }
        $question->delete();
        return response()->json([
            'message' => 'Question deleted successfully'
        ], 200);
    }
}
