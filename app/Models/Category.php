<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// Modelo que representa una categoría de recursos.
// Ejemplos: Salas de reuniones, Equipamiento, Instalaciones deportivas.

class Category extends Model
{
    use HasFactory;
    use SoftDeletes; // al borrar, guarda la fecha en deleted_at en vez de eliminar el registro

    // Campos que se pueden rellenar masivamente
    protected $fillable = [
        'name',        // nombre de la categoría
        'slug',        // versión del nombre para URLs (ej: "salas-de-reuniones")
        'description', // descripción
        'icon',        // nombre del icono (ej: mdi-laptop)
        'color',       // color en hexadecimal (ej: #3B82F6)
        'active',      // si está activa o no
    ];

    // Casts: conversiones automáticas de tipos
    protected $casts = [
        'active' => 'boolean', // se convierte a true/false en vez de 1/0
    ];

    // Este método se ejecuta automáticamente cuando se va a crear una categoría
    protected static function booted(): void
    {
        static::creating(function ($categoria) {
            // Si no se ha pasado un slug, lo generamos a partir del nombre
            // Str::slug('Salas de Reuniones') → 'salas-de-reuniones'
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->name);
            }
        });
    }

    // Relación: una categoría tiene muchos recursos
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}
