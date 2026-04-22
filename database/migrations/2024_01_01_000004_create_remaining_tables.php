<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // ── Tabla pivote: ruta_restaurantes ──────────────────────
        Schema::create('ruta_restaurantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->cascadeOnDelete();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->unsignedTinyInteger('orden')->default(1);
            $table->unique(['ruta_id','restaurante_id']);
        });

        // ── Progreso de rutas por usuario ────────────────────────
        Schema::create('progreso_rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('ruta_id')->constrained('rutas')->cascadeOnDelete();
            $table->enum('estado', ['iniciada','en_progreso','completada'])->default('iniciada');
            $table->unsignedInteger('paradas_completadas')->default(0);
            $table->unsignedInteger('puntos_ganados')->default(0);
            $table->timestamp('iniciada_en')->useCurrent();
            $table->timestamp('completada_en')->nullable();
            $table->timestamps();
            $table->unique(['usuario_id','ruta_id']);
        });

        // ── Cupones ───────────────────────────────────────────────
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 300);
            $table->unsignedTinyInteger('descuento_pct')->nullable();
            $table->decimal('descuento_euros', 6, 2)->nullable();
            $table->unsignedInteger('puntos_necesarios')->default(0);
            $table->unsignedInteger('max_usos')->default(100);
            $table->unsignedInteger('usos_actuales')->default(0);
            $table->boolean('solo_premium')->default(false);
            $table->date('valido_desde');
            $table->date('valido_hasta');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['activo','valido_hasta']);
        });

        // ── Cupones canjeados por usuario ─────────────────────────
        Schema::create('cupones_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('cupon_id')->constrained('cupones')->cascadeOnDelete();
            $table->boolean('usado')->default(false);
            $table->timestamp('canjeado_en')->useCurrent();
            $table->timestamps();
            $table->unique(['usuario_id','cupon_id']);
        });

        // ── Historial de puntos ───────────────────────────────────
        Schema::create('historial_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->integer('puntos')->comment('Positivo: ganados. Negativo: gastados.');
            $table->string('concepto', 255);
            $table->string('referencia', 100)->nullable();
            $table->timestamps();
            $table->index('usuario_id');
        });

        // ── Valoraciones ──────────────────────────────────────────
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->unsignedTinyInteger('puntuacion');
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['usuario_id','restaurante_id']);
        });

        // ── Suscripciones Premium ─────────────────────────────────
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->enum('plan', ['premium_mensual','premium_anual']);
            $table->decimal('precio', 6, 2);
            $table->date('inicio');
            $table->date('fin');
            $table->enum('estado', ['activa','cancelada','expirada'])->default('activa');
            $table->string('referencia_pago', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('suscripciones');
        Schema::dropIfExists('valoraciones');
        Schema::dropIfExists('historial_puntos');
        Schema::dropIfExists('cupones_usuario');
        Schema::dropIfExists('cupones');
        Schema::dropIfExists('progreso_rutas');
        Schema::dropIfExists('ruta_restaurantes');
    }
};
