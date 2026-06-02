<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración que crea la tabla 'reservation_logs' (historial de cambios de las reservas)
// Cada vez que una reserva cambia de estado, se guarda un registro aquí.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_logs', function (Blueprint $table) {
            $table->id();

            // Si se borra la reserva, se borran también sus logs (cascadeOnDelete)
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            // El usuario que hizo el cambio. Si se borra el usuario, este campo queda a null
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action');  // qué acción se realizó: created, status_changed, cancelled

            // Estado anterior y nuevo (pueden ser null si es la primera vez)
            $table->enum('from_status', ['pending', 'confirmed', 'cancelled'])->nullable();
            $table->enum('to_status',   ['pending', 'confirmed', 'cancelled'])->nullable();

            $table->text('notes')->nullable();        // notas opcionales
            $table->string('ip_address', 45)->nullable(); // IP del usuario (45 chars para IPv6)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_logs');
    }
};
