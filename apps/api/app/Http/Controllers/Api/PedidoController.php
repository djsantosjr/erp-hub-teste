<?php

namespace App\Http\Controllers\Api;

use App\Domain\Comercial\Actions\CancelarPedidoAction;
use App\Domain\Comercial\Actions\ConfirmarPedidoAction;
use App\Domain\Comercial\Actions\FaturarPedidoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pedido\CancelarPedidoRequest;
use App\Http\Requests\Pedido\ConfirmarPedidoRequest;
use App\Http\Requests\Pedido\FaturarPedidoRequest;
use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Http\Requests\Pedido\UpdatePedidoRequest;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PedidoController extends Controller
{
    public function index(): JsonResponse
    {
        $pedidos = QueryBuilder::for(Pedido::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('cliente_id'),
                AllowedFilter::scope('data_emissao_entre'),
            ])
            ->allowedSorts(['data_emissao', 'total_geral'])
            ->with(['cliente', 'itens'])
            ->paginate(15);

        return response()->json($pedidos);
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        $pedido = DB::transaction(function () use ($request) {
            $pedido = Pedido::create([
                'cliente_id'         => $request->cliente_id,
                'data_emissao'       => $request->data_emissao,
                'data_validade'      => $request->data_validade,
                'frete'              => $request->frete ?? 0,
                'desconto_cabecalho' => $request->desconto_cabecalho ?? 0,
                'parcelas'           => $request->parcelas ?? 1,
                'dias_entrada'       => $request->dias_entrada ?? 28,
                'intervalo'          => $request->intervalo ?? 28,
                'status'             => 'rascunho',
            ]);

            foreach ($request->itens as $item) {
                $preco    = (float) $item['preco_unitario'];
                $desconto = (float) ($item['desconto_unitario'] ?? 0);
                $qtd      = (float) $item['quantidade'];

                // R1 — total do item
                $total = round(($preco - $desconto) * $qtd, 2);

                PedidoItem::create([
                    'pedido_id'         => $pedido->id,
                    'produto_id'        => $item['produto_id'],
                    'quantidade'        => $qtd,
                    'preco_unitario'    => $preco,
                    'desconto_unitario' => $desconto,
                    'total'             => $total,
                    'ativo'             => $item['ativo'] ?? true,
                ]);
            }

            return $pedido->load(['cliente', 'itens']);
        });

        return response()->json($pedido, 201);
    }

    public function show(Pedido $pedido): JsonResponse
    {
        return response()->json(
            $pedido->load(['cliente', 'itens.produto', 'nf.itens', 'nf.contasReceber'])
        );
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido): JsonResponse
    {
        if ($pedido->status !== 'rascunho') {
            return response()->json(['message' => 'Apenas pedidos em rascunho podem ser editados.'], 422);
        }

        DB::transaction(function () use ($request, $pedido) {
            $pedido->update($request->only([
                'cliente_id', 'data_emissao', 'data_validade',
                'frete', 'desconto_cabecalho', 'parcelas',
                'dias_entrada', 'intervalo',
            ]));

            if ($request->has('itens')) {
                $pedido->itens()->delete();

                foreach ($request->itens as $item) {
                    $preco    = (float) $item['preco_unitario'];
                    $desconto = (float) ($item['desconto_unitario'] ?? 0);
                    $qtd      = (float) $item['quantidade'];
                    $total    = round(($preco - $desconto) * $qtd, 2);

                    PedidoItem::create([
                        'pedido_id'         => $pedido->id,
                        'produto_id'        => $item['produto_id'],
                        'quantidade'        => $qtd,
                        'preco_unitario'    => $preco,
                        'desconto_unitario' => $desconto,
                        'total'             => $total,
                        'ativo'             => $item['ativo'] ?? true,
                    ]);
                }
            }
        });

        return response()->json($pedido->fresh()->load(['cliente', 'itens']));
    }

    public function confirmar(ConfirmarPedidoRequest $request, Pedido $pedido): JsonResponse
    {
        $this->authorize('confirmar', $pedido);    
        $pedido = (new ConfirmarPedidoAction)->execute($pedido);
        return response()->json($pedido);
    }

    public function faturar(FaturarPedidoRequest $request, Pedido $pedido): JsonResponse
    {
        $this->authorize('faturar', $pedido);   
        $nf = (new FaturarPedidoAction)->execute($pedido);
        return response()->json($nf->load(['itens', 'contasReceber']), 200);
    }

    public function cancelar(CancelarPedidoRequest $request, Pedido $pedido): JsonResponse
    {
        $this->authorize('cancelar', $pedido);
        $pedido = (new CancelarPedidoAction)->execute($pedido);
        return response()->json($pedido);
    }
}