<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que guarda el historial de cambios de una reserva.
// Cada vez que una reserva cambia de estado, se crea un registro aquí.

class ReservationLog extends Model
{
    // Campos que se pueden rellenar masivamente
    protected $fillable = [
        'reservation_id', // a qué reserva pertenece este log
        'user_id',        // quién hizo el cambio
        'action',         // qué acción se realizó (created, status_changed, cancelled...)
        'from_status',    // estado anterior
        'to_status',      // estado nuevo
        'notes',          // notas opcionales
        'ip_address',     // IP desde donde se hizo el cambio
    ];

    // Relación: este log pertenece a una reserva
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relación: este log fue creado por un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
