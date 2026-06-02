<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// Modelo que representa un recurso reservable.
// Ejemplos: Sala A, Portátil 1, Pista de Pádel.

class Resource extends Model
{
    use HasFactory;
    use SoftDeletes; // borrado suave: no elimina el registro, guarda la fecha en deleted_at

    // Campos que se pueden rellenar masivamente
    protected $fillable = [
        'category_id',   // a qué categoría pertenece
        'name',          // nombre del recurso
        'slug',          // versión del nombre para URLs
        'description',   // descripción
        'location',      // ubicación física (ej: Planta 1 - Ala Norte)
        'capacity',      // número máximo de personas
        'image',         // ruta de la imagen
        'active',        // si está disponible para reservar
        'open_time',     // hora de apertura (ej: 08:00)
        'close_time',    // hora de cierre (ej: 20:00)
        'slot_duration', // duración de cada franja en minutos (ej: 60)
        'price_per_slot',// precio por franja en euros (0 = gratuito)
        'available_days',// días de la semana disponibles (array: 0=domingo, 6=sábado)
    ];

    // Casts: conversiones automáticas de tipos
    protected $casts = [
        'active'          => 'boolean',     // true/false
        'available_days'  => 'array',       // se guarda como JSON en BD y llega como array PHP
        'price_per_slot'  => 'decimal:2',   // con 2 decimales
    ];

    // Se ejecuta automáticamente antes de crear un recurso
    protected static function booted(): void
    {
        static::creating(function ($recurso) {
            // Generamos el slug a partir del nombre si no se ha proporcionado
            if (empty($recurso->slug)) {
                $recurso->slug = Str::slug($recurso->name);
            }
        });
    }

    // Relación: un recurso pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relación: un recurso puede tener muchas reservas
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Comprueba si el recurso está libre en un rango de tiempo concreto.
    // Devuelve true si está disponible, false si ya hay una reserva que se solapa.
    public function isAvailableAt(Carbon $inicio, Carbon $fin, ?int $excluirId = null): bool
    {
        // Buscamos reservas que se solapen con el rango dado (y que no estén canceladas)
        $query = $this->reservations()
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($inicio, $fin) {
                // Caso 1: la reserva existente empieza dentro de nuestro rango
                $q->whereBetween('start_time', [$inicio, $fin->copy()->subSecond()])
                // Caso 2: la reserva existente termina dentro de nuestro rango
                ->orWhereBetween('end_time', [$inicio->copy()->addSecond(), $fin])
                // Caso 3: la reserva existente envuelve completamente nuestro rango
                ->orWhere(function ($q) use ($inicio, $fin) {
                    $q->where('start_time', '<=', $inicio)->where('end_time', '>=', $fin);
                });
            });

        // Si estamos editando una reserva existente, la excluimos de la búsqueda
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        // doesntExist() devuelve true si NO hay ninguna reserva que se solape (= está libre)
        return $query->doesntExist();
    }
}
