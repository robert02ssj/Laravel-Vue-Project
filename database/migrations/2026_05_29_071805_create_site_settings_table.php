<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración que crea la tabla 'site_settings' (configuración del sitio)
// Funciona como una tabla clave-valor: cada fila tiene un nombre y un valor.
// Ejemplo de fila: key='site_name', value='Sistema de Reservas', type='string', group='general'
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();         // nombre del ajuste, no puede repetirse
            $table->text('value')->nullable();        // valor del ajuste
            $table->string('type')->default('string'); // tipo: string, boolean, integer, color
            $table->string('group')->default('general'); // grupo: general, appearance, reservations
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
