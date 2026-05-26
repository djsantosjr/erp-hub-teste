<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('documento', 18)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['idempresa', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};