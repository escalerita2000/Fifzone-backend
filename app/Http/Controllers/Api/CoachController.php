<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rutina;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    // GET /api/coach/miembros
    public function miembros(Request $request)
    {
        // Leer email del JWT de Supabase en el header Authorization
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token no proporcionado.'], 401);
        }

        $parts = explode('.', substr($authHeader, 7));

        if (count($parts) !== 3) {
            return response()->json(['success' => false, 'message' => 'Token inválido.'], 401);
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $email   = $payload['email'] ?? null;

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email no encontrado en el token.'], 401);
        }

        $coach = User::where('email', $email)->first();

        if (!$coach) {
            return response()->json(['success' => false, 'message' => 'Coach no encontrado.'], 404);
        }

        $miembros = User::where('id_coach', $coach->id_usuario)
            ->select('id_usuario', 'nombre', 'email', 'plan')
            ->get()
            ->map(fn($m) => [
                'id_usuario' => $m->id_usuario,
                'nombre'     => $m->nombre,
                'email'      => $m->email,
                'plan'       => $m->plan,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $miembros,
        ]);
    }

    // POST /api/coach/asignar
    public function asignar(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_coach' => 'required|integer|exists:usuarios,id_usuario',
        ]);

        $coach = User::find($request->id_coach);
        if (!$coach || ($coach->rol !== 'coach' && $coach->rol !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'El coach asignado debe tener rol de coach o admin',
            ], 403);
        }

        $usuario = User::find($request->id_usuario);
        $usuario->update(['id_coach' => $request->id_coach]);

        return response()->json([
            'success' => true,
            'message' => 'Coach asignado correctamente',
        ]);
    }

    // GET /api/coach/rutina/{id_usuario}
    public function obtenerRutina($id_usuario)
    {
        $rutinas = Rutina::where('id_usuario', $id_usuario)->get();

        return response()->json([
            'success' => true,
            'data' => $rutinas,
        ]);
    }

    // POST /api/coach/rutina/{id_usuario}
    public function crearRutina(Request $request, $id_usuario)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'duracion' => 'required|integer',
            'nivel' => 'required|string',
        ]);

        $rutina = Rutina::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'duracion' => $request->duracion,
            'nivel' => $request->nivel,
            'id_usuario' => $id_usuario,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rutina creada correctamente',
            'data' => $rutina,
        ], 201);
    }
}
