<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cpf' => fake()->unique()->numerify('###########'),
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'situacao' => true
        ];
    }
}
