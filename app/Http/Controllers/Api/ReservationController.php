<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Este controlador gestiona las reservas.
// Un usuario normal solo puede ver y gestionar sus propias reservas.
// Admin y gestor pueden ver y gestionar todas.

class ReservationController extends Controller
{
    // Listar reservas
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Empezamos la consulta con los datos relacionados incluidos
        $query = Reservation::with(['resource.category', 'user'])->latest();

        // Si el usuario NO es admin ni gestor, solo ve sus propias reservas
        if (!$usuario->hasRole(['admin', 'manager'])) {
            $query->where('user_id', $usuario->id);
        }

        // Filtro por estado si se pasa ?status=pending|confirmed|cancelled
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Devolvemos los resultados paginados (20 por página)
        return response()->json($query->paginate(20));
    }

    // Crear una reserva nueva
    public function store(Request $request)
    {
        $datos = $request->validate([
            'resource_id' => 'required|exists:resources,id', // el recurso debe existir
            'start_time'  => 'required|date|after:now',       // no se puede reservar en el pasado
            'end_time'    => 'required|date|after:start_time', // la hora fin debe ser después del inicio
            'notes'       => 'nullable|string|max:500',
        ]);

        $recurso = Resource::findOrFail($datos['resource_id']);
        $inicio  = Carbon::parse($datos['start_time']);
        $fin     = Carbon::parse($datos['end_time']);

        // Usamos una transacción de base de datos para evitar reservas dobles en el mismo hueco.
        // Si dos personas intentan reservar a la vez, solo una lo conseguirá.
        return DB::transaction(function () use ($datos, $recurso, $inicio, $fin, $request) {

            // Bloqueamos el recurso para que nadie más pueda modificarlo hasta que terminemos
            Resource::lockForUpdate()->find($recurso->id);

            // Comprobamos si el recurso está libre en ese horario
            if (!$recurso->isAvailableAt($inicio, $fin)) {
                return response()->json(['message' => 'El recurso no está disponible en ese horario.'], 422);
            }

            // Calculamos el precio total según el número de franjas que ocupa la reserva
            $minutos     = $inicio->diffInMinutes($fin);
            $numFranjas  = $recurso->slot_duration > 0 ? $minutos / $recurso->slot_duration : 1;
            $precioTotal = $numFranjas * $recurso->price_per_slot;

            // Creamos la reserva
            $reserva = Reservation::create([
                'user_id'     => $request->user()->id,
                'resource_id' => $recurso->id,
                'start_time'  => $inicio,
                'end_time'    => $fin,
                'status'      => 'pending', // empieza como pendiente de confirmación
                'notes'       => $datos['notes'] ?? null,
                'total_price' => $precioTotal,
            ]);

            // Guardamos en el historial que se ha creado esta reserva
            $reserva->logAction('created', null, 'pending');

            return response()->json($reserva->load(['resource', 'user']), 201);
        });
    }

    // Ver el detalle de una reserva concreta
    public function show(Request $request, Reservation $reservation)
    {
        // Comprobamos que el usuario tiene permiso para ver esta reserva
        $this->comprobarAcceso($request->user(), $reservation);

        // Devolvemos la reserva con recurso, categoría, usuario e historial de cambios
        return response()->json($reservation->load(['resource.category', 'user', 'logs.user']));
    }

    // Actualizar el estado o las notas de una reserva
    public function update(Request $request, Reservation $reservation)
    {
        $this->comprobarAcceso($request->user(), $reservation);

        $datos = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled', // solo estos tres estados son válidos
            'notes'  => 'nullable|string|max:500',
        ]);

        $estadoAnterior = $reservation->status;
        $reservation->update($datos);

        // Guardamos en el historial el cambio de estado
        $reservation->logAction('status_changed', $estadoAnterior, $datos['status'], $datos['notes'] ?? null);

        return response()->json($reservation->load('resource'));
    }

    // Cancelar (eliminar) una reserva
    public function destroy(Request $request, Reservation $reservation)
    {
        $this->comprobarAcceso($request->user(), $reservation);

        $estadoAnterior = $reservation->status;

        // No borramos la reserva de la base de datos, la marcamos como cancelada
        $reservation->update(['status' => 'cancelled']);
        $reservation->logAction('cancelled', $estadoAnterior, 'cancelled');

        return response()->json(null, 204);
    }

    // Devolver las reservas en formato de eventos para el calendario (FullCalendar)
    // Ejemplo: GET /api/reservations/calendar?start=2026-06-01&end=2026-06-30
    public function calendar(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        // Buscamos las reservas que caen en el rango de fechas y no están canceladas
        $reservas = Reservation::with(['resource.category', 'user'])
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_time', [$request->start, $request->end])
            ->get();

        // Transformamos cada reserva al formato que espera FullCalendar
        $eventos = $reservas->map(function ($reserva) {
            return [
                'id'    => $reserva->id,
                'title' => $reserva->resource->name,
                'start' => $reserva->start_time->toIso8601String(),
                'end'   => $reserva->end_time->toIso8601String(),
                'color' => optional($reserva->resource->category)->color ?? '#3B82F6',
                'extendedProps' => [
                    'status'   => $reserva->status,
                    'resource' => $reserva->resource->name,
                    'user'     => optional($reserva->user)->name ?? '',
                ],
            ];
        });

        return response()->json($eventos);
    }

    // Método privado: comprueba que el usuario puede acceder a esta reserva
    // Admin y gestor pueden acceder a cualquiera. Un usuario normal solo a las suyas.
    private function comprobarAcceso($usuario, Reservation $reserva): void
    {
        if (!$usuario->hasRole(['admin', 'manager']) && $reserva->user_id !== $usuario->id) {
            abort(403, 'No autorizado.');
        }
    }
}
