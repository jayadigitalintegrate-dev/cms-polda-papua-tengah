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
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();

            // Konten Hero
            $table->string('title');
            $table->text('subtitle')->nullable();

            // Gambar Hero
            $table->string('image');

            // Tombol CTA (opsional)
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();

            // Pengaturan tampilan
            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->unsignedInteger('sort_order')
                ->default(0);

            // Waktu publikasi
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};