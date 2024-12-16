<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::all();

        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'photo1_url' => $post->photo1 ? asset('storage/' . $post->photo1) : null,
                'photo2_url' => $post->photo2 ? asset('storage/' . $post->photo2) : null,
            ];
        });

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'photo1_url' => $post->photo1 ? asset('storage/' . $post->photo1) : null,
            'photo2_url' => $post->photo2 ? asset('storage/' . $post->photo2) : null,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $blogPost = new BlogPost();
        $blogPost->title = $validated['title'];
        $blogPost->body = $validated['body'];

        if ($request->hasFile('photo1')) {
            $path = $request->file('photo1')->store('photos', 'public');
            $blogPost->photo1 = $path;
        }
        if ($request->hasFile('photo2')) {
            $path = $request->file('photo2')->store('photos', 'public');
            $blogPost->photo2 = $path;
        }

        $blogPost->save();

        return response()->json([
            'id' => $blogPost->id,
            'title' => $blogPost->title,
            'body' => $blogPost->body,
            'photo1_url' => $blogPost->photo1 ? asset('storage/' . $blogPost->photo1) : null,
            'photo2_url' => $blogPost->photo2 ? asset('storage/' . $blogPost->photo2) : null,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post->title = $validated['title'] ?? $post->title;
        $post->body = $validated['body'] ?? $post->body;

        if ($request->hasFile('photo1')) {
            if ($post->photo1) {
                Storage::disk('public')->delete($post->photo1);
            }
            $post->photo1 = $request->file('photo1')->store('photos', 'public');
        }

        if ($request->hasFile('photo2')) {
            if ($post->photo2) {
                Storage::disk('public')->delete($post->photo2);
            }
            $post->photo2 = $request->file('photo2')->store('photos', 'public');
        }

        $post->update();

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'photo1_url' => $post->photo1 ? asset('storage/' . $post->photo1) : null,
            'photo2_url' => $post->photo2 ? asset('storage/' . $post->photo2) : null,
        ]);
    }


    // Delete a blog post
    public function destroy($id)
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($post->photo1) {
            Storage::disk('public')->delete($post->photo1);
        }

        if ($post->photo2) {
            Storage::disk('public')->delete($post->photo2);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
