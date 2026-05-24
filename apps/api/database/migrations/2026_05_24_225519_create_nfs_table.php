<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->string('numero', 50)->nullable();
            $table->enum('tipo', ['V', 'D'])->default('V');
            $table->enum('status', ['rascunho', 'autorizada', 'cancelada'])->default('rascunho');
            $table->date('data_emissao');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('frete', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['idempresa', 'status']);
            $table->index(['idempresa', 'pedido_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfs');
    }
};