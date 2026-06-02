<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración que crea la tabla 'categories' (categorías de recursos)
return new class extends Migration
{
    // up() se ejecuta al hacer: sail artisan migrate
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();                              // columna id autoincremental (clave primaria)
            $table->string('name');                    // nombre de la categoría
            $table->string('slug')->unique();          // versión URL del nombre, sin repetición
            $table->text('description')->nullable();   // descripción (puede estar vacía)
            $table->string('icon')->nullable();        // icono (puede estar vacío)
            $table->string('color', 7)->default('#3B82F6'); // color hex, por defecto azul
            $table->boolean('active')->default(true);  // si está activa (true por defecto)
            $table->timestamps();                      // created_at y updated_at automáticos
            $table->softDeletes();                     // columna deleted_at para borrado suave
        });
    }

    // down() se ejecuta al hacer: sail artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('categories'); // elimina la tabla si existe
    }
};
