<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'total_users'        => User::count(),
            'total_resources'    => Resource::count(),
            'total_reservations' => Reservation::count(),
            'pending'            => Reservation::where('status', 'pending')->count(),
            'confirmed'          => Reservation::where('status', 'confirmed')->count(),
            'cancelled'          => Reservation::where('status', 'cancelled')->count(),
            'recent'             => Reservation::with(['user', 'resource'])->latest()->take(10)->get(),
        ]);
    }

    public function users()
    {
        return response()->json(User::with('roles')->paginate(20));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role'  => 'sometimes|string|exists:roles,name',
        ]);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);
        return response()->json($user->load('roles'));
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function reservationLogs(Reservation $reservation)
    {
        return response()->json($reservation->load(['logs.user', 'user', 'resource']));
    }
}
