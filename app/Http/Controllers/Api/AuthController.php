<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario se encuentra inactivo.',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('fitzone-token')->plainTextToken;

        // Registrar ultimo acceso
        $user->update(['ultimo_acceso' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Bienvenido, {$user->nombre}",
            'token'   => $token,
            'user'    => [
                'id'    => $user->id_usuario,
                'name'  => $user->nombre,
                'email' => $user->email,
                'role'  => $user->rol,
            ],
        ]);
    }

    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nombre'        => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'rol'           => 'user',
            'activo'        => true,
        ]);

        $token = $user->createToken('fitzone-token')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'user'    => [
                'id'    => $user->id_usuario,
                'name'  => $user->nombre,
                'email' => $user->email,
                'role'  => $user->rol,
            ],
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Sesión cerrada.']);
    }

    // GET /api/me
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id'       => $user->id_usuario,
                'name'     => $user->nombre,
                'email'    => $user->email,
                'role'     => $user->rol,
                'plan'     => $user->plan,
                'id_coach' => $user->id_coach,
            ]
        ]);
    }
}