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
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;


Route::middleware(['auth','checkRole:admin'])->group(function () {
    Route::patch('/posts/{id}/approve', [PostController::class,'approve'])
        ->name('posts.approve');

});
Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
Route::middleware(['auth', 'checkRole:admin'])->prefix('admin')->group(function () {
    

});
Route::middleware(['auth', 'checkRole:admin,moderator'])->group(function () {
    // quyền rộng hơn
});
Route::middleware(['auth', 'checkRole:user'])->group(function () {
    // chỉ user thường
});
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




/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile/detail/{id}', [ProfileController::class,'detail'])->name('profile.detail');
    Route::get('/profile/posts/{id}', [ProfileController::class, 'posts']);
    Route::get('/profile/favorites/{id}', [ProfileController::class, 'favorites']);
    Route::get('/profile/comments/{id}', [ProfileController::class, 'comments']);
        Route::get('/profile/likes/{id}', [ProfileController::class, 'likes']);
    Route::get('/profile/edit/{id}', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware('auth')->group(function () {

    Route::get('/profile/setup_profile/{layout?}', [ProfileController::class,'setup'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class,'storeSetup'])->name('profile.setup.store');

});

/*
|--------------------------------------------------------------------------
| Topics (Chủ đề)
|--------------------------------------------------------------------------
*/

Route::prefix('topics')->group(function () {

    Route::get('/search', [TopicController::class, 'search'])->name('topics.search');
    Route::get('/create', [TopicController::class,'create'])->name('topics.create');
    Route::post('/store', [TopicController::class,'store'])->name('topics.store');
    Route::get('/show/{id}', [TopicController::class,'show'])->name('topics.show');
    Route::get('/edit/{id}', [TopicController::class,'edit'])->name('topics.edit');
    Route::post('/update/{id}', [TopicController::class,'update'])->name('topics.update');
    Route::post('/destroy/{id}', [TopicController::class,'destroy'])->name('topics.destroy');

});

/*
|--------------------------------------------------------------------------
| users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/admin/topics', [TopicController::class,'index'])->name('admin.topics');
    Route::get('/admin/users', [UserController::class,'index'])->name('admin.users');
    Route::get('/admin/comments', [CommentController::class,'index'])->name('admin.comments');
    Route::get('/admin/searchs', [SearchHistoryController::class, 'index'])->name('admin.searchs');
    Route::get('/admin/posts', [PostController::class,'index'])->name('admin.posts');
    Route::get('/admin/messages', [MessageController::class,'index'])->name('admin.messages');
    Route::get('/admin/conversations', [ConversationController::class,'adminIndex'])->name('admin.conversations');
    Route::get('/admin/conversations/{id}', [ConversationController::class,'show'])->name('admin.conversations.show');
    Route::delete('users/destroy/{id}', [UserController::class,'destroy'])->name('users.destroy');
    Route::get('/admin/reports/{tab}', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/admin/reports/tab/{type}/{tab}', [ReportController::class, 'reportTab']);


});
/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/

Route::prefix('posts')->group(function () {

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
    Route::get('/posts/like_list/{id}', [PostController::class,'like_list'])->name('posts.like_list');

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
    Route::delete('/comments/destroy/{id}', [CommentController::class,'destroy'])->name('comments.destroy');
    Route::get('/comments/like_list/{id}', [CommentController::class,'like_list'])->name('comments.like_list');

        /*
    | Search
    */
    Route::get('/search', [SearchHistoryController::class, 'search'])->name('search.result');
    Route::get('/search/suggestions', [SearchHistoryController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/tab/{type}', [SearchHistoryController::class, 'searchTab']);
    Route::delete('/search/destroy/{id}', [SearchHistoryController::class, 'destroy'])->name('search.destroy');

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
| follow
|--------------------------------------------------------------------------

*/ 
    Route::middleware('auth')->group(function () {
        Route::post('/follows/store/{id}', [FollowController::class,'store'])->name('follows.store');
        Route::get('/follows/detail/{id}', [FollowController::class,'detail'])->name('follows.detail');

    });
    /*
|--------------------------------------------------------------------------
| follow
|--------------------------------------------------------------------------

*/
    Route::middleware('auth')->group(function () {
        Route::get('/message', [ConversationController::class,'index'])->name('conversations.index');
        Route::get('/message/chat/{id}', [ConversationController::class,'messageTab'])->name('conversations.messageTab');
        Route::post('/message/send/{id}', [MessageController::class,'store'])->name('message.store');
        Route::get('/conversation/search', [ConversationController::class, 'search_user'])->name('search.user');
        Route::post('/messages/read/{id}', [MessageController::class, 'is_Read']);
         Route::delete('/message/destroy/{id}', [MessageController::class, 'destroy'])->name('message.destroy');
         Route::delete('/conversation/destroy/{id}', [ConversationController::class, 'destroy'])->name('conversation.destroy');


    });

/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/
    Route::middleware('auth')->group(function () {
        Route::get('/notifications', [NotificationController::class,'index'])->name('notifications.index');
        Route::post('/notifications/read/{id}', [NotificationController::class,'markAsRead'])->name('notifications.read');
        Route::get('/notifications/ajax', [NotificationController::class, 'ajax']);
        Route::post('/notifications/read-all', [NotificationController::class,'markAllAsRead'])->name('notifications.readAll');
 
    
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports/store', [ReportController::class, 'store'])->name('reports.store');
    Route::delete('/reports/destroy/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/check/{id}', [ReportController::class, 'check'])->name('reports.check');
       });
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';