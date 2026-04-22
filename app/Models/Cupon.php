<?php
// ──────────────────────────────────────────────
// app/Models/Cupon.php
// ──────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cupon extends Model
{
    use HasFactory;
    protected $table = 'cupones';
    protected $fillable = [
        'restaurante_id','codigo','descripcion','descuento_pct',
        'descuento_euros','puntos_necesarios','max_usos','usos_actuales',
        'solo_premium','valido_desde','valido_hasta','activo',
    ];
    protected function casts(): array {
        return [
            'descuento_pct'     => 'integer',
            'descuento_euros'   => 'float',
            'puntos_necesarios' => 'integer',
            'max_usos'          => 'integer',
            'usos_actuales'     => 'integer',
            'solo_premium'      => 'boolean',
            'activo'            => 'boolean',
            'valido_desde'      => 'date',
            'valido_hasta'      => 'date',
        ];
    }

    public function scopeActivo($query) {
        return $query->where('activo', true)
                     ->where('valido_hasta', '>=', now()->toDateString())
                     ->whereColumn('usos_actuales', '<', 'max_usos');
    }

    public function restaurante(): BelongsTo {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }
    public function usuarios(): HasMany {
        return $this->hasMany(CuponUsuario::class, 'cupon_id');
    }
}
