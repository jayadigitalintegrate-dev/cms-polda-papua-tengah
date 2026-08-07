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
        Schema::create('ppid_categories', function (Blueprint $table) {

            $table->id();

            // Nama kategori
            $table->string('name');

            // URL Friendly
            $table->string('slug')->unique();

            // Deskripsi kategori
            $table->text('description')->nullable();

            // Urutan tampil
            $table->unsignedInteger('sort_order')->default(0);

            // Status aktif
            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('ppid_categories');
    }
};