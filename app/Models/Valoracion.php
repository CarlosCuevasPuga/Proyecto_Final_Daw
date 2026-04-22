<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Valoracion extends Model {
    protected $table    = 'valoraciones';
    protected $fillable = ['usuario_id','restaurante_id','puntuacion','comentario'];
    protected function casts(): array {
        return ['puntuacion' => 'integer'];
    }
    public function usuario():     BelongsTo { return $this->belongsTo(User::class,'usuario_id'); }
    public function restaurante(): BelongsTo { return $this->belongsTo(Restaurante::class,'restaurante_id'); }
}
