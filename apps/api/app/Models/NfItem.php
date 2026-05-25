<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NfItem extends Model
{
    protected $table = 'nf_itens';
    
    protected $fillable = [
        'nf_id',
        'produto_id',
        'numero_item',
        'quantidade',
        'preco_unitario',
        'total',
        'frete_rateado',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'total'          => 'decimal:2',
        'frete_rateado'  => 'decimal:2',
    ];

    public function nf(): BelongsTo
    {
        return $this->belongsTo(Nf::class, 'nf_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}