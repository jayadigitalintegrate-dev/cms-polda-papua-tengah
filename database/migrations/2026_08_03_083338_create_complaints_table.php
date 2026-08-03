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
        Schema::create('complaints', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS PENGADU
            |--------------------------------------------------------------------------
            */

            $table->string('nama');
            $table->string('ktp', 50);
            $table->string('hp', 30);
            $table->string('email');
            $table->text('alamat');

            /*
            |--------------------------------------------------------------------------
            | DATA PENGADUAN
            |--------------------------------------------------------------------------
            */

            $table->string('jenis');
            $table->longText('isi');

            /*
            |--------------------------------------------------------------------------
            | TIKET & STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('tiket', 30)->unique();

            $table->enum('status', [
                'diterima',
                'diverifikasi',
                'diproses',
                'selesai',
                'ditolak',
            ])->default('diterima');

            /*
            |--------------------------------------------------------------------------
            | TINDAK LANJUT ADMIN
            |--------------------------------------------------------------------------
            */

            $table->text('catatan_admin')->nullable();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index('status');
            $table->index('jenis');
            $table->index('email');
            $table->index('created_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};