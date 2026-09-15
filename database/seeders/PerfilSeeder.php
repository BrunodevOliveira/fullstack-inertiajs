<?php

namespace Database\Seeders;

use App\Enums\PerfilEnum;
use App\Models\Perfil;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PerfilEnum::cases() as $perfil) {
            Perfil::updateOrCreate(
                ['id' => $perfil->value],
                [
                    'nome' => $perfil->name,
                    'descricao' => $perfil->description(),
                ]
            );
        }
    }
}
