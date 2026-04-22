<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurante\ValoracionRequest;
use App\Models\Restaurante;
use App\Models\Valoracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    // ── GET /api/restaurantes ─────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Restaurante::activo();

        if ($request->filled('categoria')) {
            $query->categoria($request->categoria);
        }

        if ($request->filled('precio')) {
            $query->precio($request->precio);
        }

        // Ordenamiento
        $orden = $request->get('orden', 'valoracion');
        match ($orden) {
            'nombre'    => $query->orderBy('nombre'),
            default     => $query->orderByDesc('valoracion'),
        };

        return response()->json([
            'restaurantes' => $query->get(),
        ]);
    }

    // ── GET /api/restaurantes/{restaurante} ───────────────────

    public function show(Restaurante $restaurante): JsonResponse
    {
        if (!$restaurante->activo) {
            return response()->json(['error' => 'Restaurante no disponible.'], 404);
        }

        // Cargar valoraciones recientes con nombre del usuario
        $restaurante->load([
            'valoraciones' => fn($q) => $q->with('usuario:id,nombre')
                                          ->latest()
                                          ->limit(10),
            'cupones'      => fn($q) => $q->activo()
                                          ->select('id','codigo','descripcion','descuento_pct',
                                                   'puntos_necesarios','solo_premium','valido_hasta'),
        ]);

        return response()->json(['restaurante' => $restaurante]);
    }

    // ── POST /api/restaurantes/{restaurante}/valorar ──────────

    public function valorar(ValoracionRequest $request, Restaurante $restaurante): JsonResponse
    {
        $usuario = $request->user();

        // Upsert: una valoración por usuario/restaurante
        Valoracion::updateOrCreate(
            [
                'usuario_id'     => $usuario->id,
                'restaurante_id' => $restaurante->id,
            ],
            [
                'puntuacion'  => $request->puntuacion,
                'comentario'  => $request->comentario,
            ]
        );

        // Recalcular media
        $restaurante->recalcularValoracion();

        // +10 puntos al usuario
        $usuario->sumarPuntos(10, "Valoración de {$restaurante->nombre}", "restaurante_{$restaurante->id}");

        return response()->json(['mensaje' => "¡Gracias por tu valoración! +10 puntos."]);
    }
}
