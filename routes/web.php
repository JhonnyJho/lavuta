<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

Route::get('/', function(Request $request) {
    return inertia('Home', ['users' => User::when($request->search, function($query) use ($request) {
        $query->where('name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%');
    })->paginate(5)->withQueryString(),

    'searchTerm' => $request->search,

    'can' => [
        'delete_user' => Auth::user() ? Auth::user()->can('delete', User::class) : null
    ]
]);
})->name('home');


Route::get('/account', function() {
    $user = Auth::user();
    return inertia('UserProfile', [
        'user' => $user,
        'canPost' => true,
        'posts' => $user->posts()->with('user', 'ratings')->latest()->get()->map(function($post) {
            $post->avg_rating = $post->ratings->avg('rating');
            $post->user_rating = $post->ratings->where('user_id', Auth::id())->first()?->rating;
            $post->ratings_count = $post->ratings->count();
            return $post;
        }),
    ]);
})->middleware('auth')->name('account');

Route::get('/users/{user}', function(App\Models\User $user) 
{
    return inertia('UserProfile', [
        'user' => $user,
        'canPost' => Auth::check() && Auth::id() === $user->id,
        'userId' => Auth::id(),
        'posts' => $user->posts()->with('user', 'ratings')->latest()->get()->map(function($post) {
            $post->avg_rating = $post->ratings->avg('rating');
            $post->user_rating = $post->ratings->where('user_id', Auth::id())->first()?->rating;
            $post->ratings_count = $post->ratings->count();
            return $post;
        }),
    ]);
})->name('users.profile');

Route::post('/users/{user}/posts', [PostController::class, 'store'])->middleware('auth')->name('posts.store');

Route::get('/posts', function() {
    return inertia('Posts', [
        'posts' => \App\Models\Post::with('user', 'ratings')->latest()->get()->map(function($post) {
            $post->avg_rating = $post->ratings->avg('rating');
            $post->user_rating = $post->ratings->where('user_id', Auth::id())->first()?->rating;
            $post->ratings_count = $post->ratings->count();
            return $post;
        }),
        'userId' => Auth::id(),
    ]);
})->name('posts.index');

Route::post('/posts/{post}/rate', [PostController::class, 'rate'])->middleware('auth')->name('posts.rate');

Route::delete('/posts/{post}/rate', [PostController::class, 'unrate'])->middleware('auth')->name('posts.unrate');

Route::delete('/users/{user}', [AuthController::class, 'delete'])->name('users.delete');

Route::middleware('auth')->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::inertia('/register', 'Auth/Register')->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});