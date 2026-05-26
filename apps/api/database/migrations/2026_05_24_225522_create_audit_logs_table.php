<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('entity', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50);
            $table->jsonb('metadata')->nullable();
            $table->timestamp('criado_em')->useCurrent();

            $table->index(['idempresa', 'entity', 'entity_id']);
            $table->index(['idempresa', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};