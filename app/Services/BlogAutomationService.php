<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogAutomationService
{
    protected array $categories = ['soccer', 'basketball', 'tennis'];
    protected int $postsPerCategory = 10;

    /**
     * RSS feed URLs for each sport from Sky Sports.
     */
    protected array $rssFeeds = [
        'soccer'     => 'https://www.skysports.com/rss/11095',
        'basketball' => 'https://www.skysports.com/rss/11096',
        'tennis'     => 'https://www.skysports.com/rss/12040',
    ];

    /**
     * Generate and publish blog posts for all categories.
     */
    public function generateDailyPosts(): array
    {
        $created = [];

        foreach ($this->categories as $category) {
            $this->info("Fetching {$category} news from Sky Sports...");
            $articles = $this->fetchRssFeed($category);

            $count = 0;
            foreach ($articles as $article) {
                if ($count >= $this->postsPerCategory) break;

                $slug = Str::slug($article['title']) . '-' . Str::random(4);
                if (BlogPost::where('slug', $slug)->exists()) continue;

                try {
                    $tags = $this->generateTags($category, $article);
                    $excerpt = Str::limit(strip_tags($article['description']), 160);

                    BlogPost::create([
                        'title'          => $article['title'],
                        'slug'           => $slug,
                        'content'        => $article['content'],
                        'excerpt'        => $excerpt,
                        'featured_image' => $article['image'] ?? null,
                        'category'       => $category,
                        'tags'           => $tags,
                        'author'         => 'Sky Sports',
                        'status'         => 'published',
                        'published_at'   => now(),
                        'source_url'     => $article['url'] ?? null,
                        'source_name'    => 'Sky Sports',
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
     * Fetch and parse a Sky Sports RSS feed for a given category.
     * When descriptions are empty, scrapes the article page for content.
     */
    protected function fetchRssFeed(string $category): array
    {
        $url = $this->rssFeeds[$category] ?? null;
        if (!$url) {
            $this->info("   No RSS feed for {$category}, skipping.");
            return [];
        }

        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) {
                $this->info("   Failed to fetch RSS feed for {$category}.");
                return [];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) {
                $this->info("   No items in RSS feed for {$category}.");
                return [];
            }

            $articles = [];
            foreach ($xml->channel->item as $item) {
                $title = trim((string) $item->title);
                if (empty($title)) continue;

                $description = trim((string) $item->description);
                $link = trim((string) $item->link);

                // Skip items with empty descriptions
                if (empty($description)) continue;
                // Skip live-blog items — no article content
                if (str_contains($link, '/live-blog/')) continue;

                // For tennis (general feed), filter to tennis-related articles only
                if ($category === 'tennis') {
                    $tennisKeywords = ['tennis', 'atp', 'wta', 'grand slam', 'djokovic', 'nadal', 'federer',
                        'murray', 'serena', 'williams', 'nishikori', 'medvedev', 'alcaraz',
                        'sinner', 'swiatek', 'rybakina', 'wimbledon',
                        'us open', 'french open', 'australian open', 'roland garros',
                        'racket', 'serve', 'match point', 'break point', 'clay court',
                        'grass court', 'hard court'];
                    $lowerTitle = strtolower($title);
                    $lowerDesc = strtolower($description);
                    $isTennis = str_contains($lowerTitle, 'tennis') || str_contains($lowerDesc, 'tennis');
                    // Also accept any article from the /tennis/ URL path
                    if (!$isTennis && $link) {
                        $isTennis = str_contains($link, '/tennis/');
                    }
                    // Broader keyword match if still not tennis
                    if (!$isTennis) {
                        foreach ($tennisKeywords as $kw) {
                            if (str_contains($lowerTitle, $kw) || str_contains($lowerDesc, $kw)) {
                                $isTennis = true;
                                break;
                            }
                        }
                    }
                    if (!$isTennis) continue;
                }

                // Get image from enclosure tag
                $image = null;
                if (isset($item->enclosure) && isset($item->enclosure['url'])) {
                    $image = trim((string) $item->enclosure['url']);
                }

                // Add source note at the end
                $content = $description;
                $content .= "\n\n---\n📰 Source: Sky Sports";
                if ($link) {
                    $content .= "\n🔗 <a href=\"{$link}\">Read more on Sky Sports →</a>";
                }

                $articles[] = [
                    'title'       => $title,
                    'description' => $description,
                    'content'     => $content,
                    'url'         => $link,
                    'image'       => $image,
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error("RSS fetch failed for {$category}: " . $e->getMessage());
            $this->info("   Error fetching RSS for {$category}.");
            return [];
        }
    }

    /**
     * Scrape article content from a Sky Sports page.
     * Extracts article body text from the HTML.
     */
    protected function scrapeArticleContent(string $url): string
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) return '';

            $html = $response->body();

            // Try to find article content in meta tags first (most reliable)
            $metaMatch = [];
            if (preg_match('/<meta\s+property="og:description"\s+content="([^"]+)"/i', $html, $metaMatch)) {
                $metaDesc = html_entity_decode($metaMatch[1], ENT_QUOTES, 'UTF-8');
                if (strlen($metaDesc) > 80) return $metaDesc;
            }

            // Try JSON-LD structured data
            if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $jsonMatch)) {
                $data = json_decode($jsonMatch[1], true);
                if ($data) {
                    $items = $data['itemListElement'] ?? [$data];
                    foreach ((array) $items as $item) {
                        $desc = $item['description'] ?? '';
                        $artBody = $item['articleBody'] ?? '';
                        if (!empty($artBody)) return strip_tags($artBody);
                        if (!empty($desc) && strlen($desc) > 80) return $desc;
                    }
                }
            }

            // Try to extract from HTML article body
            $bodyPatterns = [
                '/class="sdc-article-body"[^>]*>(.*?)<\/div>/s',
                '/class="article__body"[^>]*>(.*?)<\/div>/s',
                '/class="article-body"[^>]*>(.*?)<\/div>/s',
                '/itemprop="articleBody"[^>]*>(.*?)<\/div>/s',
            ];

            foreach ($bodyPatterns as $pattern) {
                if (preg_match($pattern, $html, $bodyMatch)) {
                    $text = strip_tags($bodyMatch[1]);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = trim($text);
                    if (strlen($text) > 100) return $text;
                }
            }

            return '';
        } catch (\Exception $e) {
            Log::warning("Scrape failed for {$url}: " . $e->getMessage());
            return '';
        }
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
