<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idempresa')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('nf_id')->constrained('nfs')->cascadeOnDelete();
            $table->integer('parcela');
            $table->integer('parcelas');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->enum('status', ['aberta', 'paga', 'cancelada'])->default('aberta');
            $table->timestamps();

            $table->index(['idempresa', 'status']);
            $table->index(['idempresa', 'nf_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_receber');
    }
};
