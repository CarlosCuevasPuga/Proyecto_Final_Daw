<?php
// ──────────────────────────────────────────────────────────────
// database/factories/UserFactory.php
// ──────────────────────────────────────────────────────────────
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nombre'    => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'email'     => fake()->unique()->safeEmail(),
            'password'  => Hash::make('password'),
            'puntos'    => fake()->numberBetween(0, 500),
            'rol'       => 'turista',
            'suscripcion'=> 'gratis',
            'activo'    => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['rol' => 'admin', 'suscripcion' => 'premium']);
    }

    public function premium(): static
    {
        return $this->state(['suscripcion' => 'premium']);
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
