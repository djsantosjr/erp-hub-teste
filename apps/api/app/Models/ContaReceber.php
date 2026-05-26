<?php

namespace App\Models;

use App\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    use BelongsToEmpresa;
    protected $table = 'contas_receber';
    
    protected $fillable = [
        'idempresa',
        'nf_id',
        'parcela',
        'parcelas',
        'valor',
        'data_vencimento',
        'status',
    ];

    protected $casts = [
        'valor'           => 'decimal:2',
        'data_vencimento' => 'date',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function nf(): BelongsTo
    {
        return $this->belongsTo(Nf::class, 'nf_id');
    }
}