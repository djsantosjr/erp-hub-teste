<?php

namespace App\Domain\Comercial\Actions;

use App\Models\AuditLog;
use App\Models\Pedido;
use Illuminate\Validation\ValidationException;

class ConfirmarPedidoAction
{
    public function execute(Pedido $pedido): Pedido
    {
        // Valida status atual
        if ($pedido->status !== 'rascunho') {
            throw ValidationException::withMessages([
                'status' => 'Apenas pedidos em rascunho podem ser confirmados.',
            ]);
        }

        // Valida se tem pelo menos 1 item ativo — R1
        $itensAtivos = $pedido->itens()->where('ativo', true)->count();

        if ($itensAtivos < 1) {
            throw ValidationException::withMessages([
                'itens' => 'O pedido precisa ter pelo menos um item ativo para ser confirmado.',
            ]);
        }

        // Recalcula totais com base nos itens ativos — R1
        $subtotal = $pedido->itens()
            ->where('ativo', true)
            ->sum('total');

        $totalGeral = round($subtotal + $pedido->frete, 2);

        // Atualiza status e totais
        $pedido->update([
            'status'          => 'confirmado',
            'total_subtotal'  => $subtotal,
            'total_frete'     => $pedido->frete,
            'total_geral'     => $totalGeral,
            'confirmado_por'  => auth()->id(),
            'confirmado_em'   => now(),
        ]);

        // Audit log
        AuditLog::record(
            entity: 'Pedido',
            entityId: $pedido->id,
            action: 'confirmado',
            metadata: [
                'total_subtotal' => $subtotal,
                'total_geral'    => $totalGeral,
                'itens_ativos'   => $itensAtivos,
            ]
        );

        return $pedido->fresh();
    }
}
