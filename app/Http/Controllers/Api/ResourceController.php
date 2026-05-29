<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::with('category')->where('active', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'capacity'         => 'integer|min:1',
            'active'           => 'boolean',
            'open_time'        => 'required|date_format:H:i',
            'close_time'       => 'required|date_format:H:i|after:open_time',
            'slot_duration'    => 'integer|min:15',
            'price_per_slot'   => 'numeric|min:0',
            'available_days'   => 'nullable|array',
            'available_days.*' => 'integer|between:0,6',
        ]);

        return response()->json(Resource::create($data), 201);
    }

    public function show(Resource $resource)
    {
        return response()->json($resource->load('category'));
    }

    public function update(Request $request, Resource $resource)
    {
        $data = $request->validate([
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

        $resource->update($data);
        return response()->json($resource->load('category'));
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();
        return response()->json(null, 204);
    }

    public function availability(Request $request, Resource $resource)
    {
        $request->validate(['date' => 'required|date']);

        $date  = \Carbon\Carbon::parse($request->date);
        $slots = [];
        $open  = \Carbon\Carbon::parse($date->toDateString() . ' ' . $resource->open_time);
        $close = \Carbon\Carbon::parse($date->toDateString() . ' ' . $resource->close_time);

        while ($open->lt($close)) {
            $slotEnd = $open->copy()->addMinutes($resource->slot_duration);
            $slots[] = [
                'start'     => $open->toDateTimeString(),
                'end'       => $slotEnd->toDateTimeString(),
                'available' => $resource->isAvailableAt($open, $slotEnd),
            ];
            $open = $slotEnd;
        }

        return response()->json($slots);
    }
}
