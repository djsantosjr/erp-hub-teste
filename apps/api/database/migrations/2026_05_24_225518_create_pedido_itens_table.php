<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('desconto_unitario', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['pedido_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};