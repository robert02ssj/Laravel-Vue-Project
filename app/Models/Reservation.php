<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo que representa una reserva.
// Una reserva conecta un usuario con un recurso en un periodo de tiempo.

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes; // borrado suave

    // Campos que se pueden rellenar masivamente
    protected $fillable = [
        'user_id',           // quién ha hecho la reserva
        'resource_id',       // qué recurso se ha reservado
        'start_time',        // cuándo empieza
        'end_time',          // cuándo termina
        'status',            // estado: pending, confirmed o cancelled
        'notes',             // notas adicionales
        'total_price',       // precio total calculado
        'payment_status',    // estado del pago: unpaid, paid...
        'payment_reference', // referencia del pago si la hay
    ];

    // Casts: conversiones automáticas de tipos
    protected $casts = [
        'start_time'  => 'datetime',  // se convierte a objeto Carbon (fecha con hora)
        'end_time'    => 'datetime',
        'total_price' => 'decimal:2', // con 2 decimales
    ];

    // Relación: una reserva pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: una reserva pertenece a un recurso
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    // Relación: una reserva puede tener muchos registros de historial
    public function logs()
    {
        return $this->hasMany(ReservationLog::class);
    }

    // Guarda un registro en el historial de cambios de esta reserva
    public function logAction(string $accion, ?string $estadoAnterior, ?string $estadoNuevo, ?string $notas = null): void
    {
        $this->logs()->create([
            'user_id'     => auth()->id(),   // quién ha hecho el cambio
            'action'      => $accion,         // qué se ha hecho (created, status_changed, cancelled...)
            'from_status' => $estadoAnterior, // estado antes del cambio
            'to_status'   => $estadoNuevo,    // estado después del cambio
            'notes'       => $notas,
            'ip_address'  => request()->ip(), // IP desde la que se hizo el cambio
        ]);
    }
}
