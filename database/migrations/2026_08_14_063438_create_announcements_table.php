<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            // Identitas pengumuman
            $table->string('title');
            $table->string('slug')->unique();

            // Isi pengumuman
            $table->text('description')->nullable();
            $table->longText('content')->nullable();

            // Media
            $table->string('image')->nullable();
            $table->string('attachment')->nullable();
            $table->string('attachment_name')->nullable();

            // Jenis dan tingkat prioritas
            $table->enum('priority', [
                'high',
                'medium',
                'low',
            ])->default('medium');

            $table->enum('type', [
                'banner',
                'popup',
                'info',
            ])->default('popup');

            // Periode publikasi
            $table->dateTime('publish_start')->nullable();
            $table->dateTime('publish_end')->nullable();

            // Pengaturan tampilan
            $table->boolean('featured')->default(false);
            $table->enum('status', [
                'published',
                'draft',
                'expired',
                'archived',
            ])->default('draft');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Index untuk query API
            $table->index([
                'status',
                'type',
                'featured',
                'sort_order',
            ]);

            $table->index([
                'publish_start',
                'publish_end',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
