<?php
// ──────────────────────────────────────────────────────────────
// database/factories/RutaFactory.php
// ──────────────────────────────────────────────────────────────
namespace Database\Factories;

use App\Models\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition(): array
    {
        return [
            'nombre'        => 'Ruta ' . fake()->words(2, true),
            'descripcion'   => fake()->paragraph(),
            'dificultad'    => fake()->randomElement(['facil','media','dificil']),
            'duracion_min'  => fake()->randomElement([60, 90, 120, 180]),
            'puntos_reward' => fake()->randomElement([50, 75, 100, 150, 200, 300]),
            'imagen_url'    => 'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=600',
            'activa'        => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(['activa' => false]);
    }
}
