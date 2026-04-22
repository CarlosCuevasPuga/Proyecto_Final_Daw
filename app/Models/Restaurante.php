<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurante extends Model
{
    use HasFactory;

    protected $table = 'restaurantes';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'direccion',
        'latitud',
        'longitud',
        'telefono',
        'email',
        'web',
        'categoria',
        'precio_medio',
        'imagen_url',
        'valoracion',
        'num_valoraciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'latitud'          => 'float',
            'longitud'         => 'float',
            'valoracion'       => 'float',
            'num_valoraciones' => 'integer',
            'activo'           => 'boolean',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePrecio($query, string $precio)
    {
        return $query->where('precio_medio', $precio);
    }

    // ── Relaciones ───────────────────────────────────────────────

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function rutas(): BelongsToMany
    {
        return $this->belongsToMany(Ruta::class, 'ruta_restaurantes', 'restaurante_id', 'ruta_id')
                    ->withPivot('orden')
                    ->orderByPivot('orden');
    }

    public function cupones(): HasMany
    {
        return $this->hasMany(Cupon::class, 'restaurante_id');
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class, 'restaurante_id');
    }

    // ── Helper: recalcular valoración media ──────────────────────

    public function recalcularValoracion(): void
    {
        $stats = $this->valoraciones()->selectRaw('AVG(puntuacion) as media, COUNT(*) as total')->first();
        $this->update([
            'valoracion'       => round($stats->media ?? 0, 2),
            'num_valoraciones' => $stats->total ?? 0,
        ]);
    }
}
