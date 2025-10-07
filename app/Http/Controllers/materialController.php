<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Auth;
use Illuminate\Http\Request;

class materialController extends Controller
{
    public function index()
    {
        $materials = Material::inRandomOrder()->get();

        return response()->json([
            'message' => 'Material index',
            'data' => MaterialResource::collection($materials),
        ]);
    }

    public function getNewest()
    {
        $materials = Material::orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Newest materials',
            'data' => MaterialResource::collection($materials),
        ]);
    }

    public function getMostLiked()
    {
        $material = Material::orderBy('likes', 'desc')->get();

        return response()->json([
            'message' => 'Most liked materials',
            'data' => MaterialResource::collection($material),
        ]);
    }

    public function show($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        return response()->json([
            'message' => 'Material details',
            'data' => new MaterialResource($material),
        ]);
    }

    public function store(Request $request)
    {
        $pengajar = Auth::user();

        if (!$pengajar || $pengajar->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            'file' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'video' => 'nullable|file|mimes:mp4,avi,mov|max:20480',
        ]);

        $image_url = $request->file('image')->store('materials/images', 'public');
        $file_url = $request->file('file')->store('materials/files', 'public');
        $video_url = $request->hasFile('video')
            ? $request->file('video')->store('materials/videos', 'public')
            : null;

        $material = Material::create([
            'user_id' => $pengajar->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image' => $image_url,
            'file' => $file_url,
            'video' => $video_url,
        ]);

        return response()->json([
            'message' => 'Material created successfully',
            'data' => new MaterialResource($material)
        ], 201);
    }


    public function destroy($id)
    {
        $pengajar = Auth::user();

        if (!$pengajar || $pengajar->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        if ($material->user_id !== $pengajar->id) {
            return response()->json(['message' => 'You can only delete your own materials'], 403);
        }

        foreach (['image', 'file', 'video'] as $field) {
            if ($material->$field && \Storage::disk('public')->exists($material->$field)) {
                \Storage::disk('public')->delete($material->$field);
            }
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted successfully']);
    }

    public function update($id, Request $request)
    {
        $pengajar = Auth::user();

        if (!$pengajar || $pengajar->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        if ($material->user_id !== $pengajar->id) {
            return response()->json(['message' => 'You can only update your own materials'], 403);
        }

        $validateData= $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|file|mimes:png,jpg,jpeg|max:2048',
            'file' => 'sometimes|file|mimes:pdf,doc,docx|max:5120',
            'video' => 'sometimes|nullable|file|mimes:mp4,avi,mov|max:20480',
        ]);

        if ($request->hasFile('image')) {
            if ($material->image && \Storage::disk('public')->exists($material->image)) {
                \Storage::disk('public')->delete($material->image);
            }
            $validateData['image'] = $request->file('image')->store('materials/images', 'public');
        }

        if ($request->hasFile('file')) {
            if ($material->file && \Storage::disk('public')->exists($material->file)) {
                \Storage::disk('public')->delete($material->file);
            }
            $validateData['file'] = $request->file('file')->store('materials/files', 'public');
        }

        if ($request->hasFile('video')) {
            if ($material->video && \Storage::disk('public')->exists($material->video)) {
                \Storage::disk('public')->delete($material->video);
            }
            $validateData['video'] = $request->file('video')->store('materials/videos', 'public');
        }

        $material->update($validateData);

        return response()->json([
            'message' => 'Material updated successfully',
            'data' => new MaterialResource($material)
        ]);
    }
}
