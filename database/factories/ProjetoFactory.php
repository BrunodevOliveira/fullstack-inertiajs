<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\Projeto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Projeto>
 */
class ProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'assunto' => fake()->sentence(5),
            'descricao' => fake()->sentence(8),
            'vagas' => fake()->numberBetween(1, 10),
            'arquivado' => false,
            'departamento_id' => Departamento::factory(),
            'responsavel_id' => Usuario::factory(),
            'agencia_id' => null, // é opcional
        ];
    }

    public function arquivado(): static
    {
        return $this->state(fn(array $attributes) => [
            'arquivado' => true,
        ]);
    }
}
