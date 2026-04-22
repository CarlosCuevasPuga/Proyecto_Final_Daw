<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'dificultad',
        'duracion_min',
        'puntos_reward',
        'imagen_url',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'duracion_min'  => 'integer',
            'puntos_reward' => 'integer',
            'activa'        => 'boolean',
        ];
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    public function restaurantes(): BelongsToMany
    {
        return $this->belongsToMany(Restaurante::class, 'ruta_restaurantes', 'ruta_id', 'restaurante_id')
                    ->withPivot('orden')
                    ->orderByPivot('orden');
    }

    public function progresos(): HasMany
    {
        return $this->hasMany(ProgresoRuta::class, 'ruta_id');
    }
}
