<?php

namespace Database\Seeders;

use App\Models\Restaurante;
use App\Models\Valoracion;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantesSeeder extends Seeder
{
    public function run(): void
    {
        $restaurantes = [
            [
                'nombre'          => 'El Puerto de Motril',
                'descripcion'     => 'Mariscos y pescados frescos del Mediterráneo con vistas al puerto.',
                'direccion'       => 'Paseo de los Moriscos, 12, Motril',
                'latitud'         => 36.7340,
                'longitud'        => -3.5100,
                'telefono'        => '958600001',
                'categoria'       => 'mariscos',
                'precio_medio'    => '€€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600',
                'valoracion'      => 4.5,
                'num_valoraciones'=> 128,
            ],
            [
                'nombre'          => 'Tapas La Caña',
                'descripcion'     => 'Bar de tapas tradicional con los mejores pinchos del centro.',
                'direccion'       => 'Calle Nueva, 5, Motril',
                'latitud'         => 36.7450,
                'longitud'        => -3.5200,
                'telefono'        => '958600002',
                'categoria'       => 'tapas',
                'precio_medio'    => '€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600',
                'valoracion'      => 4.2,
                'num_valoraciones'=> 89,
            ],
            [
                'nombre'          => 'Restaurante Guadalfeo',
                'descripcion'     => 'Cocina local con los sabores auténticos de la Costa Tropical.',
                'direccion'       => 'Av. de Salobreña, 34, Motril',
                'latitud'         => 36.7380,
                'longitud'        => -3.5150,
                'telefono'        => '958600003',
                'categoria'       => 'cocina_local',
                'precio_medio'    => '€€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600',
                'valoracion'      => 4.7,
                'num_valoraciones'=> 56,
            ],
            [
                'nombre'          => 'Café Central',
                'descripcion'     => 'El punto de encuentro para el café de la mañana y la merienda.',
                'direccion'       => 'Plaza de España, 1, Motril',
                'latitud'         => 36.7420,
                'longitud'        => -3.5170,
                'telefono'        => '958600004',
                'categoria'       => 'cafeteria',
                'precio_medio'    => '€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=600',
                'valoracion'      => 4.0,
                'num_valoraciones'=> 34,
            ],
            [
                'nombre'          => 'La Tasca Motrileña',
                'descripcion'     => 'Tradición e innovación en cada plato, cocina de mercado diaria.',
                'direccion'       => 'Calle Real, 22, Motril',
                'latitud'         => 36.7400,
                'longitud'        => -3.5180,
                'telefono'        => '958600005',
                'categoria'       => 'cocina_local',
                'precio_medio'    => '€€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600',
                'valoracion'      => 4.3,
                'num_valoraciones'=> 77,
            ],
            [
                'nombre'          => 'Heladería Mediterráneo',
                'descripcion'     => 'Los mejores helados artesanos con frutas tropicales de la Costa.',
                'direccion'       => 'Paseo Marítimo, 8, Motril',
                'latitud'         => 36.7310,
                'longitud'        => -3.5080,
                'telefono'        => '958600006',
                'categoria'       => 'heladeria',
                'precio_medio'    => '€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=600',
                'valoracion'      => 4.8,
                'num_valoraciones'=> 210,
            ],
            [
                'nombre'          => 'Mariscos Don Juanito',
                'descripcion'     => 'Gamba roja de Motril y espetos de sardinas como ninguno.',
                'direccion'       => 'Puerto Pesquero, Local 3, Motril',
                'latitud'         => 36.7300,
                'longitud'        => -3.5060,
                'telefono'        => '958600007',
                'categoria'       => 'mariscos',
                'precio_medio'    => '€€€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=600',
                'valoracion'      => 4.9,
                'num_valoraciones'=> 189,
            ],
            [
                'nombre'          => 'Sushi Tropical',
                'descripcion'     => 'Fusión asiática con ingredientes frescos de la Costa Tropical.',
                'direccion'       => 'Calle Enrique Martín, 15, Motril',
                'latitud'         => 36.7460,
                'longitud'        => -3.5220,
                'telefono'        => '958600008',
                'categoria'       => 'internacional',
                'precio_medio'    => '€€',
                'imagen_url'      => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=600',
                'valoracion'      => 4.1,
                'num_valoraciones'=> 45,
            ],
        ];

        foreach ($restaurantes as $data) {
            Restaurante::firstOrCreate(['nombre' => $data['nombre']], $data);
        }

        // Valoraciones de ejemplo
        $maria  = User::where('email', 'maria@example.com')->first();
        $carlos = User::where('email', 'carlos@example.com')->first();

        $valoraciones = [
            ['usuario_id' => $maria->id,  'restaurante_id' => 7, 'puntuacion' => 5, 'comentario' => 'La mejor gamba roja que he probado en mi vida. Imprescindible.'],
            ['usuario_id' => $maria->id,  'restaurante_id' => 1, 'puntuacion' => 4, 'comentario' => 'Muy buena relación calidad-precio, vistas al mar preciosas.'],
            ['usuario_id' => $carlos->id, 'restaurante_id' => 2, 'puntuacion' => 4, 'comentario' => 'Tapas riquísimas, ambiente muy local y auténtico.'],
        ];

        foreach ($valoraciones as $v) {
            Valoracion::firstOrCreate(
                ['usuario_id' => $v['usuario_id'], 'restaurante_id' => $v['restaurante_id']],
                $v
            );
        }
    }
}
