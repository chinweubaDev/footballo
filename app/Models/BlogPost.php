<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category',
        'tags',
        'author',
        'status',
        'published_at',
        'source_url',
        'source_name',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->published()->orderBy('published_at', 'desc')->limit($limit);
    }

    public function getExcerptAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->content), 160);
    }

    public function getTagListAttribute()
    {
        return is_array($this->tags) ? $this->tags : [];
    }

    public function getCategoryLabelAttribute()
    {
        return ucfirst($this->category);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
