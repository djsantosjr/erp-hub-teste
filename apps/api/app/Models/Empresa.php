<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = [
        'nome',
        'cnpj',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'idempresa');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'idempresa');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'idempresa');
    }

    public function usuarios(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_empresa', 'empresa_id', 'usuario_id');
    }
}