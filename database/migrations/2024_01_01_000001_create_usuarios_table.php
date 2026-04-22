<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellidos', 150);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->unsignedInteger('puntos')->default(0);
            $table->enum('rol', ['turista', 'admin', 'restaurante'])->default('turista');
            $table->enum('suscripcion', ['gratis', 'premium'])->default('gratis');
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
