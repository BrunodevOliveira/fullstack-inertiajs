<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Campus;
use App\Models\Disciplina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(3, true),
            'campus_id' => Campus::factory(), //Ao criar um Curso, crie também um Campus para ele e use o ID desse Campus como campus_id.
            'disciplina_id' => Disciplina::factory(),
            'documento_id' => fake()->randomNumber(4),
            'email' => fake()->safeEmail(),
            'colaborador' => fake()->boolean(25),
        ];
    }
}
