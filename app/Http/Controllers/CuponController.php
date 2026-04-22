<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\CuponUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    // ── GET /api/cupones ──────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $usuario   = $request->user();
        $esPremium = $usuario->esPremium();

        $query = Cupon::activo()
            ->with('restaurante:id,nombre,imagen_url')
            ->orderBy('valido_hasta');

        if (!$esPremium) {
            $query->where('solo_premium', false);
        }

        $cupones = $query->get();

        // Marcar los ya canjeados por el usuario
        $idsCanjeados = CuponUsuario::where('usuario_id', $usuario->id)
            ->pluck('cupon_id')
            ->toArray();

        $cupones->each(function ($cupon) use ($idsCanjeados) {
            $cupon->canjeado           = in_array($cupon->id, $idsCanjeados, true);
            $cupon->restaurante_nombre = $cupon->restaurante?->nombre;
            $cupon->restaurante_imagen = $cupon->restaurante?->imagen_url;
        });

        return response()->json(['cupones' => $cupones]);
    }

    // ── POST /api/cupones/{cupon}/canjear ─────────────────────

    public function canjear(Request $request, Cupon $cupon): JsonResponse
    {
        $usuario = $request->user();

        // Cupon activo y vigente
        if (!$cupon->activo || $cupon->valido_hasta->isPast() || $cupon->usos_actuales >= $cupon->max_usos) {
            return response()->json(['error' => 'Cupón no válido o expirado.'], 404);
        }

        // Exclusivo premium
        if ($cupon->solo_premium && !$usuario->esPremium()) {
            return response()->json(['error' => 'Este cupón es exclusivo para miembros Premium.'], 403);
        }

        // Comprobación de canje previo
        $yaCanjeado = CuponUsuario::where('usuario_id', $usuario->id)
                                  ->where('cupon_id', $cupon->id)
                                  ->exists();

        if ($yaCanjeado) {
            return response()->json(['error' => 'Ya canjeaste este cupón.'], 409);
        }

        // Puntos suficientes
        if ($usuario->puntos < $cupon->puntos_necesarios) {
            return response()->json([
                'error'             => 'No tienes suficientes puntos para canjear este cupón.',
                'puntos_actuales'   => $usuario->puntos,
                'puntos_necesarios' => $cupon->puntos_necesarios,
            ], 400);
        }

        // Registrar canje
        CuponUsuario::create([
            'usuario_id'  => $usuario->id,
            'cupon_id'    => $cupon->id,
            'canjeado_en' => now(),
        ]);

        $cupon->increment('usos_actuales');

        // Descontar puntos si el cupón los requiere
        if ($cupon->puntos_necesarios > 0) {
            $usuario->restarPuntos(
                $cupon->puntos_necesarios,
                "Canje de cupón: {$cupon->codigo}",
                "cupon_{$cupon->id}"
            );
        }

        return response()->json([
            'mensaje' => "¡Cupón {$cupon->codigo} canjeado correctamente! Muéstralo en el restaurante.",
            'cupon'   => $cupon,
        ]);
    }

    // ── GET /api/cupones/mis-cupones ──────────────────────────

    public function misCupones(Request $request): JsonResponse
    {
        $cupones = CuponUsuario::where('usuario_id', $request->user()->id)
            ->with([
                'cupon',
                'cupon.restaurante:id,nombre',
            ])
            ->latest('canjeado_en')
            ->get()
            ->map(function ($cu) {
                return array_merge($cu->cupon->toArray(), [
                    'canjeado_en'        => $cu->canjeado_en,
                    'usado'              => $cu->usado,
                    'restaurante_nombre' => $cu->cupon->restaurante?->nombre,
                ]);
            });

        return response()->json(['cupones' => $cupones]);
    }
}
