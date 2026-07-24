<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogAutomationService
{
    protected array $categories = ['soccer', 'basketball', 'hockey', 'tennis'];
    protected int $postsPerCategory = 10;

    /**
     * Generate and publish blog posts for all categories.
     */
    public function generateDailyPosts(): array
    {
        $created = [];
        $newsApiKey = config('services.newsapi.key');

        foreach ($this->categories as $category) {
            $this->info("Fetching {$category} news...");
            $articles = $this->fetchNews($category, $newsApiKey);

            $count = 0;
            foreach ($articles as $article) {
                if ($count >= $this->postsPerCategory) break;

                // Skip if slug already exists
                $slug = Str::slug($article['title']) . '-' . Str::random(4);
                if (BlogPost::where('slug', $slug)->exists()) continue;

                try {
                    $tags = $this->generateTags($category, $article);
                    $rawContent = $article['content'] ?? $article['description'] ?? 'No content available.';
                    // Strip NewsAPI truncation like "… [+10295 chars]"
                    $rawContent = preg_replace('/\s*\x{2026}\s*\[\+\d+\s*chars?\]\s*$/u', '', $rawContent);
                    $rawContent = preg_replace('/\s*\[\+\d+\s*chars?\]\s*$/i', '', $rawContent);
                    $excerpt = $article['description'] ?? Str::limit(strip_tags($rawContent), 160);

                    BlogPost::create([
                        'title' => $article['title'],
                        'slug' => $slug,
                        'content' => $rawContent,
                        'excerpt' => $excerpt,
                        'featured_image' => $article['urlToImage'] ?? null,
                        'category' => $category,
                        'tags' => $tags,
                        'author' => 'EsureBet',
                        'status' => 'published',
                        'published_at' => now(),
                        'source_url' => $article['url'] ?? null,
                        'source_name' => $article['source']['name'] ?? 'NewsAPI',
                    ]);

                    $count++;
                    $created[] = $category;
                } catch (\Exception $e) {
                    Log::error("Blog auto: failed to create {$category} post", [
                        'error' => $e->getMessage(),
                        'title' => $article['title'] ?? 'N/A',
                    ]);
                }
            }

            $this->info("   Created {$count} {$category} posts.");
        }

        return $created;
    }

    /**
     * Fetch news articles for a category.
     */
    protected function fetchNews(string $category, ?string $apiKey): array
    {
        // Map categories to NewsAPI keywords
        $keywords = [
            'soccer' => 'soccer OR football OR premier-league',
            'basketball' => 'basketball OR NBA',
            'hockey' => 'hockey OR NHL',
            'tennis' => 'tennis OR ATP OR WTA',
        ];

        $query = $keywords[$category] ?? $category;

        if (!$apiKey) {
            $this->info("   No API key configured, skipping {$category}.");
            return [];
        }

        try {
            $response = Http::timeout(15)->get('https://newsapi.org/v2/everything', [
                'q' => $query,
                'language' => 'en',
                'pageSize' => $this->postsPerCategory + 5,
                'sortBy' => 'publishedAt',
                'apiKey' => $apiKey,
            ]);

            if ($response->successful()) {
                $articles = $response->json()['articles'] ?? [];
                if (!empty($articles)) {
                    return array_filter($articles, fn($a) => !empty($a['title']) && $a['title'] !== '[Removed]');
                }
            }
        } catch (\Exception $e) {
            Log::warning("NewsAPI failed for {$category}: " . $e->getMessage());
        }

        $this->info("   No articles returned for {$category}, skipping.");
        return [];
    }

    /**
     * Generate relevant tags from article title and category.
     */
    protected function generateTags(string $category, array $article): array
    {
        $tags = [$category];

        $categoryTags = [
            'soccer' => ['football', 'premier-league', 'soccer-news', 'match-preview', 'betting-tips'],
            'basketball' => ['NBA', 'basketball-news', 'hoops', 'playoffs', 'bball'],
            'hockey' => ['NHL', 'hockey-news', 'puck', 'ice-hockey', 'playoffs'],
            'tennis' => ['ATP', 'WTA', 'tennis-news', 'grand-slam', 'racket'],
        ];
        $tags = array_merge($tags, $categoryTags[$category] ?? []);

        // Extract potential tags from title
        $title = strtolower($article['title'] ?? '');
        $keywords = ['premier league', 'champions league', 'nba finals', 'grand slam',
                     'transfer', 'injury', 'prediction', 'highlights', 'roundup',
                     'trade', 'draft', 'playoffs', 'championship'];
        foreach ($keywords as $kw) {
            if (str_contains($title, $kw)) {
                $tags[] = str_replace(' ', '-', $kw);
            }
        }

        return array_unique(array_slice($tags, 0, 8));
    }

    protected function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo "  {$message}\n";
        }
    }
}
