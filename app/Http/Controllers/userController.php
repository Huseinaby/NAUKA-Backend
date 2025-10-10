<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class userController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        if(!$user) {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'message' => 'User profile',
            'data' => new UserResource($user),
        ], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',            
            'password' => 'sometimes|string|min:6',
            'photo_profile' => 'sometimes|image|max:2048|mimes:jpg,jpeg,png',
            'photo_evidence' => 'sometimes|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('photo_profile')) {
            if($user->photo_profile && \Storage::disk('public')->exists(str_replace('storage/', '', $user->photo_profile))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $user->photo_profile));
            }
            $path = $request->file('photo_profile')->store('profiles', 'public');
            $validatedData['photo_profile'] = 'storage/' . $path;
        }

        if ($request->hasFile('photo_evidence')) {
            if($user->photo_evidence && \Storage::disk('public')->exists(str_replace('storage/', '', $user->photo_evidence))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $user->photo_evidence));
            }
            $path = $request->file('photo_evidence')->store('evidences', 'public');
            $validatedData['photo_evidence'] = 'storage/' . $path;
        }

        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'User profile updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    public function getAllPengajar()
    {
        $pengajars = User::where('role', 'pengajar')->get();

        return response()->json([
            'message' => 'List of all pengajars',
            'data' => UserResource::collection($pengajars),
        ], 200);
    }
}
