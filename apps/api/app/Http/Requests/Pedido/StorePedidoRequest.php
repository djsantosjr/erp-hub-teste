<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'         => ['required', 'integer', 'exists:clientes,id'],
            'data_emissao'       => ['required', 'date'],
            'data_validade'      => ['nullable', 'date', 'after_or_equal:data_emissao'],
            'frete'              => ['nullable', 'numeric', 'min:0'],
            'desconto_cabecalho' => ['nullable', 'numeric', 'min:0'],
            'parcelas'           => ['nullable', 'integer', 'min:1'],
            'dias_entrada'       => ['nullable', 'integer', 'min:0'],
            'intervalo'          => ['nullable', 'integer', 'min:1'],
            'itens'              => ['required', 'array', 'min:1'],
            'itens.*.produto_id'        => ['required', 'integer', 'exists:produtos,id'],
            'itens.*.quantidade'        => ['required', 'numeric', 'min:0.001'],
            'itens.*.preco_unitario'    => ['required', 'numeric', 'min:0'],
            'itens.*.desconto_unitario' => ['nullable', 'numeric', 'min:0'],
            'itens.*.ativo'             => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'      => 'O cliente é obrigatório.',
            'cliente_id.exists'        => 'Cliente não encontrado.',
            'data_emissao.required'    => 'A data de emissão é obrigatória.',
            'itens.required'           => 'O pedido precisa ter pelo menos um item.',
            'itens.*.produto_id.exists' => 'Produto não encontrado.',
            'itens.*.quantidade.min'   => 'A quantidade deve ser maior que zero.',
        ];
    }
}