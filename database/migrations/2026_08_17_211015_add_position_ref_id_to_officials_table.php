<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->foreignId('position_ref_id')
                ->nullable()
                ->after('position_id')
                ->constrained('positions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->dropForeign(['position_ref_id']);
            $table->dropColumn('position_ref_id');
        });
    }
};
