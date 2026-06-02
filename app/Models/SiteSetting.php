<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// Modelo para guardar la configuración del sitio en base de datos.
// Funciona como una tabla clave-valor: cada fila tiene un 'key' y un 'value'.
// Ejemplos de ajustes: site_name, primary_color, allow_registration...

class SiteSetting extends Model
{
    // Campos que se pueden rellenar masivamente
    protected $fillable = [
        'key',   // nombre del ajuste (ej: 'site_name')
        'value', // valor del ajuste (ej: 'Sistema de Reservas')
        'type',  // tipo de dato: string, boolean, integer, color
        'group', // grupo al que pertenece: general, appearance, reservations
    ];

    // Obtener el valor de un ajuste concreto por su clave
    // Usa caché para no consultar la base de datos cada vez
    public static function get(string $clave, mixed $porDefecto = null): mixed
    {
        // Cache::rememberForever guarda el resultado indefinidamente hasta que se borre
        return Cache::rememberForever("setting:{$clave}", function () use ($clave, $porDefecto) {
            $ajuste = static::where('key', $clave)->first();
            return $ajuste ? $ajuste->value : $porDefecto;
        });
    }

    // Guardar o actualizar un ajuste
    public static function set(string $clave, mixed $valor, string $tipo = 'string', string $grupo = 'general'): void
    {
        // updateOrCreate: si existe el ajuste lo actualiza, si no lo crea
        static::updateOrCreate(
            ['key' => $clave],
            ['value' => $valor, 'type' => $tipo, 'group' => $grupo]
        );

        // Borramos la caché de este ajuste para que la próxima lectura coja el valor nuevo
        Cache::forget("setting:{$clave}");
    }

    // Obtener todos los ajustes de un grupo como array clave => valor
    // Ejemplo: SiteSetting::getGroup('appearance') → ['primary_color' => '#3B82F6', ...]
    public static function getGroup(string $grupo): array
    {
        return static::where('group', $grupo)->pluck('value', 'key')->toArray();
    }
}
