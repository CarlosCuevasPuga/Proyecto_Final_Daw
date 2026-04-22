<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ──────────────────────────────────────────────
// CuponUsuario – Tabla pivote con datos extra
// ──────────────────────────────────────────────
class CuponUsuario extends Model {
    protected $table    = 'cupones_usuario';
    protected $fillable = ['usuario_id','cupon_id','usado','canjeado_en'];
    protected function casts(): array {
        return ['usado' => 'boolean', 'canjeado_en' => 'datetime'];
    }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class,'usuario_id'); }
    public function cupon():   BelongsTo { return $this->belongsTo(Cupon::class,'cupon_id'); }
}
