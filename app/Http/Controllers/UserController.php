<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ActualizarPerfilRequest;
use App\Http\Requests\User\SuscripcionRequest;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ── GET /api/usuarios/perfil ──────────────────────────────

    public function perfil(Request $request): JsonResponse
    {
        $usuario = $request->user()->load([
            'historialPuntos' => fn($q) => $q->latest()->limit(20),
            'progresoRutas.ruta:id,nombre,imagen_url,puntos_reward',
        ]);

        // Separar rutas completadas
        $usuario->rutas_completadas = $usuario->progresoRutas
            ->where('estado', 'completada')
            ->values();

        return response()->json(['usuario' => $usuario]);
    }

    // ── PUT /api/usuarios/perfil ──────────────────────────────

    public function actualizarPerfil(ActualizarPerfilRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $datos   = $request->only(['nombre', 'apellidos']);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        if (empty($datos)) {
            return response()->json(['error' => 'No hay datos que actualizar.'], 400);
        }

        $usuario->update($datos);

        return response()->json([
            'mensaje' => 'Perfil actualizado correctamente.',
            'usuario' => $usuario->fresh(),
        ]);
    }

    // ── GET /api/usuarios/ranking ─────────────────────────────

    public function ranking(): JsonResponse
    {
        $ranking = User::where('rol', 'turista')
            ->where('activo', true)
            ->orderByDesc('puntos')
            ->limit(10)
            ->get(['nombre', 'apellidos', 'puntos', 'suscripcion']);

        return response()->json(['ranking' => $ranking]);
    }

    // ── POST /api/usuarios/suscripcion ────────────────────────

    public function suscribirse(SuscripcionRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $plan    = $request->plan;

        $precio = $plan === 'premium_anual'
            ? (float) env('PRECIO_PREMIUM_ANUAL', 39.99)
            : (float) env('PRECIO_PREMIUM_MENSUAL', 4.99);

        $inicio = now()->toDateString();
        $fin    = $plan === 'premium_anual'
            ? now()->addYear()->toDateString()
            : now()->addMonth()->toDateString();

        // Registrar suscripción
        Suscripcion::create([
            'usuario_id' => $usuario->id,
            'plan'       => $plan,
            'precio'     => $precio,
            'inicio'     => $inicio,
            'fin'        => $fin,
        ]);

        // Actualizar estado del usuario
        $usuario->update(['suscripcion' => 'premium']);

        // Bonus de puntos
        $bonus = $plan === 'premium_anual' ? 500 : 100;
        $usuario->sumarPuntos($bonus, "Bonus por suscripción $plan");

        return response()->json([
            'mensaje' => "¡Bienvenido/a a Premium! Has ganado {$bonus} puntos extra.",
            'plan'    => $plan,
            'fin'     => $fin,
        ], 201);
    }
}
