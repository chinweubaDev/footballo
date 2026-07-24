<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published();

        if ($request->s) {
            $search = $request->s;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Soccer first, then basketball, hockey, tennis
        $posts = $query->orderByRaw("FIELD(category, 'soccer', 'basketball', 'hockey', 'tennis')")
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = [
            'soccer' => BlogPost::published()->byCategory('soccer')->count(),
            'basketball' => BlogPost::published()->byCategory('basketball')->count(),
            'hockey' => BlogPost::published()->byCategory('hockey')->count(),
            'tennis' => BlogPost::published()->byCategory('tennis')->count(),
        ];

        $popularTags = BlogPost::published()->get()->flatMap->tagList->countBy()->sortDesc()->take(10);

        return view('blog.index', compact('posts', 'categories', 'popularTags'));
    }

    public function category($category)
    {
        if (!in_array($category, ['soccer', 'basketball', 'hockey', 'tennis'])) {
            abort(404);
        }

        $posts = BlogPost::published()
            ->byCategory($category)
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = [
            'soccer' => BlogPost::published()->byCategory('soccer')->count(),
            'basketball' => BlogPost::published()->byCategory('basketball')->count(),
            'hockey' => BlogPost::published()->byCategory('hockey')->count(),
            'tennis' => BlogPost::published()->byCategory('tennis')->count(),
        ];

        $popularTags = BlogPost::published()->get()->flatMap->tagList->countBy()->sortDesc()->take(10);

        return view('blog.index', compact('posts', 'categories', 'popularTags'));
    }

    public function byTag($tag)
    {
        $posts = BlogPost::published()
            ->whereJsonContains('tags', $tag)
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = [
            'soccer' => BlogPost::published()->byCategory('soccer')->count(),
            'basketball' => BlogPost::published()->byCategory('basketball')->count(),
            'hockey' => BlogPost::published()->byCategory('hockey')->count(),
            'tennis' => BlogPost::published()->byCategory('tennis')->count(),
        ];

        $popularTags = BlogPost::published()->get()->flatMap->tagList->countBy()->sortDesc()->take(10);

        return view('blog.index', compact('posts', 'categories', 'popularTags'));
    }

    public function show($slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        $related = BlogPost::published()
            ->byCategory($post->category)
            ->where('id', '!=', $post->id)
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
