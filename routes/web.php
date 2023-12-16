<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Gate Route
Route::get('/admin-only', function () {

    // GATE >> Controller Way
    // if (Gate::allows('visitAdminPages')) {
    //     return 'Only Admin can see this page.';
    // }
    // return 'You cannot view this page.';

    // GATE >> Middleware Way
    return 'Only Admin can see this page.';
})->middleware('can:visitAdminPages');

// User Routes
Route::get('/', [UserController::class, 'showHomepage'])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('isLoggedIn');
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('isLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'updateAvatar'])->middleware('isLoggedIn');


// Blog Posts Routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('isLoggedIn');
Route::post('/create-post', [PostController::class, 'saveNewPost'])->middleware('isLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewSinglePost']);
Route::delete('/post/{post}', [PostController::class, 'deletePost'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'updatePost'])->middleware('can:update,post');
Route::get('/search/q={term}', [PostController::class, 'search'])->middleware('isLoggedIn');

// Follow Routes
Route::post('/follow-user/{user:username}', [FollowController::class, 'followUser'])->middleware('isLoggedIn');
Route::post('/unfollow-user/{user:username}', [FollowController::class, 'unfollowUser'])->middleware('isLoggedIn');

// Profile Routes
Route::get('/profile/{user:username}', [UserController::class, 'showProfile']);
Route::get('/profile/{user:username}/follower', [UserController::class, 'showProfileFollower']);
Route::get('/profile/{user:username}/following', [UserController::class, 'showProfileFollowing']);

// Profile RAW Routes
Route::middleware('cache.headers:public;max_age=20;etag')->group(function () {
    Route::get('/profile/{user:username}/raw', [UserController::class, 'showProfileRAW']);
    Route::get('/profile/{user:username}/follower/raw', [UserController::class, 'showProfileFollowerRAW']);
    Route::get('/profile/{user:username}/following/raw', [UserController::class, 'showProfileFollowingRAW']);
});

// Chat Routes
Route::post('/send-message', function (Request $request) {
    $formFields = $request->validate([
        'chatValue' => 'required|max:255',
    ]);

    if (!trim(strip_tags($formFields['chatValue']))) {
        return response()->noContent();
    }

    broadcast(new ChatMessage(['username' => auth()->user()->username, 'message' => strip_tags($request['chatValue']), 'avatar' => auth()->user()->avatar]))->toOthers();
    return response()->noContent();

})->middleware('isLoggedIn');