<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\questionController;
use App\Http\Controllers\userController;
use App\Http\Controllers\videoController;
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
Route::get('/users/pengajars', [userController::class, 'getAllPengajar'])->middleware('auth:sanctum');
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
Route::get('/materials/pengajar/{pengajarId}', [materialController::class, 'getByPengajar'])->middleware('auth:sanctum');
Route::post('/materials/{id}/like', [materialController::class, 'toggleLike'])->middleware('auth:sanctum');

//material questions
Route::post('/materials/{id}/questions', [questionController::class, 'storeBatch'])->middleware('auth:sanctum');
Route::get('/materials/{id}/questions', [questionController::class, 'getByMaterial'])->middleware('auth:sanctum');
Route::get('/materials/questions/{id}', [questionController::class, 'show'])->middleware('auth:sanctum');
Route::put('/materials/questions/{id}', [questionController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/materials/questions/{id}', [questionController::class, 'destroy'])->middleware('auth:sanctum');

//videos
Route::get('/videos', [videoController::class, 'index']);
Route::get('/videos/{id}', [videoController::class, 'show'])->middleware('auth:sanctum');
Route::post('/videos', [videoController::class, 'store'])->middleware('auth:sanctum');
Route::put('/videos/{id}', [videoController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/videos/{id}', [videoController::class, 'destroy'])->middleware('auth:sanctum');