<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'         => ['sometimes', 'integer', 'exists:clientes,id'],
            'data_emissao'       => ['sometimes', 'date'],
            'data_validade'      => ['nullable', 'date', 'after_or_equal:data_emissao'],
            'frete'              => ['nullable', 'numeric', 'min:0'],
            'desconto_cabecalho' => ['nullable', 'numeric', 'min:0'],
            'parcelas'           => ['nullable', 'integer', 'min:1'],
            'dias_entrada'       => ['nullable', 'integer', 'min:0'],
            'intervalo'          => ['nullable', 'integer', 'min:1'],
            'itens'              => ['sometimes', 'array', 'min:1'],
            'itens.*.produto_id'        => ['required_with:itens', 'integer', 'exists:produtos,id'],
            'itens.*.quantidade'        => ['required_with:itens', 'numeric', 'min:0.001'],
            'itens.*.preco_unitario'    => ['required_with:itens', 'numeric', 'min:0'],
            'itens.*.desconto_unitario' => ['nullable', 'numeric', 'min:0'],
            'itens.*.ativo'             => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists'         => 'Cliente não encontrado.',
            'itens.*.produto_id.exists' => 'Produto não encontrado.',
            'itens.*.quantidade.min'    => 'A quantidade deve ser maior que zero.',
        ];
    }
}