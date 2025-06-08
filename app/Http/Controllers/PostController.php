<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\PostRating;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $fields = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $fields['user_id'] = $user->id;

        if ($request->hasFile('image')) {
            $fields['image'] = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create($fields);

        return redirect()->back()->with('success', 'Post created!');
    }

    public function rate(Request $request, Post $post)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);

        if ($post->user_id === Auth::id()) {
            return response()->json(['error' => "You can't rate your own post."], 403);
        }

        $rating = PostRating::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => Auth::id()],
            ['rating' => $request->rating]
        );

        // Return new average and count
        $avg = round($post->ratings()->avg('rating'), 2);
        $count = $post->ratings()->count();

        return back();
    }

    public function unrate(Post $post)
    {
        $deleted = $post->ratings()->where('user_id', Auth::id())->delete();

        $avg = round($post->ratings()->avg('rating'), 2);
        $count = $post->ratings()->count();

        return back();
    }
}
