<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgresoRuta;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    // ── GET /api/rutas ────────────────────────────────────────

    public function index(): JsonResponse
    {
        $rutas = Ruta::activa()
            ->withCount('restaurantes as num_paradas')
            ->get();

        return response()->json(['rutas' => $rutas]);
    }

    // ── GET /api/rutas/{ruta} ─────────────────────────────────

    public function show(Ruta $ruta): JsonResponse
    {
        if (!$ruta->activa) {
            return response()->json(['error' => 'Ruta no disponible.'], 404);
        }

        $ruta->loadCount('restaurantes as num_paradas');
        $ruta->load(['restaurantes' => fn($q) => $q->orderByPivot('orden')]);

        // Renombrar la relación a "paradas" para compatibilidad con el frontend
        $data            = $ruta->toArray();
        $data['paradas'] = $data['restaurantes'];
        unset($data['restaurantes']);

        return response()->json(['ruta' => $data]);
    }

    // ── POST /api/rutas/{ruta}/iniciar ────────────────────────

    public function iniciar(Request $request, Ruta $ruta): JsonResponse
    {
        if (!$ruta->activa) {
            return response()->json(['error' => 'Ruta no disponible.'], 404);
        }

        ProgresoRuta::firstOrCreate(
            ['usuario_id' => $request->user()->id, 'ruta_id' => $ruta->id],
            ['estado' => 'iniciada', 'iniciada_en' => now()]
        );

        return response()->json(['mensaje' => '¡Ruta iniciada! Sigue las paradas del mapa.']);
    }

    // ── POST /api/rutas/{ruta}/completar ──────────────────────

    public function completar(Request $request, Ruta $ruta): JsonResponse
    {
        if (!$ruta->activa) {
            return response()->json(['error' => 'Ruta no disponible.'], 404);
        }

        $usuario  = $request->user();
        $progreso = ProgresoRuta::where('usuario_id', $usuario->id)
                                ->where('ruta_id', $ruta->id)
                                ->first();

        if (!$progreso) {
            return response()->json(['error' => 'Debes iniciar la ruta antes de completarla.'], 400);
        }

        if ($progreso->estado === 'completada') {
            return response()->json(['error' => 'Ya completaste esta ruta anteriormente.'], 409);
        }

        $puntos = $ruta->puntos_reward;

        $progreso->update([
            'estado'              => 'completada',
            'paradas_completadas' => $ruta->restaurantes()->count(),
            'puntos_ganados'      => $puntos,
            'completada_en'       => now(),
        ]);

        $usuario->sumarPuntos($puntos, "Completaste la ruta: {$ruta->nombre}", "ruta_{$ruta->id}");

        return response()->json([
            'mensaje'        => "¡Enhorabuena! Has completado la ruta y ganado {$puntos} puntos.",
            'puntos_ganados' => $puntos,
        ]);
    }

    // ── GET /api/rutas/progreso ───────────────────────────────

    public function progreso(Request $request): JsonResponse
    {
        $progreso = ProgresoRuta::where('usuario_id', $request->user()->id)
            ->with(['ruta:id,nombre,imagen_url,puntos_reward'])
            ->latest('iniciada_en')
            ->get();

        return response()->json(['progreso' => $progreso]);
    }
}
