<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departamento>
 */
class DepartamentoFactory extends Factory
{
    protected $model = Departamento::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => 'Departament de ' . fake()->words(2, true),
            'curso_id' => Curso::factory(),
        ];
    }
}
