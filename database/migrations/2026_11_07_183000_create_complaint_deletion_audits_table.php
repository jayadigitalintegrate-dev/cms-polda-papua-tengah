<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaint_deletion_audits')) {
            throw new RuntimeException(
                'Tabel audit penghapusan sudah tersedia.'
            );
        }

        if (! Schema::hasTable('complaints')) {
            throw new RuntimeException(
                'Tabel complaints tidak ditemukan.'
            );
        }

        if (! Schema::hasColumn('complaints', 'deleted_at')) {
            throw new RuntimeException(
                'Soft Delete belum tersedia.'
            );
        }

        Schema::create('complaint_deletion_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('complaint_id')
                ->constrained('complaints')
                ->restrictOnDelete();

            $table->string('ticket', 30);

            $table->enum('action', [
                'soft_delete',
                'restore',
            ]);

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at');

            $table->index(['complaint_id', 'created_at']);
            $table->index('actor_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_deletion_audits');
    }
};
