<?php

namespace App\Domain\Comercial\Actions;

use App\Models\AuditLog;
use App\Models\Nf;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelarPedidoAction
{
    public function execute(Pedido $pedido): Pedido
    {
        // Valida se o pedido pode ser cancelado
        if (!in_array($pedido->status, ['rascunho', 'confirmado', 'faturado'])) {
            throw ValidationException::withMessages([
                'status' => 'Pedido já está cancelado.',
            ]);
        }

        return DB::transaction(function () use ($pedido) {

            // Se faturado, precisa validar a NF e parcelas
            if ($pedido->status === 'faturado') {
                $nf = Nf::where('pedido_id', $pedido->id)->first();

                if (!$nf) {
                    throw ValidationException::withMessages([
                        'nf' => 'NF não encontrada para este pedido.',
                    ]);
                }

                // Só cancela se NF ainda está em rascunho
                if ($nf->status !== 'rascunho') {
                    throw ValidationException::withMessages([
                        'nf' => 'Não é possível cancelar. A NF já foi autorizada.',
                    ]);
                }

                // Verifica se existe alguma parcela paga
                $parcelasPagas = $nf->contasReceber()
                    ->where('status', 'paga')
                    ->count();

                if ($parcelasPagas > 0) {
                    throw ValidationException::withMessages([
                        'parcelas' => 'Não é possível cancelar. Existe parcela já paga.',
                    ]);
                }

                // Cancela NF
                $nf->update(['status' => 'cancelada']);

                // Cancela parcelas abertas
                $nf->contasReceber()
                    ->where('status', 'aberta')
                    ->update(['status' => 'cancelada']);
            }

            // Cancela o pedido
            $pedido->update([
                'status'       => 'cancelado',
                'cancelado_por' => auth()->id(),
                'cancelado_em'  => now(),
            ]);

            // Audit log
            AuditLog::record(
                entity: 'Pedido',
                entityId: $pedido->id,
                action: 'cancelado',
                metadata: [
                    'status_anterior' => $pedido->getOriginal('status'),
                ]
            );

            return $pedido->fresh();
        });
    }
}