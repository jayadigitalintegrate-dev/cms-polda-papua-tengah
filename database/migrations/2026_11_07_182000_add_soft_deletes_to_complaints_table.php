<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('complaints')) {
            throw new RuntimeException(
                'Tabel complaints tidak ditemukan.'
            );
        }

        if (Schema::hasColumn('complaints', 'deleted_at')) {
            throw new RuntimeException(
                'Kolom deleted_at sudah tersedia.'
            );
        }

        Schema::table('complaints', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('complaints')) {
            return;
        }

        if (Schema::hasColumn('complaints', 'deleted_at')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
