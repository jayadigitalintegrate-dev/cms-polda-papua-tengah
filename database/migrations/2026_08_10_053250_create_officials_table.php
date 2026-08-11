<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->id();

            // Identitas utama
            $table->string('photo')->nullable();
            $table->string('name_id');
            $table->string('name_en')->nullable();
            $table->string('rank')->nullable();
            $table->string('position_id');
            $table->string('position_en')->nullable();

            // Biodata
            $table->string('nrp')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('spouse')->nullable();
            $table->unsignedInteger('children')->default(0);
            $table->text('motto')->nullable();

            // Data riwayat
            $table->json('education')->nullable();
            $table->json('assignments')->nullable();
            $table->json('career')->nullable();
            $table->json('awards')->nullable();

            // Pengaturan tampilan
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};