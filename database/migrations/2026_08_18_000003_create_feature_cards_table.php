<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shared table for the two "icon card" grids that use identical markup:
     * the Services page ("What We Build") and the About page ("How We
     * Work"). `group` tells them apart; one admin screen manages both,
     * filtered by tab, rather than two near-identical CRUD screens.
     */
    public function up(): void
    {
        Schema::create('feature_cards', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // 'services' | 'about_values'
            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_cards');
    }
};
