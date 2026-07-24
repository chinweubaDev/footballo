<?php

namespace App\Console\Commands;

use App\Services\BlogAutomationService;
use Illuminate\Console\Command;

class BlogAutoGenerate extends Command
{
    protected $signature = 'blog:auto-generate';
    protected $description = 'Fetch news and auto-generate blog posts for all categories (10 per category)';

    public function handle(BlogAutomationService $blog): int
    {
        $this->info('📰 Auto-generating daily blog posts...');

        $created = $blog->generateDailyPosts();

        $this->info('✅ Done! ' . count($created) . ' posts created across categories.');
        return 0;
    }
}
