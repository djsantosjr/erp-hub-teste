<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Policies\PedidoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Define o team scope do Spatie Permission pela empresa do usuário logado
        setPermissionsTeamId(auth()->user()?->idempresa_default ?? 0);

        // Registra a Policy
        Gate::policy(Pedido::class, PedidoPolicy::class);

        // Platform admin tem acesso a tudo
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('platform_admin')) {
                return true;
            }
        });
    }
}