<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('direccion', 300);
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('web', 500)->nullable();
            $table->enum('categoria', [
                'tapas','mariscos','cocina_local','internacional','cafeteria','heladeria','copas'
            ])->default('cocina_local');
            $table->enum('precio_medio', ['€','€€','€€€'])->default('€€');
            $table->string('imagen_url', 500)->nullable();
            $table->decimal('valoracion', 3, 2)->default(0.00);
            $table->unsignedInteger('num_valoraciones')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('categoria');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurantes');
    }
};
