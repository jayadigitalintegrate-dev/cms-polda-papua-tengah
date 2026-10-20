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
        Schema::create('feedback_submissions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | ISI MASUKAN
            |--------------------------------------------------------------------------
            */

            $table->enum('category', [
                'apresiasi',
                'saran',
                'kritik',
            ]);

            $table->unsignedTinyInteger('rating');

            $table->text('message');

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS PENGIRIM (OPSIONAL)
            |--------------------------------------------------------------------------
            */

            $table->string('name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | PERSETUJUAN & ANTI-PENYALAHGUNAAN
            |--------------------------------------------------------------------------
            |
            | IP tidak disimpan mentah; hanya HMAC-SHA256 dengan kunci aplikasi.
            |
            */

            $table->timestamp('consent_at');
            $table->string('consent_version', 20);
            $table->char('ip_hash', 64)->nullable();

            /*
            |--------------------------------------------------------------------------
            | PENANGANAN ADMIN (TIDAK PERNAH DIKIRIM KE PUBLIK)
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'baru',
                'ditinjau',
                'ditindaklanjuti',
                'diarsipkan',
            ])->default('baru');

            $table->text('admin_note')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index('category');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_submissions');
    }
};
