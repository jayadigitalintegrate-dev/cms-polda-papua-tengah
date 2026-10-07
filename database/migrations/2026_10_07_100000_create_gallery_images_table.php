<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto-foto (child) milik satu koleksi Galeri Dokumentasi.
     */
    public function up(): void
    {
        if (Schema::hasTable('gallery_images')) {
            return;
        }

        Schema::create('gallery_images', function (Blueprint $table) {

            $table->id();

            // Koleksi induk; baris foto ikut terhapus bila koleksi dihapus.
            $table->foreignId('gallery_id')
                ->constrained('galleries')
                ->cascadeOnDelete();

            // Path foto pada disk public
            $table->string('image');

            // Urutan tampil dalam koleksi
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['gallery_id', 'sort_order']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
