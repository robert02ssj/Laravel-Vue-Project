<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

// Este controlador gestiona la configuración del sitio (nombre, colores, logo...).
// Cualquiera puede leer los ajustes. Solo el admin puede guardarlos.

class SiteSettingController extends Controller
{
    // Devolver todos los ajustes agrupados por categoría
    public function index()
    {
        // groupBy('group') agrupa los ajustes por su campo 'group' (general, appearance, reservations...)
        $ajustes = SiteSetting::all()->groupBy('group');

        return response()->json($ajustes);
    }

    // Devolver los ajustes de un grupo concreto
    // Ejemplo: GET /api/settings/appearance
    public function show(string $group)
    {
        // getGroup() devuelve un array clave => valor para ese grupo
        return response()->json(SiteSetting::getGroup($group));
    }

    // Guardar varios ajustes a la vez (solo admin)
    // Se envía un array de ajustes en el cuerpo de la petición
    public function update(Request $request)
    {
        $datos = $request->validate([
            'settings'         => 'required|array',
            'settings.*.key'   => 'required|string',   // cada ajuste debe tener una clave
            'settings.*.value' => 'present|nullable',  // el valor puede ser null
            'settings.*.type'  => 'sometimes|string',  // tipo: string, boolean, integer, color...
            'settings.*.group' => 'sometimes|string',  // grupo al que pertenece
        ]);

        // Guardamos o actualizamos cada ajuste
        foreach ($datos['settings'] as $ajuste) {
            SiteSetting::set(
                $ajuste['key'],
                $ajuste['value'],
                $ajuste['type']  ?? 'string',  // si no viene tipo, asumimos string
                $ajuste['group'] ?? 'general'  // si no viene grupo, asumimos general
            );
        }

        return response()->json(['message' => 'Ajustes guardados.']);
    }
}
