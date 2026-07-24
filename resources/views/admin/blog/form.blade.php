@extends('layouts.app')

@section('title', (isset($post) ? 'Edit' : 'Create') . ' Blog Post - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">{{ isset($post) ? 'Edit' : 'Create' }} Blog Post</h1>
                <a href="{{ route('admin.blog.index') }}" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left mr-1"></i> Back</a>
            </div>
        </div>

        <form action="{{ isset($post) ? route('admin.blog.update', $post) : route('admin.blog.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8 space-y-6">
            @csrf
            @if(isset($post)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="soccer" {{ (old('category', $post->category ?? '') === 'soccer') ? 'selected' : '' }}>⚽ Soccer</option>
                        <option value="basketball" {{ (old('category', $post->category ?? '') === 'basketball') ? 'selected' : '' }}>🏀 Basketball</option>
                        <option value="hockey" {{ (old('category', $post->category ?? '') === 'hockey') ? 'selected' : '' }}>🏒 Hockey</option>
                        <option value="tennis" {{ (old('category', $post->category ?? '') === 'tennis') ? 'selected' : '' }}>🎾 Tennis</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="draft" {{ (old('status', $post->status ?? '') === 'draft') ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ (old('status', $post->status ?? '') === 'published') ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                    <input type="url" name="featured_image" value="{{ old('featured_image', $post->featured_image ?? '') }}" placeholder="https://..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <input type="text" name="author" value="{{ old('author', $post->author ?? 'EsureBet') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ old('tags', $post->tags ? implode(', ', $post->tagList) : '') }}" placeholder="e.g. premier-league, match-preview, betting-tips" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                    <textarea name="excerpt" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                    <textarea name="content" rows="15" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 font-mono text-sm">{{ old('content', $post->content ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                    {{ isset($post) ? 'Update' : 'Create' }} Post
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
