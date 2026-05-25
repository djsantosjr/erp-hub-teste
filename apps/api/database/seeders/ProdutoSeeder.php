<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = [
            // Empresa A
            ['idempresa' => 1, 'codigo' => 'PROD-001', 'descricao' => 'Parafuso Sextavado M8', 'preco_unitario' => 0.85],
            ['idempresa' => 1, 'codigo' => 'PROD-002', 'descricao' => 'Porca Sextavada M8', 'preco_unitario' => 0.45],
            ['idempresa' => 1, 'codigo' => 'PROD-003', 'descricao' => 'Arruela Lisa M8', 'preco_unitario' => 0.25],
            ['idempresa' => 1, 'codigo' => 'PROD-004', 'descricao' => 'Chapa de Aco 2mm', 'preco_unitario' => 45.90],
            ['idempresa' => 1, 'codigo' => 'PROD-005', 'descricao' => 'Tubo Redondo 1 pol', 'preco_unitario' => 32.50],
            // Empresa B
            ['idempresa' => 2, 'codigo' => 'PROD-001', 'descricao' => 'Tinta Epxi Cinza 18L', 'preco_unitario' => 189.90],
            ['idempresa' => 2, 'codigo' => 'PROD-002', 'descricao' => 'Solvente Industrial 5L', 'preco_unitario' => 45.00],
            ['idempresa' => 2, 'codigo' => 'PROD-003', 'descricao' => 'Pincel 2 pol', 'preco_unitario' => 8.50],
            ['idempresa' => 2, 'codigo' => 'PROD-004', 'descricao' => 'Rolo de Pintura 23cm', 'preco_unitario' => 12.90],
            ['idempresa' => 2, 'codigo' => 'PROD-005', 'descricao' => 'Fita Crepe 48mm', 'preco_unitario' => 5.75],
        ];

        foreach ($produtos as $produto) {
            Produto::create([...$produto, 'ativo' => true]);
        }
    }
}