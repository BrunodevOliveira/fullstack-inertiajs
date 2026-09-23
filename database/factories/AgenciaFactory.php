<?php

namespace Database\Factories;

use App\Enums\AgenciaTipoEnum;
use App\Models\Agencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agencia>
 */
class AgenciaFactory extends Factory
{
    protected $model = Agencia::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sigla' => strtoupper(fake()->unique()->lexify('????')),//gera uma sigla de 4 letras maiúsculas e únicas.
            'nome' => fake()->company(),
            'tipo' => fake()->randomElement(AgenciaTipoEnum::cases()),
        ];
    }
}
