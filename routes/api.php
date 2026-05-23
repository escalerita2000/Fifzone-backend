<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\TiendaController;
use Illuminate\Support\Facades\Route;

// ── Pública ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// ── Tienda pública (no requiere login) ───────────────────
Route::prefix('tienda')->group(function () {
    Route::get('/productos',        [TiendaController::class, 'productos']);
    Route::get('/productos/{id}',   [TiendaController::class, 'producto']);
    Route::get('/categorias',       [TiendaController::class, 'categorias']);
    Route::get('/marcas',           [TiendaController::class, 'marcas']);
});

// ── Protegidas con Sanctum ───────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/products/stock/summary', [ProductController::class, 'stockSummary']);
    Route::apiResource('/products', ProductController::class);

    Route::get('/sales',        [SaleController::class, 'index']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales',       [SaleController::class, 'store']);

    Route::get('/reports/pdf',   [ReportController::class, 'pdf']);
    Route::get('/reports/excel', [ReportController::class, 'excel']);

    Route::get('/dispositivos', function() {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 1, 'nombre' => 'Caminadora Pro-Form', 'tipo' => 'Cardio', 'estado' => 'operativo', 'ultima_mantencion' => '2026-04-10'],
                ['id' => 2, 'nombre' => 'Bicicleta Spinning Matrix', 'tipo' => 'Cardio', 'estado' => 'operativo', 'ultima_mantencion' => '2026-03-15'],
                ['id' => 3, 'nombre' => 'Prensa de Piernas Hammer Strength', 'tipo' => 'Fuerza', 'estado' => 'en mantencion', 'ultima_mantencion' => '2026-05-01'],
                ['id' => 4, 'nombre' => 'Mancuernero Completo (2kg - 40kg)', 'tipo' => 'Peso Libre', 'estado' => 'operativo', 'ultima_mantencion' => '2026-01-20'],
                ['id' => 5, 'nombre' => 'Elíptica Life Fitness', 'tipo' => 'Cardio', 'estado' => 'fuera de servicio', 'ultima_mantencion' => '2025-12-05'],
            ]
        ]);
    });

    Route::get('/prestamos', function() {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 1, 'usuario' => 'Carlos Gomez', 'dispositivo' => 'Cinturón de Fuerza (M)', 'fecha_prestamo' => '2026-05-20 08:30', 'estado' => 'activo'],
                ['id' => 2, 'usuario' => 'Mariana Restrepo', 'dispositivo' => 'Bandas Elásticas (Resistencia Media)', 'fecha_prestamo' => '2026-05-20 09:15', 'estado' => 'activo'],
                ['id' => 3, 'usuario' => 'Juan Perez', 'dispositivo' => 'Cinturón de Fuerza (L)', 'fecha_prestamo' => '2026-05-19 14:00', 'estado' => 'devuelto'],
                ['id' => 4, 'usuario' => 'Andres Tobon', 'dispositivo' => 'Lazo de Saltar Speed 2.0', 'fecha_prestamo' => '2026-05-20 11:00', 'estado' => 'activo'],
            ]
        ]);
    });
});