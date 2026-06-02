<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración que crea la tabla 'resources' (los recursos que se pueden reservar)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            // foreignId crea la columna category_id y la clave foránea automáticamente
            // constrained() la enlaza con la tabla 'categories'
            // restrictOnDelete() impide borrar una categoría si tiene recursos
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('name');                           // nombre del recurso (ej: Sala A)
            $table->string('slug')->unique();                 // versión URL del nombre
            $table->text('description')->nullable();          // descripción
            $table->string('location')->nullable();           // ubicación física
            $table->unsignedInteger('capacity')->default(1);  // capacidad máxima de personas
            $table->string('image')->nullable();              // ruta de la imagen
            $table->boolean('active')->default(true);         // si está disponible

            $table->time('open_time')->default('08:00:00');   // hora de apertura
            $table->time('close_time')->default('20:00:00');  // hora de cierre
            $table->unsignedInteger('slot_duration')->default(60); // duración de cada franja en minutos
            $table->decimal('price_per_slot', 8, 2)->default(0)->unsigned(); // precio por franja (0 = gratis)

            // available_days se guarda como JSON (array de números: 0=domingo, 1=lunes ... 6=sábado)
            $table->json('available_days')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
