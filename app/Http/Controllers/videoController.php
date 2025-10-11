<?php

namespace App\Http\Controllers;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use Auth;
use Illuminate\Http\Request;

class videoController extends Controller
{
    public function index()
    {
        $videos = Video::all();

        return response()->json([
            'status' => 'success',
            'data' => VideoResource::collection($videos)
        ]);
    }

    public function show($id)
    {
        $video = Video::find($id);
        
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new VideoResource($video)
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'required|file|mimes:mp4,mov,avi,wmv,mkv|max:50240',
        ]);

        $videoPath = $request->file('video')->store('videos', 'public');

        $video = Video::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'video' => $videoPath,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new VideoResource($video)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        if ($video->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'video' => 'sometimes|required|file|mimes:mp4,mov,avi,wmv,mkv|max:50240',
        ]);

        if ($request->hasFile('video')) {
            if ($video->video) {
                \Storage::disk('public')->delete($video->video);
            }
            $validated['video'] = $request->file('video')->store('videos', 'public');
        }

        $video->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => new VideoResource($video)
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'pengajar') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        if ($video->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($video->video) {
            \Storage::disk('public')->delete($video->video);
        }

        $video->delete();

        return response()->json(['message' => 'Video deleted successfully']);
    }

    public function toggleLike($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        $liked = $video->likeBy()->where('user_id', $user->id)->exists();

        if($liked) {
            $video->likeBy()->detach($user->id);
            $message = 'Video unliked';
        } else {
            $video->likeBy()->attach($user->id);
            $message = 'Video liked';
        }

        return response()->json([
            'message' => $message,
            'data' => new VideoResource($video)
        ]);
    }
}
