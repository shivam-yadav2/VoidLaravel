<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Void Layer's own products (SaaS, apps, tools) — distinct from
        // `projects`, which are client case studies.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline', 200)->nullable();
            $table->string('category', 100)->nullable();      // e.g. "SaaS", "Mobile app"
            $table->string('status', 20)->default('live');   // live | beta | coming_soon
            $table->string('summary', 500);                    // card text
            $table->longText('description')->nullable();      // modal / detail page (HTML)
            $table->text('features')->nullable();             // one per line
            $table->string('tech_stack', 500)->nullable();    // comma separated
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_image_alt')->nullable();
            $table->string('video_path')->nullable();         // uploaded mp4/webm
            $table->string('video_url')->nullable();          // YouTube / Vimeo link
            $table->string('product_url')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();

            $table->index(['is_published', 'is_featured', 'sort_order']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
