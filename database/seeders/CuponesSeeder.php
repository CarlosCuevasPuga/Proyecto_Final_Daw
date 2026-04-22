<?php

namespace Database\Seeders;

use App\Models\Cupon;
use App\Models\CuponUsuario;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Database\Seeder;

class CuponesSeeder extends Seeder
{
    public function run(): void
    {
        $cupones = [
            [
                'restaurante'      => 'El Puerto de Motril',
                'codigo'           => 'PUERTO10',
                'descripcion'      => '10% de descuento en tu consumición en El Puerto de Motril',
                'descuento_pct'    => 10,
                'puntos_necesarios'=> 50,
                'max_usos'         => 200,
                'solo_premium'     => false,
                'valido_desde'     => '2025-01-01',
                'valido_hasta'     => '2025-12-31',
            ],
            [
                'restaurante'      => 'Tapas La Caña',
                'codigo'           => 'CANA2X1',
                'descripcion'      => '2x1 en raciones de tapas en La Caña (martes y miércoles)',
                'descuento_pct'    => null,
                'puntos_necesarios'=> 80,
                'max_usos'         => 100,
                'solo_premium'     => false,
                'valido_desde'     => '2025-01-01',
                'valido_hasta'     => '2025-12-31',
            ],
            [
                'restaurante'      => 'Mariscos Don Juanito',
                'codigo'           => 'JUANITO15',
                'descripcion'      => '15% dto en Mariscos Don Juanito (mín. 2 personas)',
                'descuento_pct'    => 15,
                'puntos_necesarios'=> 120,
                'max_usos'         => 50,
                'solo_premium'     => true,
                'valido_desde'     => '2025-01-01',
                'valido_hasta'     => '2025-12-31',
            ],
            [
                'restaurante'      => 'Restaurante Guadalfeo',
                'codigo'           => 'GUADALFEO20',
                'descripcion'      => '20% dto para miembros Premium en Restaurante Guadalfeo',
                'descuento_pct'    => 20,
                'puntos_necesarios'=> 200,
                'max_usos'         => 30,
                'solo_premium'     => true,
                'valido_desde'     => '2025-01-01',
                'valido_hasta'     => '2025-12-31',
            ],
            [
                'restaurante'      => 'Heladería Mediterráneo',
                'codigo'           => 'HELADO1',
                'descripcion'      => 'Helado gratis al completar la Ruta Dulce',
                'descuento_pct'    => null,
                'puntos_necesarios'=> 0,
                'max_usos'         => 300,
                'solo_premium'     => false,
                'valido_desde'     => '2025-01-01',
                'valido_hasta'     => '2025-12-31',
            ],
        ];

        foreach ($cupones as $data) {
            $restaurante = Restaurante::where('nombre', $data['restaurante'])->first();
            if (!$restaurante) continue;

            unset($data['restaurante']);
            $data['restaurante_id'] = $restaurante->id;

            Cupon::firstOrCreate(['codigo' => $data['codigo']], $data);
        }

        // María ya canjeó PUERTO10
        $maria  = User::where('email', 'maria@example.com')->first();
        $cupon1 = Cupon::where('codigo', 'PUERTO10')->first();

        if ($maria && $cupon1) {
            CuponUsuario::firstOrCreate(
                ['usuario_id' => $maria->id, 'cupon_id' => $cupon1->id],
                ['usado' => true]
            );
            $cupon1->increment('usos_actuales');
        }
    }
}
