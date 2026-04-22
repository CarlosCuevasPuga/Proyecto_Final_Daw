<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistroRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── POST /api/auth/registro ───────────────────────────────

    public function registro(RegistroRequest $request): JsonResponse
    {
        $usuario = User::create([
            'nombre'    => $request->nombre,
            'apellidos' => $request->apellidos,
            'email'     => strtolower($request->email),
            'password'  => Hash::make($request->password),
        ]);

        // Bonus de bienvenida
        $usuario->sumarPuntos(20, 'Bono de bienvenida');

        $token = $usuario->createToken('papm-api')->plainTextToken;

        return response()->json([
            'mensaje' => '¡Bienvenido/a a PAPM! Has recibido 20 puntos de bienvenida.',
            'token'   => $token,
            'usuario' => $usuario->fresh(),
        ], 201);
    }

    // ── POST /api/auth/login ──────────────────────────────────

    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', strtolower($request->email))
                       ->where('activo', true)
                       ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['error' => 'Credenciales incorrectas.'], 401);
        }

        // Revocar tokens anteriores para mantener solo uno activo
        $usuario->tokens()->delete();

        $token = $usuario->createToken('papm-api')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'usuario' => $usuario,
        ]);
    }

    // ── GET /api/auth/me ──────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        return response()->json(['usuario' => $request->user()]);
    }

    // ── POST /api/auth/logout ─────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        // Sanctum revoca el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada correctamente.']);
    }
}
