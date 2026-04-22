<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'nombre'      => 'Admin',
                'apellidos'   => 'PAPM',
                'email'       => 'admin@papmotril.com',
                'password'    => Hash::make('password123'),
                'rol'         => 'admin',
                'suscripcion' => 'premium',
                'puntos'      => 9999,
            ],
            [
                'nombre'      => 'María',
                'apellidos'   => 'García López',
                'email'       => 'maria@example.com',
                'password'    => Hash::make('password123'),
                'rol'         => 'turista',
                'suscripcion' => 'premium',
                'puntos'      => 350,
            ],
            [
                'nombre'      => 'Carlos',
                'apellidos'   => 'Fernández Ruiz',
                'email'       => 'carlos@example.com',
                'password'    => Hash::make('password123'),
                'rol'         => 'turista',
                'suscripcion' => 'gratis',
                'puntos'      => 120,
            ],
            [
                'nombre'      => 'Restaurante',
                'apellidos'   => 'El Puerto',
                'email'       => 'elpuerto@example.com',
                'password'    => Hash::make('password123'),
                'rol'         => 'restaurante',
                'suscripcion' => 'premium',
                'puntos'      => 0,
            ],
        ];

        foreach ($usuarios as $data) {
            User::firstOrCreate(['email' => $data['email']], $data);
        }

        // Historial de puntos para María
        $maria = User::where('email', 'maria@example.com')->first();
        $maria->historialPuntos()->createMany([
            ['puntos' =>  100, 'concepto' => 'Completaste la Ruta del Puerto',           'referencia' => 'ruta_1'],
            ['puntos' =>  150, 'concepto' => 'Completaste la Ruta del Centro Histórico', 'referencia' => 'ruta_2'],
            ['puntos' =>  100, 'concepto' => 'Bono de bienvenida Premium',               'referencia' => null],
            ['puntos' =>  -50, 'concepto' => 'Canje de cupón PUERTO10',                  'referencia' => 'cupon_1'],
            ['puntos' =>   50, 'concepto' => 'Valoración de restaurante',                'referencia' => 'restaurante_1'],
        ]);
    }
}
