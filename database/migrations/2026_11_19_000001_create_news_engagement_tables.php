<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->timestamp('created_at');
            $table->unique(['news_id', 'visitor_hash']);
        });

        Schema::create('news_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->timestamp('created_at');
            $table->unique(['news_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_likes');
        Schema::dropIfExists('news_views');
    }
};
