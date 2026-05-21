<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id(); // Autoincremental
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('matricula');
            $table->decimal('promedio', 4, 2);
            $table->string('password');
            $table->string('fotoPerfilUrl')->nullable(); // Guardará la URL pública de S3
            $table->timestamps(); // Crea 'created_at' y 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
