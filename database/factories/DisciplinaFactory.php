<?php

namespace Database\Factories;

use App\Models\Disciplina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Disciplina>
 */
class DisciplinaFactory extends Factory
{
    protected $model = Disciplina::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => 'PINC ' . fake()->unique()->numberBetween(1, 99),
        ];
    }
}
