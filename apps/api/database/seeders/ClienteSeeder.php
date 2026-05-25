<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            // Empresa A
            ['idempresa' => 1, 'nome' => 'Metalurgica Santos Ltda', 'documento' => '33.333.333/0001-33'],
            ['idempresa' => 1, 'nome' => 'Industria Ferreira SA', 'documento' => '44.444.444/0001-44'],
            ['idempresa' => 1, 'nome' => 'Construcoes Oliveira ME', 'documento' => '55.555.555/0001-55'],
            // Empresa B
            ['idempresa' => 2, 'nome' => 'Pinturas Souza Ltda', 'documento' => '66.666.666/0001-66'],
            ['idempresa' => 2, 'nome' => 'Reformas Costa ME', 'documento' => '77.777.777/0001-77'],
            ['idempresa' => 2, 'nome' => 'Acabamentos Lima SA', 'documento' => '88.888.888/0001-88'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create([...$cliente, 'ativo' => true]);
        }
    }
}