<?php

namespace Database\Seeders;

use App\Models\Disciplina;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DisciplinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disciplinas = [
            'PINC 1',
            'PINC 2',
            'PINC 3',
            'PINC 4',
        ];

        foreach($disciplinas as $nome) {
            Disciplina::firstOrCreate(['nome' => $nome]); //buscar um registro e, caso ele não exista, cria.
        }
    }
}
