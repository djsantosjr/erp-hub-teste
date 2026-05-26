<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'id'   => 1,
            'nome' => 'Empresa A',
            'cnpj' => '11.111.111/0001-11',
            'ativo' => true,
        ]);

        Empresa::create([
            'id'   => 2,
            'nome' => 'Empresa B',
            'cnpj' => '22.222.222/0001-22',
            'ativo' => true,
        ]);
    }
}