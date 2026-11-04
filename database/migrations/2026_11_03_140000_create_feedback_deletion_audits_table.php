<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_deletion_audits', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_uuid');
            // Historical identifiers deliberately have no cascading FK.
            $table->unsignedBigInteger('actor_user_id');
            $table->unsignedBigInteger('feedback_id');
            $table->timestamp('deleted_at');
            $table->string('action_type', 32);
            $table->unique(['operation_uuid', 'feedback_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_deletion_audits');
    }
};
