<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,
            UserSeeder::class,
            ProdutoSeeder::class,
            ClienteSeeder::class,
        ]);
    }
}