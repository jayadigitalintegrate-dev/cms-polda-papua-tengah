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
        Schema::table('ppid_documents', function (Blueprint $table) {

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('publication_year');

            $table->unsignedBigInteger('view_count')
                ->default(0)
                ->after('download_count');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppid_documents', function (Blueprint $table) {

            $table->dropColumn([
                'sort_order',
                'view_count',
            ]);

        });
    }
};