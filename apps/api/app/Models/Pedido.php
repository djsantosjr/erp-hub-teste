<?php

namespace App\Models;

use App\Traits\BelongsToEmpresa;
use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use BelongsToEmpresa, AuditsChanges;

    protected $fillable = [
        'idempresa',
        'cliente_id',
        'data_emissao',
        'data_validade',
        'status',
        'frete',
        'desconto_cabecalho',
        'parcelas',
        'dias_entrada',
        'intervalo',
        'total_subtotal',
        'total_frete',
        'total_geral',
        'confirmado_por',
        'confirmado_em',
        'faturado_por',
        'faturado_em',
        'cancelado_por',
        'cancelado_em',
    ];

    protected $casts = [
        'data_emissao'   => 'date',
        'data_validade'  => 'date',
        'frete'          => 'decimal:2',
        'desconto_cabecalho' => 'decimal:2',
        'total_subtotal' => 'decimal:2',
        'total_frete'    => 'decimal:2',
        'total_geral'    => 'decimal:2',
        'confirmado_em'  => 'datetime',
        'faturado_em'    => 'datetime',
        'cancelado_em'   => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function itensAtivos(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id')->where('ativo', true);
    }

    public function nf(): HasOne
    {
        return $this->hasOne(Nf::class, 'pedido_id');
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    public function faturadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faturado_por');
    }

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }
}