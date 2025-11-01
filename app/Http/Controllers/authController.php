<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string|unique:users,username',
            'name' => 'required|string',
            'id_number' => 'required|string|unique:users,id_number',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
            'role' => 'required|string|in:admin,pelajar,pengajar',
            'photo_profile' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'photo_evidence' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('photo_profile')) {
            $path = $request->file('photo_profile')->store('profiles', 'public');
            $fields['photo_profile'] = 'storage/' . $path;
        }

        if ($request->hasFile('photo_evidence')) {
            $path = $request->file('photo_evidence')->store('evidences', 'public');
            $fields['photo_evidence'] = 'storage/' . $path;
        }

        $user = User::create([
            'username' => $fields['username'],
            'name' => $fields['name'],
            'id_number' => $fields['id_number'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => $fields['role'],
            'photo_profile' => $fields['photo_profile'] ?? null,
            'photo_evidence' => $fields['photo_evidence'] ?? null
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'message' => 'User registered successfully',
            'user' => UserResource::make($user),
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);


        $user = User::where('username', $fields['username'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token
        ], 200);
    }


    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out'], 200);
    }
}
