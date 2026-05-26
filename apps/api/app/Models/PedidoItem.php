<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    protected $table = 'pedido_itens';
    
    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco_unitario',
        'desconto_unitario',
        'total',
        'ativo',
    ];

    protected $casts = [
        'quantidade'        => 'decimal:3',
        'preco_unitario'    => 'decimal:2',
        'desconto_unitario' => 'decimal:2',
        'total'             => 'decimal:2',
        'ativo'             => 'boolean',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}