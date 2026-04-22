<?php
// ──────────────────────────────────────────────────────────────
// database/factories/CuponFactory.php
// ──────────────────────────────────────────────────────────────
namespace Database\Factories;

use App\Models\Cupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CuponFactory extends Factory
{
    protected $model = Cupon::class;

    public function definition(): array
    {
        return [
            'codigo'            => strtoupper(Str::random(8)),
            'descripcion'       => fake()->sentence(8),
            'descuento_pct'     => fake()->randomElement([10, 15, 20, null]),
            'descuento_euros'   => null,
            'puntos_necesarios' => fake()->randomElement([0, 50, 80, 100, 150]),
            'max_usos'          => 100,
            'usos_actuales'     => 0,
            'solo_premium'      => false,
            'valido_desde'      => now()->subDays(30)->toDateString(),
            'valido_hasta'      => now()->addMonths(6)->toDateString(),
            'activo'            => true,
        ];
    }

    public function premium(): static
    {
        return $this->state(['solo_premium' => true]);
    }

    public function expirado(): static
    {
        return $this->state([
            'valido_desde' => now()->subMonths(3)->toDateString(),
            'valido_hasta' => now()->subDay()->toDateString(),
        ]);
    }
}
