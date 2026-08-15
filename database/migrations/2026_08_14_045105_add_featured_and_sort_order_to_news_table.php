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
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('featured')
                ->default(false)
                ->after('status');

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('featured');

            $table->index(['status', 'featured', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['status', 'featured', 'sort_order']);
            $table->dropColumn([
                'featured',
                'sort_order',
            ]);
        });
    }
};
