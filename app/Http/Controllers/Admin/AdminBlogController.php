<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|url|max:500',
            'category' => 'required|in:soccer,basketball,hockey,tennis',
            'tags' => 'nullable|string',
            'author' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        $data['tags'] = $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [];
        $data['author'] = $data['author'] ?? 'EsureBet';
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created!');
    }

    public function edit(BlogPost $post)
    {
        return view('admin.blog.form', compact('post'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|url|max:500',
            'category' => 'required|in:soccer,basketball,hockey,tennis',
            'tags' => 'nullable|string',
            'author' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        $data['tags'] = $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [];
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated!');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }

    public function toggleStatus(BlogPost $post)
    {
        if ($post->status === 'published') {
            $post->update(['status' => 'draft']);
        } else {
            $post->update(['status' => 'published', 'published_at' => now()]);
        }
        return back()->with('success', 'Status updated.');
    }
}
