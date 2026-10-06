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
        if (Schema::hasTable('galleries')) {
            return;
        }

        Schema::create('galleries', function (Blueprint $table) {

            $table->id();

            // Relasi kategori Galeri
            $table->foreignId('gallery_category_id')
                ->constrained('gallery_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Informasi utama
            $table->string('title');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            // Foto Galeri
            $table->string('image');

            // Tanggal dokumentasi
            $table->date('taken_at')->nullable();

            // Unggulan & urutan tampil
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            // Status
            $table->enum('status', [
                'draft',
                'published'
            ])->default('draft');

            // User CMS
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
