<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Cria as permissions
        $permissions = [
            'pedidos.ver',
            'pedidos.criar',
            'pedidos.editar',
            'pedidos.confirmar',
            'pedidos.faturar',
            'pedidos.cancelar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Cria as roles
        $platformAdmin = Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);
        $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        $faturador = Role::firstOrCreate(['name' => 'comercial_faturador', 'guard_name' => 'web']);

        // Operador tem todas as permissões exceto faturar
        $operador->syncPermissions([
            'pedidos.ver',
            'pedidos.criar',
            'pedidos.editar',
            'pedidos.confirmar',
            'pedidos.cancelar',
        ]);

        // Faturador tem todas as permissões incluindo faturar
        $faturador->syncPermissions([
            'pedidos.ver',
            'pedidos.criar',
            'pedidos.editar',
            'pedidos.confirmar',
            'pedidos.faturar',
            'pedidos.cancelar',
        ]);

        $empresaA = Empresa::find(1);
        $empresaB = Empresa::find(2);

        // Admin — acesso às duas empresas
        $admin = User::create([
            'name'               => 'Administrador',
            'email'              => 'admin@erp.local',
            'password'           => Hash::make('admin123'),
            'idempresa_default'  => 1,
        ]);
        $admin->empresas()->attach([$empresaA->id, $empresaB->id]);

        setPermissionsTeamId(1);
        $admin->assignRole('platform_admin');
        setPermissionsTeamId(2);
        $admin->assignRole('platform_admin');

        // Gestor A — apenas Empresa A
        $gestorA = User::create([
            'name'              => 'Gestor A',
            'email'             => 'gestor.a@erp.local',
            'password'          => Hash::make('gestor123'),
            'idempresa_default' => 1,
        ]);
        $gestorA->empresas()->attach($empresaA->id);

        setPermissionsTeamId(1);
        $gestorA->assignRole('operador');

        // Faturador B — apenas Empresa B
        $faturadorB = User::create([
            'name'              => 'Faturador B',
            'email'             => 'faturador.b@erp.local',
            'password'          => Hash::make('fatura123'),
            'idempresa_default' => 2,
        ]);
        $faturadorB->empresas()->attach($empresaB->id);

        setPermissionsTeamId(2);
        $faturadorB->assignRole('comercial_faturador');
    }
}