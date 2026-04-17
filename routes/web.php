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
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationUserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'vi'])) {
        abort(400);
    }
    Session::put('locale', $locale);
    return redirect()->back();
})->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Public/Guest & Auth Routes (View only)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('profile/detail/{id}', [ProfileController::class, 'detail'])->name('profile.detail');
Route::get('posts/detail/{id}', [PostController::class, 'detail'])->name('posts.detail');
Route::get('posts/like_list/{id}', [PostController::class, 'like_list'])->name('posts.like_list');
Route::get('/search', [SearchHistoryController::class, 'search'])->name('search.result');
Route::get('/search/suggestions', [SearchHistoryController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/tab/{type}', [SearchHistoryController::class, 'searchTab']);
Route::get('topics/show/{id}', [TopicController::class, 'show'])->name('topics.show');
Route::get('topics/search', [TopicController::class, 'search'])->name('topics.search');
Route::get('posts/comments/{id}', [PostController::class, 'loadComments'])->name('posts.comments');
Route::get('profile/posts/{id}', [ProfileController::class, 'posts']);
Route::get('profile/favorites/{id}', [ProfileController::class, 'favorites']);
Route::get('profile/comments/{id}', [ProfileController::class, 'comments']);
Route::get('profile/likes/{id}', [ProfileController::class, 'likes']);
Route::get('comments/like_list/{id}', [CommentController::class, 'like_list'])->name('comments.like_list');
 Route::get('follows/detail/{id}', [FollowController::class, 'detail'])->name('follows.detail');
/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    
    Route::delete('/search/destroy/{id}', [SearchHistoryController::class, 'destroy'])->name('search.destroy');
    
    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/ajax', [NotificationController::class, 'ajax']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    });

   
    // --- Home ---
       // --- Profile ---
    Route::prefix('profile')->group(function () {
        Route::get('/edit/{id}', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/setup_profile/{layout?}', [ProfileController::class, 'setup'])->name('profile.setup');
        Route::post('/setup', [ProfileController::class, 'storeSetup'])->name('profile.setup.store');
    });
    Route::prefix('posts')->group(function () {
    Route::get('/edit/{id}', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/update/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/destroy/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    });
    Route::prefix('topics')->group(function () {
        Route::get('/create', [TopicController::class, 'create'])->name('topics.create');
        Route::post('/store', [TopicController::class, 'store'])->name('topics.store');
    });
     Route::prefix('comments')->group(function () {
        Route::get('/latest/{id}', [CommentController::class, 'latest']);
        Route::delete('/destroy/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
    Route::delete('/message/destroy/{id}', [MessageController::class, 'destroy'])->name('message.destroy');
    Route::delete('/conversation/destroy/{id}', [ConversationController::class, 'destroy'])->name('conversation.destroy');
    Route::delete('/reports/destroy/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');
    //USER
    Route::middleware(['checkRole:user'])->group(function () {
      // --- Share Post logic ---
    Route::get('/share/list/{id}', [ShareController::class, 'getShareList'])->name('share.list');
    // --- Posts ---
    Route::prefix('posts')->group(function () {
        Route::get('/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('/store', [PostController::class, 'store'])->name('posts.store');
        Route::post('/like/{id}', [LikePostController::class, 'store'])->name('posts.like');
        Route::post('/favorite/{id}', [FavoriteController::class, 'store'])->name('posts.favorite');

    });
    Route::post('/ckeditor-upload', [PostController::class, 'uploadImage'])->name('ckeditor.upload');
    // --- Topics ---

    // --- Comments ---
    Route::prefix('comments')->group(function () {
        Route::get('/latest/{id}', [CommentController::class, 'latest']);
        Route::post('/create/{id}', [CommentController::class, 'store'])->name('comments.create');
        Route::post('/like/{id}', [CommentController::class, 'like'])->name('comments.like');
    });
    Route::post('/comment/like/{id}', [LikeCommentController::class, 'store'])->name('comment.like');

    // --- Messages & Conversations ---
    Route::get('/message', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/message/chat/{id}', [ConversationController::class, 'messageTab'])->name('conversations.messageTab');
    Route::post('/message/send/{id}', [MessageController::class, 'store'])->name('message.store');
    Route::get('/conversation/search', [ConversationController::class, 'search_user'])->name('search.user');
    Route::post('/messages/read/{id}', [MessageController::class, 'is_Read']);

    // Group Chat Features
    Route::post('/conversation/group/create', [ConversationController::class, 'storeGroup'])->name('conversation.group.create');
    Route::get('/message/group/chat/{id}', [ConversationController::class, 'groupTab'])->name('conversations.groupTab');
    Route::post('/message/group/send/{convoId}', [MessageController::class, 'storeGroupMsg'])->name('message.group.store');
    
    // --- Search ---

    // --- Follows ---
    Route::prefix('follows')->group(function () {
        Route::post('/store/{id}', [FollowController::class, 'store'])->name('follows.store');
      
    });

    // --- Reports (User) ---
    Route::prefix('reports')->group(function () {
        Route::get('/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/store', [ReportController::class, 'store'])->name('reports.store');
       
    });

    /*
    |--------------------------------------------------------------------------
    | Staff Routes (Admin & Moderator)
    |--------------------------------------------------------------------------
    */
});
    Route::middleware(['checkRole:admin,moderator'])->prefix('admin')->group(function () {
       
        // Reports Management
        Route::get('/reports/{tab?}', [ReportController::class, 'index'])->name('admin.reports');
        Route::get('/reports/tab/{type}/{tab}', [ReportController::class, 'reportTab']);
        Route::get('/topics', [TopicController::class, 'index'])->name('admin.topics');
        Route::get('/topics/edit/{id}', [TopicController::class, 'edit'])->name('topics.edit');
        Route::post('/topics/update/{id}', [TopicController::class, 'update'])->name('topics.update');
        Route::post('/topics/destroy/{id}', [TopicController::class, 'destroy'])->name('topics.destroy');
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/comments', [CommentController::class, 'index'])->name('admin.comments');
        Route::get('/searchs', [SearchHistoryController::class, 'index'])->name('admin.searchs');
        Route::get('/posts', [PostController::class, 'index'])->name('admin.posts');
        Route::get('/messages', [MessageController::class, 'index'])->name('admin.messages');
        Route::get('/conversations', [ConversationController::class, 'adminIndex'])->name('admin.conversations');
        Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('admin.conversations.show');
    });
    Route::middleware(['checkRole:moderator,admin'])->group(function () {
        Route::post('/reports/check/{id}', [ReportController::class, 'check'])->name('reports.check');
    });
     Route::middleware(['checkRole:admin'])->prefix('admin')->group(function () {
         Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
     });
    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Super Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['checkRole:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::put('/users/hide/{id}', [UserController::class, 'hide'])->name('users.hide');
    Route::delete('users/destroy/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

});

require __DIR__.'/auth.php';