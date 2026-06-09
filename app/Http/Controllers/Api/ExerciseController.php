<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExerciseService;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    protected $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * GET /api/exercises
     * Listar todos los ejercicios con filtros opcionales
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'grupo_muscular', 'dificultad', 'equipamiento', 'deleted']);
        $exercises = $this->exerciseService->listExercises($filters);

        return response()->json([
            'success' => true,
            'data'    => $exercises
        ]);
    }

    /**
     * GET /api/exercises/{id}
     * Obtener detalle de un ejercicio
     */
    public function show($id)
    {
        $exercise = $this->exerciseService->getExercise($id);

        return response()->json([
            'success' => true,
            'data'    => $exercise
        ]);
    }

    /**
     * POST /api/exercises
     * Crear un nuevo ejercicio (Sólo Admin)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:255|unique:ejercicios,nombre',
            'grupo_muscular' => 'required|string|max:100',
            'dificultad'     => 'required|string|max:100',
            'equipamiento'   => 'required|string|max:100',
            'series'         => 'nullable|string|max:50',
            'repeticiones'   => 'nullable|string|max:50',
            'descripcion'    => 'nullable|string',
            'imagen_url'     => 'nullable|url'
        ]);

        $exercise = $this->exerciseService->createExercise($data);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio creado correctamente.',
            'data'    => $exercise
        ], 201);
    }

    /**
     * PUT /api/exercises/{id}
     * Actualizar ejercicio (Sólo Admin)
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre'         => 'sometimes|string|max:255|unique:ejercicios,nombre,' . $id,
            'grupo_muscular' => 'sometimes|string|max:100',
            'dificultad'     => 'sometimes|string|max:100',
            'equipamiento'   => 'sometimes|string|max:100',
            'series'         => 'nullable|string|max:50',
            'repeticiones'   => 'nullable|string|max:50',
            'descripcion'    => 'nullable|string',
            'imagen_url'     => 'nullable|url'
        ]);

        $exercise = $this->exerciseService->updateExercise($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio actualizado correctamente.',
            'data'    => $exercise
        ]);
    }

    /**
     * DELETE /api/exercises/{id}
     * Soft delete ejercicio (Sólo Admin)
     */
    public function destroy($id)
    {
        $this->exerciseService->deleteExercise($id);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio inhabilitado correctamente.'
        ]);
    }

    /**
     * POST /api/exercises/{id}/restore
     * Restaurar ejercicio inhabilitado (Sólo Admin)
     */
    public function restore($id)
    {
        $this->exerciseService->restoreExercise($id);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio restaurado correctamente.'
        ]);
    }
}
