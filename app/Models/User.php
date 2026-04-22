<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password',
        'rol',
        'suscripcion',
        'puntos',
        'avatar_url',
        'activo',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // bcrypt automático al asignar
            'puntos'            => 'integer',
            'activo'            => 'boolean',
        ];
    }

    // ── Helpers de rol / suscripción ────────────────────────────

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esPremium(): bool
    {
        return $this->suscripcion === 'premium';
    }

    // ── Relaciones ───────────────────────────────────────────────

    public function progresoRutas(): HasMany
    {
        return $this->hasMany(ProgresoRuta::class, 'usuario_id');
    }

    public function cuponesUsuario(): HasMany
    {
        return $this->hasMany(CuponUsuario::class, 'usuario_id');
    }

    public function historialPuntos(): HasMany
    {
        return $this->hasMany(HistorialPuntos::class, 'usuario_id');
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class, 'usuario_id');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'usuario_id');
    }

    // ── Helper: añadir puntos con registro en historial ──────────

    public function sumarPuntos(int $cantidad, string $concepto, ?string $referencia = null): void
    {
        $this->increment('puntos', $cantidad);

        $this->historialPuntos()->create([
            'puntos'     => $cantidad,
            'concepto'   => $concepto,
            'referencia' => $referencia,
        ]);
    }

    public function restarPuntos(int $cantidad, string $concepto, ?string $referencia = null): void
    {
        $this->decrement('puntos', $cantidad);

        $this->historialPuntos()->create([
            'puntos'     => -$cantidad,
            'concepto'   => $concepto,
            'referencia' => $referencia,
        ]);
    }
}
