<?php

namespace App\Models;

use App\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produto extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'idempresa',
        'codigo',
        'descricao',
        'preco_unitario',
        'ativo',
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'ativo'          => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }
}