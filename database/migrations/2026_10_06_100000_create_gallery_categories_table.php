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
        if (Schema::hasTable('gallery_categories')) {
            return;
        }

        Schema::create('gallery_categories', function (Blueprint $table) {

            $table->id();

            // Nama kategori
            $table->string('name');

            // URL Friendly
            $table->string('slug')->unique();

            // Deskripsi kategori
            $table->text('description')->nullable();

            // Status aktif
            $table->boolean('is_active')->default(true);

            // Urutan tampil
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_categories');
    }
};
