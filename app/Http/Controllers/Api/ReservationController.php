<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Reservation::with(['resource.category', 'user'])
            ->when(!$user->hasRole(['admin', 'manager']), fn ($q) => $q->where('user_id', $user->id))
            ->latest();

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_time'  => 'required|date|after:now',
            'end_time'    => 'required|date|after:start_time',
            'notes'       => 'nullable|string|max:500',
        ]);

        $resource = Resource::findOrFail($data['resource_id']);
        $start    = \Carbon\Carbon::parse($data['start_time']);
        $end      = \Carbon\Carbon::parse($data['end_time']);

        return DB::transaction(function () use ($data, $resource, $start, $end, $request) {
            Resource::lockForUpdate()->find($resource->id);

            if (!$resource->isAvailableAt($start, $end)) {
                return response()->json(['message' => 'El recurso no está disponible en ese horario.'], 422);
            }

            $minutes    = $start->diffInMinutes($end);
            $slots      = $resource->slot_duration > 0 ? $minutes / $resource->slot_duration : 1;
            $totalPrice = $slots * $resource->price_per_slot;

            $reservation = Reservation::create([
                'user_id'     => $request->user()->id,
                'resource_id' => $resource->id,
                'start_time'  => $start,
                'end_time'    => $end,
                'status'      => 'pending',
                'notes'       => $data['notes'] ?? null,
                'total_price' => $totalPrice,
            ]);

            $reservation->logAction('created', null, 'pending');

            return response()->json($reservation->load(['resource', 'user']), 201);
        });
    }

    public function show(Request $request, Reservation $reservation)
    {
        $this->authorizeAccess($request->user(), $reservation);
        return response()->json($reservation->load(['resource.category', 'user', 'logs.user']));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorizeAccess($request->user(), $reservation);

        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $from = $reservation->status;
        $reservation->update($data);
        $reservation->logAction('status_changed', $from, $data['status'], $data['notes'] ?? null);

        return response()->json($reservation->load('resource'));
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        $this->authorizeAccess($request->user(), $reservation);

        $from = $reservation->status;
        $reservation->update(['status' => 'cancelled']);
        $reservation->logAction('cancelled', $from, 'cancelled');

        return response()->json(null, 204);
    }

    public function calendar(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        $reservations = Reservation::with(['resource.category', 'user'])
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_time', [$request->start, $request->end])
            ->get()
            ->map(fn ($r) => [
                'id'    => $r->id,
                'title' => $r->resource->name,
                'start' => $r->start_time->toIso8601String(),
                'end'   => $r->end_time->toIso8601String(),
                'color' => optional($r->resource->category)->color ?? '#3B82F6',
                'extendedProps' => [
                    'status'   => $r->status,
                    'resource' => $r->resource->name,
                    'user'     => optional($r->user)->name ?? '',
                ],
            ]);

        return response()->json($reservations);
    }

    private function authorizeAccess($user, Reservation $reservation): void
    {
        if (!$user->hasRole(['admin', 'manager']) && $reservation->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }
}
