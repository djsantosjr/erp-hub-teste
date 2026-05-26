<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nf_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nf_id')->constrained('nfs')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('numero_item');
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('frete_rateado', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['nf_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nf_itens');
    }
};