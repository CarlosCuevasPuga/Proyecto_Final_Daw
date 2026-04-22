<?php

return [

    /*
     * Rutas a las que se aplica CORS.
     * 'api/*' cubre todos los endpoints de la API REST.
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * En producción, reemplazar '*' por el dominio real del frontend.
     * Ej: ['https://papmotril.com']
     */
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5500')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Necesario para Sanctum (envío de cookies/credenciales).
     */
    'supports_credentials' => true,

];
