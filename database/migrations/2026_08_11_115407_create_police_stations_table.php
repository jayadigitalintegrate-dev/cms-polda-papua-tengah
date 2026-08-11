<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('police_stations', function (Blueprint $table) {
            $table->id();

            // Identitas Polres
            $table->string('name_id');
            $table->string('name_en')->nullable();

            // Kapolres
            $table->string('chief_name')->nullable();
            $table->string('chief_rank')->nullable();
            $table->string('chief_nrp')->nullable();
            $table->string('chief_photo')->nullable();

            // Wilayah dan kontak
            $table->text('jurisdiction')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Informasi tambahan
            $table->text('description')->nullable();

            // Pengaturan tampilan
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('police_stations');
    }
};
