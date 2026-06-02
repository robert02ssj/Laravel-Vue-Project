<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\Request;

// Este controlador es solo para administradores y gestores.
// Gestiona el panel de administración, usuarios y logs de reservas.

class AdminController extends Controller
{
    // Devolver las estadísticas del panel principal (dashboard)
    public function dashboard()
    {
        return response()->json([
            'total_users'        => User::count(),                                      // Número total de usuarios
            'total_resources'    => Resource::count(),                                  // Número total de recursos
            'total_reservations' => Reservation::count(),                               // Número total de reservas
            'pending'            => Reservation::where('status', 'pending')->count(),   // Reservas pendientes
            'confirmed'          => Reservation::where('status', 'confirmed')->count(), // Reservas confirmadas
            'cancelled'          => Reservation::where('status', 'cancelled')->count(), // Reservas canceladas
            'recent'             => Reservation::with(['user', 'resource'])->latest()->take(10)->get(), // Últimas 10 reservas
        ]);
    }

    // Listar todos los usuarios (paginado)
    public function users()
    {
        // Incluimos los roles de cada usuario en la respuesta
        return response()->json(User::with('roles')->paginate(20));
    }

    // Editar los datos de un usuario concreto
    public function updateUser(Request $request, User $user)
    {
        $datos = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id, // el email puede repetirse solo para él mismo
            'role'  => 'sometimes|string|exists:roles,name',              // el rol debe existir en la tabla roles
        ]);

        // Si se ha enviado un nuevo rol, lo cambiamos
        if (isset($datos['role'])) {
            // syncRoles() reemplaza todos los roles actuales por los nuevos
            $user->syncRoles([$datos['role']]);
            unset($datos['role']); // quitamos 'role' del array para no intentar guardarlo como columna
        }

        $user->update($datos);

        return response()->json($user->load('roles'));
    }

    // Borrar un usuario
    public function deleteUser(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }

    // Ver el historial de cambios de una reserva concreta
    public function reservationLogs(Reservation $reservation)
    {
        // Cargamos la reserva con sus logs, el usuario de cada log, el usuario y el recurso
        return response()->json($reservation->load(['logs.user', 'user', 'resource']));
    }
}
