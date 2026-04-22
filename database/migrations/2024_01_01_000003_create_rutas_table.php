<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ──────────────────────────────────────────────
// RUTAS
// ──────────────────────────────────────────────
return new class extends Migration {
    public function up(): void {
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('dificultad', ['facil','media','dificil'])->default('facil');
            $table->unsignedSmallInteger('duracion_min')->comment('Duración estimada en minutos');
            $table->unsignedInteger('puntos_reward')->default(50);
            $table->string('imagen_url', 500)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rutas'); }
};
