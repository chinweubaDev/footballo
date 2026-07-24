@extends('layouts.app')

@section('title', $post->title . ' - EsureBet Blog')
@section('meta_description', $post->excerpt)
@if($post->tags && count($post->tagList) > 0)
    @section('meta_keywords', implode(', ', $post->tagList))
@endif

@section('content')
<div class="bg-slate-950 min-h-screen pb-20">
    {{-- Article --}}
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-28">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest mb-8" data-aos="fade-up">
            <a href="{{ route('blog.index') }}" class="hover:text-primary-400 transition-colors">Blog</a>
            <span class="text-slate-700">/</span>
            <a href="{{ route('blog.category', $post->category) }}" class="hover:text-primary-400 transition-colors">{{ $post->category_label }}</a>
            <span class="text-slate-700">/</span>
            <span class="text-slate-400 truncate max-w-[200px]">{{ $post->title }}</span>
        </div>

        {{-- Featured Image --}}
        @if($post->featured_image)
        <div class="relative h-[300px] md:h-[450px] rounded-[2.5rem] overflow-hidden mb-10" data-aos="fade-up">
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>
        </div>
        @endif

        {{-- Header --}}
        <header class="mb-10" data-aos="fade-up">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-4 py-1.5 bg-primary-600/10 text-primary-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-primary-500/20">{{ $post->category_label }}</span>
                <span class="text-xs text-slate-500 font-bold">{{ $post->published_at->format('F d, Y') }}</span>
                <span class="text-xs text-slate-600">·</span>
                <span class="text-xs text-slate-500 font-bold">{{ $post->author }}</span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">{{ $post->title }}</h1>
        </header>

        {{-- Content --}}
        <div class="text-slate-200 text-base md:text-lg leading-relaxed space-y-4 mb-12" data-aos="fade-up">
            @foreach(explode("\n", $post->content) as $paragraph)
                @php $trimmed = trim($paragraph); @endphp
                @if(!empty($trimmed))
                    @if(preg_match('/^## (.+)/', $trimmed, $m))
                        <h2 class="text-xl md:text-2xl font-black text-white mt-8 mb-4">{{ $m[1] }}</h2>
                    @elseif(preg_match('/^\*\*(.+)\*\*/', $trimmed, $m))
                        <p class="font-bold text-white text-lg">{{ $m[1] }}{{ substr($trimmed, strlen($m[0])) }}</p>
                    @elseif(preg_match('/^• (.+)/', $trimmed, $m))
                        <li class="text-slate-300 ml-4 list-disc">{{ $m[1] }}</li>
                    @elseif(preg_match('/^---/', $trimmed))
                        <hr class="border-white/10 my-8">
                    @else
                        <p>{{ $trimmed }}</p>
                    @endif
                @endif
            @endforeach
        </div>

        {{-- Tags --}}
        @if($post->tags && count($post->tagList) > 0)
        <div class="flex flex-wrap gap-2 mb-12 pb-8 border-b border-white/5" data-aos="fade-up">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest mr-2 self-center">Tags:</span>
            @foreach($post->tagList as $tag)
                <a href="{{ route('blog.tag', $tag) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-xl hover:bg-primary-600 hover:text-white transition-all">
                    #{{ $tag }}
                </a>
            @endforeach
        </div>
        @endif

        {{-- Share --}}
        <div class="flex items-center gap-4 mb-16 pb-8 border-b border-white/5" data-aos="fade-up">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition-all"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-slate-400 hover:bg-blue-400 hover:text-white transition-all"><i class="fab fa-twitter"></i></a>
            <a href="https://telegram.me/share/url?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-slate-400 hover:bg-blue-500 hover:text-white transition-all"><i class="fab fa-telegram"></i></a>
            <a href="whatsapp://send?text={{ urlencode($post->title . ' - ' . request()->url()) }}" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-slate-400 hover:bg-green-600 hover:text-white transition-all"><i class="fab fa-whatsapp"></i></a>
        </div>
    </article>

    {{-- Related Posts --}}
    @if($related->count() > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-2xl font-black text-white">Related Articles</h2>
            <p class="text-slate-400 text-sm mt-2">More {{ $post->category_label }} news and predictions</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($related as $rel)
            <a href="{{ route('blog.show', $rel->slug) }}" class="group bg-slate-900/50 backdrop-blur-xl rounded-[1.5rem] border border-white/5 overflow-hidden hover:border-primary-500/30 transition-all" data-aos="fade-up">
                <div class="h-36 bg-slate-800 overflow-hidden">
                    @if($rel->featured_image)
                        <img src="{{ $rel->featured_image }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl opacity-20">{{ ['soccer' => '⚽', 'basketball' => '🏀', 'hockey' => '🏒', 'tennis' => '🎾'][$rel->category] ?? '📰' }}</div>
                    @endif
                </div>
                <div class="p-5">
                    <div class="text-[10px] font-bold text-primary-400 uppercase tracking-widest mb-2">{{ $rel->category_label }}</div>
                    <h3 class="text-sm font-black text-white group-hover:text-primary-400 transition-colors line-clamp-2 leading-snug">{{ $rel->title }}</h3>
                    <p class="text-[11px] text-slate-500 mt-2">{{ $rel->published_at->format('M d, Y') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-20" data-aos="fade-up">
        <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-[3rem] p-12 text-center shadow-2xl">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Never Miss a Winning Tip</h2>
            <p class="text-primary-100 text-lg mb-8 max-w-lg mx-auto">Get premium access for 99% accurate predictions and daily accumulator tips.</p>
            <a href="{{ route('pricing') }}" class="inline-flex items-center px-10 py-5 bg-white text-primary-700 rounded-2xl font-black text-sm uppercase tracking-widest hover:shadow-2xl hover:-translate-y-1 transition-all">
                <i class="fas fa-crown mr-3"></i> Upgrade to VIP
            </a>
        </div>
    </section>
</div>
@endsection
