<?php
// ──────────────────────────────────────────────────────────────
// database/factories/RestauranteFactory.php
// ──────────────────────────────────────────────────────────────
namespace Database\Factories;

use App\Models\Restaurante;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestauranteFactory extends Factory
{
    protected $model = Restaurante::class;

    public function definition(): array
    {
        return [
            'nombre'          => fake()->company(),
            'descripcion'     => fake()->sentence(12),
            'direccion'       => fake()->streetAddress() . ', Motril',
            'latitud'         => fake()->latitude(36.70, 36.76),
            'longitud'        => fake()->longitude(-3.55, -3.48),
            'telefono'        => fake()->phoneNumber(),
            'categoria'       => fake()->randomElement(['tapas','mariscos','cocina_local','cafeteria','heladeria','internacional']),
            'precio_medio'    => fake()->randomElement(['€','€€','€€€']),
            'imagen_url'      => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600',
            'valoracion'      => fake()->randomFloat(2, 3.0, 5.0),
            'num_valoraciones'=> fake()->numberBetween(5, 300),
            'activo'          => true,
        ];
    }
}
