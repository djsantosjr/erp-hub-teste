<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'idempresa',
        'user_id',
        'entity',
        'entity_id',
        'action',
        'metadata',
        'criado_em',
    ];

    protected $casts = [
        'metadata' => 'array',
        'criado_em' => 'datetime',
    ];

    public static function record(
        string $entity,
        int $entityId,
        string $action,
        array $metadata = [],
        ?int $idempresa = null,
        ?int $userId = null
    ): void {
        static::create([
            'idempresa' => $idempresa ?? auth()->user()?->idempresa_default,
            'user_id'   => $userId ?? auth()->id(),
            'entity'    => $entity,
            'entity_id' => $entityId,
            'action'    => $action,
            'metadata'  => $metadata,
            'criado_em' => now(),
        ]);
    }
}