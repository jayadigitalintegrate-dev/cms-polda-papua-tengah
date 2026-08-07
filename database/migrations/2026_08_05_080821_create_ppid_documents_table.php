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
        Schema::create('ppid_documents', function (Blueprint $table) {

            $table->id();

            // Relasi kategori PPID
            $table->foreignId('ppid_category_id')
                ->constrained('ppid_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Informasi utama
            $table->string('title');
            $table->string('slug')->unique();

            $table->text('summary')->nullable();

            $table->longText('content')->nullable();

            // Nomor dokumen resmi
            $table->string('document_number')->nullable();

            // File PDF
            $table->string('document')->nullable();

            $table->string('document_name')->nullable();

            // Thumbnail
            $table->string('thumbnail')->nullable();

            // Tahun publikasi
            $table->year('publication_year')->nullable();

            // Urutan tampil
            $table->unsignedInteger('sort_order')->default(0);

            // Status
            $table->enum('status', [
                'draft',
                'published'
            ])->default('draft');

            // Tanggal publish
            $table->timestamp('published_at')->nullable();

            // Statistik download
            $table->unsignedBigInteger('download_count')->default(0);

            // Statistik view
            $table->unsignedBigInteger('view_count')->default(0);
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
        Schema::dropIfExists('ppid_documents');
    }
};





