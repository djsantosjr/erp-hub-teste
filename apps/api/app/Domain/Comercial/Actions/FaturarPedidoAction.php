<?php

namespace App\Domain\Comercial\Actions;

use App\Models\AuditLog;
use App\Models\ContaReceber;
use App\Models\Nf;
use App\Models\NfItem;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FaturarPedidoAction
{
    public function execute(Pedido $pedido): Nf
    {
        // Valida status
        if ($pedido->status !== 'confirmado') {
            throw ValidationException::withMessages([
                'status' => 'Apenas pedidos confirmados podem ser faturados.',
            ]);
        }

        // R7 — Idempotência: se já existe NF, retorna ela
        $nfExistente = Nf::where('pedido_id', $pedido->id)->first();
        if ($nfExistente) {
            return $nfExistente;
        }

        return DB::transaction(function () use ($pedido) {

            // Busca itens ativos do pedido
            $itens = $pedido->itens()->where('ativo', true)->orderBy('id')->get();

            // R1 — Calcula subtotal apenas com itens ativos
            $subtotal = $itens->sum('total');
            $frete    = (float) $pedido->frete;
            $totalNf  = round($subtotal + $frete, 2);

            // Cria NF rascunho
            $nf = Nf::create([
                'idempresa'    => $pedido->idempresa,
                'pedido_id'    => $pedido->id,
                'tipo'         => 'V',
                'status'       => 'rascunho',
                'data_emissao' => $pedido->data_emissao,
                'subtotal'     => $subtotal,
                'frete'        => $frete,
                'total'        => $totalNf,
            ]);

            // R2 — Frete rateado proporcional por item
            // item.frete_rateado = round((item.total / subtotal) * frete, 2)
            // resíduo de centavo vai pro último item ativo
            $somaRateio = 0;
            $ultimo     = $itens->count() - 1;

            foreach ($itens as $index => $item) {
                if ($index < $ultimo) {
                    // Itens normais: frete proporcional ao valor
                    $rateio = $subtotal > 0
                        ? round(((float) $item->total / $subtotal) * $frete, 2)
                        : 0;
                } else {
                    // Último item: recebe o resíduo para fechar exato
                    $rateio = round($frete - $somaRateio, 2);
                }

                $somaRateio += $rateio;

                NfItem::create([
                    'nf_id'          => $nf->id,
                    'produto_id'     => $item->produto_id,
                    'numero_item'    => $index + 1,
                    'quantidade'     => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                    'total'          => $item->total,
                    'frete_rateado'  => $rateio,
                ]);
            }

            // R3 — Gera parcelas de contas a receber
            // 1ª parcela vence em: data_emissao + dias_entrada
            // próximas a cada: intervalo dias
            // resíduo de centavo na última parcela
            $numParcelas = max(1, (int) $pedido->parcelas);
            $diasEntrada = (int) $pedido->dias_entrada;
            $intervalo   = (int) $pedido->intervalo;
            $valorParcela = round($totalNf / $numParcelas, 2);
            $somaParcelas = 0;
            $parcelasIds  = [];

            for ($p = 1; $p <= $numParcelas; $p++) {
                if ($p < $numParcelas) {
                    $valor = $valorParcela;
                } else {
                    // Última parcela: resíduo para fechar exato no total
                    $valor = round($totalNf - $somaParcelas, 2);
                }

                $somaParcelas += $valor;

                // Calcula data de vencimento
                $dias       = $diasEntrada + ($p - 1) * $intervalo;
                $vencimento = $pedido->data_emissao->addDays($dias);

                $conta = ContaReceber::create([
                    'idempresa'       => $pedido->idempresa,
                    'nf_id'           => $nf->id,
                    'parcela'         => $p,
                    'parcelas'        => $numParcelas,
                    'valor'           => $valor,
                    'data_vencimento' => $vencimento,
                    'status'          => 'aberta',
                ]);

                $parcelasIds[] = $conta->id;
            }

            // Atualiza status do pedido
            $pedido->update([
                'status'      => 'faturado',
                'faturado_por' => auth()->id(),
                'faturado_em'  => now(),
            ]);

            // R8 — Audit log com nf_id e parcelas geradas
            AuditLog::record(
                entity: 'Pedido',
                entityId: $pedido->id,
                action: 'faturado',
                metadata: [
                    'nf_id'               => $nf->id,
                    'parcelas_geradas_ids' => $parcelasIds,
                    'total_nf'            => $totalNf,
                ]
            );

            // Mock do NfeDispatcher — não chama SEFAZ real
            $this->dispatchNfe($nf);

            return $nf;
        });
    }

    private function dispatchNfe(Nf $nf): void
    {
        // Stub — em produção chamaria a SEFAZ
        // NfeDispatcher::dispatch($nf);
        logger()->info("NFeDispatcher mock: NF {$nf->id} enviada para fila.");
    }
}
