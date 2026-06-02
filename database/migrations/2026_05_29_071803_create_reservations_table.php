<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración que crea la tabla 'reservations' (reservas de los usuarios)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Clave foránea al usuario que hace la reserva
            // restrictOnDelete() impide borrar un usuario si tiene reservas
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            // Clave foránea al recurso que se reserva
            $table->foreignId('resource_id')->constrained()->restrictOnDelete();

            $table->dateTime('start_time');  // fecha y hora de inicio de la reserva
            $table->dateTime('end_time');    // fecha y hora de fin de la reserva

            // Estado de la reserva: solo puede ser uno de estos tres valores
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');

            $table->text('notes')->nullable();                              // notas opcionales
            $table->decimal('total_price', 8, 2)->default(0)->unsigned();  // precio total calculado
            $table->string('payment_status')->default('unpaid');            // estado del pago
            $table->string('payment_reference')->nullable();                // referencia de pago

            $table->timestamps();
            $table->softDeletes(); // deleted_at para borrado suave
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
