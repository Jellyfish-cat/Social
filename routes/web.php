<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

});
// web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::post('/posts/{post}/like', [LikeController::class, 'store'])->middleware('auth');
Route::post('/posts/{post}/comment', [CommentController::class, 'store'])->middleware('auth');

Route::middleware(['auth'])->group(function () {

Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/store', [PostController::class, 'store'])->name('posts.store');
Route::get('/posts/edit/{id}', [PostController::class, 'edit'])->name('posts.edit');
Route::put('/posts/update/{id}', [PostController::class, 'update'])->name('posts.update');
Route::delete('/posts/destroy/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
Route::post('/ckeditor-upload', [PostController::class, 'uploadImage'])->name('ckeditor.upload');
    // Quản trị viên (Duyệt bài)
    Route::middleware(['checkRole:admin'])->group(function () {
        Route::patch('/posts/{id}/approve', [PostController::class, 'approve'])->name('posts.approve');
    });
});