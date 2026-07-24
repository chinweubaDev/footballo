@extends('layouts.app')

@section('title', 'Manage Blog - Admin Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manage Blog Posts</h1>
                    <p class="text-gray-600">Create, edit and manage blog articles</p>
                </div>
                <a href="{{ route('admin.blog.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i>New Post
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 responsive-table-admin">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($posts as $post)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4" data-label="Title">
                                <div class="flex items-center">
                                    @if($post->featured_image)
                                        <img src="{{ $post->featured_image }}" class="w-10 h-10 rounded object-cover mr-3">
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $post->title }}</div>
                                        <div class="text-xs text-gray-500">{{ Str::limit($post->excerpt, 60) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Category">
                                <span class="px-2 py-1 text-xs font-bold rounded-full
                                    @switch($post->category)
                                        @case('soccer') bg-green-100 text-green-800 @break
                                        @case('basketball') bg-orange-100 text-orange-800 @break
                                        @case('hockey') bg-blue-100 text-blue-800 @break
                                        @case('tennis') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">{{ ucfirst($post->category) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Date">
                                {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
                                <div class="flex space-x-2">
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-gray-400 hover:text-gray-600"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.blog.edit', $post) }}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.blog.toggle', $post) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $post->status === 'published' ? 'yellow' : 'green' }}-600 hover:text-{{ $post->status === 'published' ? 'yellow' : 'green' }}-900">
                                            <i class="fas {{ $post->status === 'published' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No blog posts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($posts->hasPages())<div class="px-6 py-4 border-t">{{ $posts->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
