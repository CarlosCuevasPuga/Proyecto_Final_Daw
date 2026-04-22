<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suscripcion extends Model {
    protected $table    = 'suscripciones';
    protected $fillable = ['usuario_id','plan','precio','inicio','fin','estado','referencia_pago'];
    protected function casts(): array {
        return ['precio' => 'float', 'inicio' => 'date', 'fin' => 'date'];
    }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class,'usuario_id'); }
}
