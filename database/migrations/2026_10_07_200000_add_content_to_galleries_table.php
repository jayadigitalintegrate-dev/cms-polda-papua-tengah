<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Isi berita/editorial untuk item Media Center.
     * Nullable: item Galeri lain (foto tunggal & Galeri Dokumentasi) tidak memakainya.
     */
    public function up(): void
    {
        if (Schema::hasColumn('galleries', 'content')) {
            return;
        }

        Schema::table('galleries', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('galleries', 'content')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->dropColumn('content');
            });
        }
    }
};
