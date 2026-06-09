<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RoutineService;
use App\Services\UserService;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    protected $routineService;
    protected $userService;

    public function __construct(RoutineService $routineService, UserService $userService)
    {
        $this->routineService = $routineService;
        $this->userService = $userService;
    }

    /**
     * GET /api/coach/miembros
     * Listar miembros asignados al coach autenticado
     */
    public function miembros(Request $request)
    {
        $coach = auth()->user();

        if (!$coach) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        $miembros = User::where('id_coach', $coach->id_usuario)
            ->select('id_usuario', 'nombre', 'email', 'plan')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $miembros,
        ]);
    }

    /**
     * POST /api/coach/asignar
     * Asignar un coach a un usuario
     */
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

    /**
     * GET /api/coach/rutina/{id_usuario}
     * Obtener rutinas asignadas al usuario
     */
    public function obtenerRutina($id_usuario)
    {
        $rutinas = $this->routineService->getUserRoutines($id_usuario);

        return response()->json([
            'success' => true,
            'data' => $rutinas,
        ]);
    }

    /**
     * POST /api/coach/rutina/{id_usuario}
     * Crear y asignar una rutina a un usuario
     */
    public function crearRutina(Request $request, $id_usuario)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'duracion'    => 'required|string|max:100',
            'nivel'       => 'required|string|max:100',
            'ejercicios'  => 'nullable|array',
            'imagen'      => 'nullable|string',
        ]);

        $coach = auth()->user();

        if (!$coach) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        $rutina = $this->routineService->createRoutine([
            'nombre'      => $request->nombre,
            'duracion'    => $request->duracion,
            'nivel'       => $request->nivel,
            'ejercicios'  => $request->ejercicios,
            'imagen'      => $request->imagen,
            'id_coach'    => $coach->id_usuario,
        ]);

        $this->routineService->assignRoutineToUser($rutina->id, $id_usuario);

        return response()->json([
            'success' => true,
            'message' => 'Rutina creada y asignada correctamente.',
            'data'    => $rutina,
        ], 201);
    }

    /**
     * GET /api/coach/email-logs
     * Obtener el historial de correos enviados por simulaciones
     */
    public function emailLogs(Request $request)
    {
        $userId = $request->integer('id_usuario', 0);
        $logs = $this->routineService->getEmailLogs($userId ?: null);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}
