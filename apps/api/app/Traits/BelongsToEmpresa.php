<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder) {
            if (auth()->check() && auth()->user()->idempresa_default) {
                $builder->where(
                    (new static)->getTable() . '.idempresa',
                    auth()->user()->idempresa_default
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !$model->idempresa) {
                $model->idempresa = auth()->user()->idempresa_default;
            }
        });
    }
}