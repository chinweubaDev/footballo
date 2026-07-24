<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('category'); // soccer, basketball, hockey, tennis
            $table->json('tags')->nullable();
            $table->string('author')->default('EsureBet');
            $table->string('status')->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->string('source_url')->nullable();
            $table->string('source_name')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('status');
            $table->index('published_at');
            $table->fullText(['title', 'content', 'excerpt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
