<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Auth;
use Illuminate\Http\Request;

class videoController extends Controller
{
    public function index()
    {
        $video = Video::all();

        return response()->json([
            'status' => 'success',
            'data' => $video
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
            'data' => $video
        ], 201);
    }
}
