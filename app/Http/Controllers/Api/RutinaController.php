<?php

namespace App\Http\Controllers\Api;

use App\Models\Rutina;
use Illuminate\Http\JsonResponse;

class RutinaController
{
    public function index(): JsonResponse
    {
        $rutinas = Rutina::all();

        return response()->json([
            'success' => true,
            'rutinas' => $rutinas,
        ]);
    }
}
