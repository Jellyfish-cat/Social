<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikePostController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LikeCommentController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

Route::get('/lang/{locale}', function ($locale) {

    if (! in_array($locale, ['en','vi'])) {
        abort(400);
    }

    Session::put('locale', $locale);

    return redirect()->back();

})->name('lang.switch');
/*
|--------------------------------------------------------------------------
| Trang chính
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
->middleware(['auth','verified'])->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth','verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class,'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class,'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class,'destroy'])->name('profile.destroy');

});
Route::middleware('auth')->group(function () {

    Route::get('/profile/setup_profile', [ProfileController::class,'setup'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class,'storeSetup'])->name('profile.setup.store');

});

/*
|--------------------------------------------------------------------------
| Topics (Chủ đề)
|--------------------------------------------------------------------------
*/

Route::prefix('topics')->group(function () {

    Route::get('/', [TopicController::class,'index'])->name('topics.index');

    Route::get('/create', [TopicController::class,'create'])->name('topics.create');
    Route::post('/store', [TopicController::class,'store'])->name('topics.store');

    Route::get('/edit/{id}', [TopicController::class,'edit'])->name('topics.edit');
    Route::post('/update/{id}', [TopicController::class,'update'])->name('topics.update');
    Route::post('/destroy/{id}', [TopicController::class,'destroy'])->name('topics.destroy');

});


/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/

Route::prefix('posts')->group(function () {

    Route::get('/', [PostController::class,'index'])->name('posts.index');
    Route::get('/detail/{id}', [PostController::class,'detail'])->name('posts.detail');

});


/*
|--------------------------------------------------------------------------
| Các chức năng cần đăng nhập
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /*
    | Post CRUD
    */
    Route::get('/posts/create', [PostController::class,'create'])->name('posts.create');
    Route::post('/posts/store', [PostController::class,'store'])->name('posts.store');

    Route::get('/posts/edit/{id}', [PostController::class,'edit'])->name('posts.edit');
    Route::put('/posts/update/{id}', [PostController::class,'update'])->name('posts.update');
    Route::delete('/posts/destroy/{id}', [PostController::class,'destroy'])->name('posts.destroy');

    Route::post('/ckeditor-upload', [PostController::class,'uploadImage'])->name('ckeditor.upload');


    /*
    | Like Post
    */

    Route::post('/posts/like/{id}', [LikePostController::class,'store'])->name('posts.like');
    /*
    | favorite Post
    */
    Route::post('/posts/favorite/{id}', [FavoriteController::class,'store'])->name('posts.favorite');
  /*
    | Like Comment
    */
     Route::post('/comment/like/{id}', [LikeCommentController::class,'store'])->name('comment.like');
    /*
    | Comments
    */
    Route::get('/comments/latest/{post}', [CommentController::class,'latest']);
    Route::post('/posts/{post}/comment', [CommentController::class,'store']);

    Route::get('/posts/comments/{id}', [PostController::class,'loadComments']);

    Route::post('/comments/create/{id}', [CommentController::class,'store'])->name('comments.create');
    Route::post('/comments/reply/{id}', [CommentController::class,'reply'])->name('comments.reply');
    Route::post('/comments/like/{id}', [CommentController::class,'like'])->name('comments.like');


    /*
    | Admin duyệt bài
    */

    Route::middleware('checkRole:admin')->group(function () {

        Route::patch('/posts/{id}/approve', [PostController::class,'approve'])
        ->name('posts.approve');

    });

});


/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';