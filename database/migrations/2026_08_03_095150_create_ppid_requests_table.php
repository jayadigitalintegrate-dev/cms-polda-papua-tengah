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
        Schema::create('ppid_requests', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS PEMOHON
            |--------------------------------------------------------------------------
            */

            $table->string('name');
            $table->string('identity_number', 50);
            $table->string('email');
            $table->string('phone', 30);
            $table->text('address');

            /*
            |--------------------------------------------------------------------------
            | PERMOHONAN INFORMASI
            |--------------------------------------------------------------------------
            */

            $table->longText('information');
            $table->longText('purpose');

            /*
            |--------------------------------------------------------------------------
            | CARA MEMPEROLEH INFORMASI
            |--------------------------------------------------------------------------
            */

            $table->enum('delivery_method', [
                'softcopy',
                'hardcopy',
                'view',
            ])->default('softcopy');

            /*
            |--------------------------------------------------------------------------
            | TIKET & STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('ticket', 30)->unique();

            $table->enum('status', [
                'diterima',
                'diverifikasi',
                'diproses',
                'selesai',
                'ditolak',
            ])->default('diterima');

            /*
            |--------------------------------------------------------------------------
            | TINDAK LANJUT PPID
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
            $table->index('delivery_method');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppid_requests');
    }
};