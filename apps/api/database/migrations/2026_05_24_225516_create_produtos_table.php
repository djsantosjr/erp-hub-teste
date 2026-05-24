<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->string('codigo', 50);
            $table->string('descricao');
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['idempresa', 'codigo']);
            $table->index(['idempresa', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};