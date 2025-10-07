<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authentication
Route::post('/register', [authController::class, 'register']);
Route::post('/login', [authController::class,'login']);
Route::post('/logout', [authController::class,'logout'])->middleware('auth:sanctum');

//user profile
Route::get('/user/profile', [userController::class, 'profile'])->middleware('auth:sanctum');
Route::put('/user/profile', [userController::class, 'update'])->middleware('auth:sanctum');

//materials
Route::get('/materials', [materialController::class, 'index']);
Route::get('/materials/newest', [materialController::class, 'getNewest']);
Route::get('/materials/most-liked', [materialController::class, 'getMostLiked']);
Route::post('/materials', [materialController::class, 'store'])->middleware('auth:sanctum');
Route::get('/materials/{id}', [materialController::class, 'show']);
Route::put('/materials/{id}', [materialController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/materials/{id}', [materialController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/materials/{id}/like', [materialController::class, 'toggleLike'])->middleware('auth:sanctum');

