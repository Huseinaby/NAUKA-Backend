<?php

namespace App\Http\Controllers;

use App\Models\Video;
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
}
