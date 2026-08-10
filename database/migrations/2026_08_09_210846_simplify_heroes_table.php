<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyederhanakan tabel heroes.
     *
     * Hero CMS hanya mengelola:
     * - image
     * - status
     * - sort_order
     */
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'subtitle',
                'button_text',
                'button_url',
                'published_at',
            ]);
        });
    }

    /**
     * Mengembalikan kolom Hero seperti sebelumnya.
     */
    public function down(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->text('subtitle')->nullable()->after('title');
            $table->string('button_text')->nullable()->after('image');
            $table->string('button_url')->nullable()->after('button_text');
            $table->timestamp('published_at')->nullable()->after('sort_order');
        });
    }
};