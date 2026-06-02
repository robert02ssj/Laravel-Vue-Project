<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// Modelo que representa a un usuario de la aplicación.
// Hereda de Authenticatable para poder usarlo con el sistema de login de Laravel.

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;   // permite crear usuarios de prueba con factories
    use Notifiable;   // permite enviar notificaciones al usuario
    use HasApiTokens; // añade soporte para tokens de Sanctum (autenticación API)
    use HasRoles;     // añade soporte de roles y permisos de Spatie

    // Campos que se pueden rellenar masivamente (User::create([...]))
    protected $fillable = ['name', 'email', 'password'];

    // Campos que NUNCA se incluyen en las respuestas JSON (por seguridad)
    protected $hidden = ['password', 'remember_token'];

    // Casts: Laravel convierte automáticamente estos campos al tipo indicado
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // se convierte a objeto Carbon (fecha)
            'password'          => 'hashed',   // al guardar, se hashea automáticamente
        ];
    }

    // Relación: un usuario puede tener muchas reservas
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
