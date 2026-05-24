<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->date('data_emissao');
            $table->date('data_validade')->nullable();
            $table->enum('status', ['rascunho', 'confirmado', 'faturado', 'cancelado'])->default('rascunho');
            $table->decimal('frete', 10, 2)->default(0);
            $table->decimal('desconto_cabecalho', 10, 2)->default(0);
            $table->integer('parcelas')->default(1);
            $table->integer('dias_entrada')->default(28);
            $table->integer('intervalo')->default(28);
            $table->decimal('total_subtotal', 10, 2)->default(0);
            $table->decimal('total_frete', 10, 2)->default(0);
            $table->decimal('total_geral', 10, 2)->default(0);
            $table->foreignId('confirmado_por')->nullable()->constrained('users');
            $table->timestamp('confirmado_em')->nullable();
            $table->foreignId('faturado_por')->nullable()->constrained('users');
            $table->timestamp('faturado_em')->nullable();
            $table->foreignId('cancelado_por')->nullable()->constrained('users');
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            $table->index(['idempresa', 'status']);
            $table->index(['idempresa', 'data_emissao']);
            $table->index(['idempresa', 'total_geral']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};