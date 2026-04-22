<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPuntos extends Model {
    protected $table    = 'historial_puntos';
    protected $fillable = ['usuario_id','puntos','concepto','referencia'];
    protected function casts(): array { return ['puntos' => 'integer']; }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class,'usuario_id'); }
}
