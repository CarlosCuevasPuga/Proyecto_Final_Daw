<?php

namespace Database\Seeders;

use App\Models\ProgresoRuta;
use App\Models\Restaurante;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Database\Seeder;

class RutasSeeder extends Seeder
{
    public function run(): void
    {
        $rutas = [
            [
                'nombre'        => 'Ruta del Puerto',
                'descripcion'   => 'Descubre los mejores sabores del puerto pesquero de Motril. Mariscos, pescados frescos y el ambiente marinero único de nuestra ciudad.',
                'dificultad'    => 'facil',
                'duracion_min'  => 90,
                'puntos_reward' => 100,
                'imagen_url'    => 'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=600',
                'paradas'       => [
                    ['nombre' => 'Mariscos Don Juanito',   'orden' => 1],
                    ['nombre' => 'El Puerto de Motril',    'orden' => 2],
                    ['nombre' => 'Heladería Mediterráneo', 'orden' => 3],
                ],
            ],
            [
                'nombre'        => 'Ruta del Centro Histórico',
                'descripcion'   => 'Recorre el casco antiguo de Motril entre tapas tradicionales y cafeterías con encanto. La historia y la gastronomía se dan la mano.',
                'dificultad'    => 'facil',
                'duracion_min'  => 120,
                'puntos_reward' => 150,
                'imagen_url'    => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600',
                'paradas'       => [
                    ['nombre' => 'Tapas La Caña',     'orden' => 1],
                    ['nombre' => 'Café Central',      'orden' => 2],
                    ['nombre' => 'La Tasca Motrileña','orden' => 3],
                ],
            ],
            [
                'nombre'        => 'Ruta Gourmet Premium',
                'descripcion'   => 'Para los paladares más exigentes. Los restaurantes de mayor calificación en una única ruta exclusiva para miembros Premium.',
                'dificultad'    => 'dificil',
                'duracion_min'  => 180,
                'puntos_reward' => 300,
                'imagen_url'    => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600',
                'paradas'       => [
                    ['nombre' => 'Mariscos Don Juanito',  'orden' => 1],
                    ['nombre' => 'Restaurante Guadalfeo', 'orden' => 2],
                    ['nombre' => 'La Tasca Motrileña',    'orden' => 3],
                ],
            ],
            [
                'nombre'        => 'Ruta Dulce',
                'descripcion'   => 'Heladerías, pastelerías y cafeterías. El tour más dulce de Motril para disfrutar en familia o en pareja.',
                'dificultad'    => 'facil',
                'duracion_min'  => 60,
                'puntos_reward' => 75,
                'imagen_url'    => 'https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=600',
                'paradas'       => [
                    ['nombre' => 'Café Central',          'orden' => 1],
                    ['nombre' => 'Heladería Mediterráneo','orden' => 2],
                ],
            ],
        ];

        foreach ($rutas as $rutaData) {
            $paradas = $rutaData['paradas'];
            unset($rutaData['paradas']);

            $ruta = Ruta::firstOrCreate(['nombre' => $rutaData['nombre']], $rutaData);

            // Sincronizar paradas
            $sync = [];
            foreach ($paradas as $p) {
                $rest = Restaurante::where('nombre', $p['nombre'])->first();
                if ($rest) {
                    $sync[$rest->id] = ['orden' => $p['orden']];
                }
            }
            $ruta->restaurantes()->sync($sync);
        }

        // Progreso de María: completó 2 rutas
        $maria = User::where('email', 'maria@example.com')->first();
        $rutas = Ruta::all();

        ProgresoRuta::firstOrCreate(
            ['usuario_id' => $maria->id, 'ruta_id' => $rutas[0]->id],
            [
                'estado'              => 'completada',
                'paradas_completadas' => 3,
                'puntos_ganados'      => 100,
                'iniciada_en'         => now()->subDays(10),
                'completada_en'       => now()->subDays(9),
            ]
        );

        ProgresoRuta::firstOrCreate(
            ['usuario_id' => $maria->id, 'ruta_id' => $rutas[1]->id],
            [
                'estado'              => 'completada',
                'paradas_completadas' => 3,
                'puntos_ganados'      => 150,
                'iniciada_en'         => now()->subDays(5),
                'completada_en'       => now()->subDays(4),
            ]
        );

        ProgresoRuta::firstOrCreate(
            ['usuario_id' => $maria->id, 'ruta_id' => $rutas[2]->id],
            [
                'estado'              => 'iniciada',
                'paradas_completadas' => 1,
                'puntos_ganados'      => 0,
                'iniciada_en'         => now()->subDay(),
            ]
        );
    }
}
