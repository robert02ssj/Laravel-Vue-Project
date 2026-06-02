<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;

// Este controlador gestiona los recursos reservables (salas, portátiles, pistas...).
// Cualquiera puede ver los recursos. Solo admin/gestor pueden crearlos, editarlos o borrarlos.

class ResourceController extends Controller
{
    // Listar todos los recursos activos
    public function index(Request $request)
    {
        // Empezamos la consulta: solo recursos activos, con su categoría incluida
        $query = Resource::with('category')->where('active', true);

        // Si el usuario ha filtrado por categoría (ej: ?category=salas), lo aplicamos
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        return response()->json($query->get());
    }

    // Ver un recurso concreto con su categoría
    public function show(Resource $resource)
    {
        return response()->json($resource->load('category'));
    }

    // Crear un recurso nuevo (solo admin/gestor)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'category_id'      => 'required|exists:categories,id', // la categoría debe existir
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'capacity'         => 'integer|min:1',           // número de personas que caben
            'active'           => 'boolean',
            'open_time'        => 'required|date_format:H:i', // hora de apertura (ej: 08:00)
            'close_time'       => 'required|date_format:H:i|after:open_time', // hora de cierre
            'slot_duration'    => 'integer|min:15',           // duración de cada franja en minutos
            'price_per_slot'   => 'numeric|min:0',            // precio por franja (0 = gratuito)
            'available_days'   => 'nullable|array',           // días disponibles (0=domingo, 6=sábado)
            'available_days.*' => 'integer|between:0,6',
        ]);

        return response()->json(Resource::create($datos), 201);
    }

    // Editar un recurso existente (solo admin/gestor)
    public function update(Request $request, Resource $resource)
    {
        $datos = $request->validate([
            'category_id'      => 'sometimes|exists:categories,id',
            'name'             => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'capacity'         => 'integer|min:1',
            'active'           => 'boolean',
            'open_time'        => 'sometimes|date_format:H:i',
            'close_time'       => 'sometimes|date_format:H:i',
            'slot_duration'    => 'integer|min:15',
            'price_per_slot'   => 'numeric|min:0',
            'available_days'   => 'nullable|array',
            'available_days.*' => 'integer|between:0,6',
        ]);

        $resource->update($datos);

        return response()->json($resource->load('category'));
    }

    // Borrar un recurso (solo admin/gestor)
    public function destroy(Resource $resource)
    {
        $resource->delete(); // SoftDelete: queda marcado como borrado, no se elimina de verdad

        return response()->json(null, 204);
    }

    // Ver las franjas horarias disponibles de un recurso en una fecha concreta
    // Ejemplo de petición: GET /api/resources/1/availability?date=2026-06-01
    public function availability(Request $request, Resource $resource)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $fecha = Carbon::parse($request->date);
        $franjas = [];

        // Hora de apertura y cierre del recurso en la fecha solicitada
        $apertura = Carbon::parse($fecha->toDateString() . ' ' . $resource->open_time);
        $cierre   = Carbon::parse($fecha->toDateString() . ' ' . $resource->close_time);

        // Recorremos el día franja a franja según la duración configurada
        $inicio = $apertura->copy();
        while ($inicio->lt($cierre)) {
            $fin = $inicio->copy()->addMinutes($resource->slot_duration);

            // Comprobamos si esa franja está libre usando el método del modelo
            $franjas[] = [
                'start'     => $inicio->toDateTimeString(),
                'end'       => $fin->toDateTimeString(),
                'available' => $resource->isAvailableAt($inicio, $fin),
            ];

            // Avanzamos al siguiente hueco
            $inicio = $fin;
        }

        return response()->json($franjas);
    }
}
