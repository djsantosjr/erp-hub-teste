<?php

namespace App\Models;

use App\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nf extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'idempresa',
        'pedido_id',
        'numero',
        'tipo',
        'status',
        'data_emissao',
        'subtotal',
        'frete',
        'total',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'subtotal'     => 'decimal:2',
        'frete'        => 'decimal:2',
        'total'        => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(NfItem::class, 'nf_id');
    }

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class, 'nf_id');
    }
}
