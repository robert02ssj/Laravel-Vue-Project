<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// Este controlador gestiona el registro, login y logout de usuarios.
// Usa Laravel Sanctum para crear tokens de acceso a la API.

class AuthController extends Controller
{
    // Registrar un usuario nuevo
    public function register(Request $request)
    {
        // Validamos los datos que llegan del formulario
        $datos = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',     // el email no puede repetirse
            'password' => 'required|string|min:8|confirmed', // debe venir también password_confirmation
        ]);

        // Creamos el usuario en la base de datos
        $usuario = User::create($datos);

        // Le asignamos el rol 'user' (rol básico por defecto)
        $usuario->assignRole('user');

        // Devolvemos los datos del usuario y un token para que pueda hacer peticiones autenticadas
        return response()->json([
            'user'  => $usuario,
            'token' => $usuario->createToken('api')->plainTextToken,
        ], 201); // 201 significa "creado con éxito"
    }

    // Iniciar sesión
    public function login(Request $request)
    {
        // Validamos que lleguen email y contraseña
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Auth::attempt comprueba si el email y la contraseña son correctos
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son incorrectas, devolvemos un error
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        // Si llegamos aquí, el usuario está autenticado
        $usuario = $request->user();

        // Devolvemos el usuario (con sus roles) y un token nuevo
        return response()->json([
            'user'  => $usuario->load('roles'), // load('roles') incluye los roles en la respuesta
            'token' => $usuario->createToken('api')->plainTextToken,
        ]);
    }

    // Cerrar sesión (invalidar el token actual)
    public function logout(Request $request)
    {
        // Eliminamos solo el token con el que se hizo esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    // Devolver los datos del usuario que está logueado ahora mismo
    public function me(Request $request)
    {
        // $request->user() nos da el usuario autenticado gracias a Sanctum
        return response()->json($request->user()->load('roles'));
    }
}
