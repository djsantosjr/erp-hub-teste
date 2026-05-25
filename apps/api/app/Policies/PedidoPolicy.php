<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pedidos.ver');
    }

    public function view(User $user, Pedido $pedido): bool
    {
        // R4 — multi-tenant: usuário só pode ver pedido da sua empresa
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.ver');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('pedidos.criar');
    }

    public function update(User $user, Pedido $pedido): bool
    {
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.editar');
    }

    public function delete(User $user, Pedido $pedido): bool
    {
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.editar');
    }

    public function confirmar(User $user, Pedido $pedido): bool
    {
        // R4 + R5 — tenant e permissão
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.confirmar');
    }

    public function faturar(User $user, Pedido $pedido): bool
    {
        // R5 — apenas comercial_faturador pode faturar
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.faturar');
    }

    public function cancelar(User $user, Pedido $pedido): bool
    {
        return $pedido->idempresa === $user->idempresa_default
            && $user->hasPermissionTo('pedidos.cancelar');
    }
}