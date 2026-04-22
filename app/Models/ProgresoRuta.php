<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoRuta extends Model
{
    protected $table = 'progreso_rutas';

    protected $fillable = [
        'usuario_id',
        'ruta_id',
        'estado',
        'paradas_completadas',
        'puntos_ganados',
        'iniciada_en',
        'completada_en',
    ];

    protected function casts(): array
    {
        return [
            'paradas_completadas' => 'integer',
            'puntos_ganados'      => 'integer',
            'iniciada_en'         => 'datetime',
            'completada_en'       => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
