<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RutinaController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\TiendaController;
use App\Http\Controllers\Api\WompiController;
use Illuminate\Support\Facades\Route;

// ── Pública ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/wompi/signature', [WompiController::class, 'generateSignature']);
Route::post('/wompi/checkout', [WompiController::class, 'checkoutConfig']);
Route::post('/wompi/webhook', [WompiController::class, 'webhook']);
Route::get('/plans', function () {
    return response()->json([
        [
            'id' => 'fit',
            'nombre' => 'FIT',
            'precio' => 69900,
            'precio_display' => '$69.900/mes',
            'periodo' => '/mes',
            'descripcion' => '1 sede, clases grupales, zona cardio y pesas, vestuarios, app de seguimiento, sin fidelidad obligatoria.',
            'beneficios' => [
                '1 sede',
                'Clases grupales',
                'Zona cardio y pesas',
                'Vestuarios',
                'App de seguimiento',
                'Sin fidelidad obligatoria',
            ],
            'color' => 'default'
        ],
        [
            'id' => 'smart',
            'nombre' => 'SMART',
            'precio' => 99900,
            'precio_display' => '$99.900/mes',
            'periodo' => '/mes',
            'descripcion' => 'Multisede (hasta 3 sedes), clases ilimitadas, evaluación física mensual, descuentos 15% en tienda, soporte WhatsApp.',
            'beneficios' => [
                'Multisede (hasta 3 sedes)',
                'Clases ilimitadas',
                'Evaluación física mensual',
                'Descuentos 15% en tienda',
                'Soporte WhatsApp',
            ],
            'color' => 'featured',
            'badge' => 'Más popular'
        ],
        [
            'id' => 'black',
            'nombre' => 'BLACK',
            'precio' => 119900,
            'precio_display' => '$119.900/mes',
            'periodo' => '/mes',
            'descripcion' => 'Todas las sedes Colombia, coach personalizado asignado, rutina exclusiva mensual, plan nutricional, Smart Spa, llevar invitado 5 veces/mes, acceso 24/7.',
            'beneficios' => [
                'Todas las sedes Colombia',
                'Coach personalizado asignado',
                'Rutina exclusiva mensual',
                'Plan nutricional',
                'Smart Spa',
                'Llevar invitado 5 veces/mes',
                'Acceso 24/7',
            ],
            'color' => 'default',
            'badge' => 'Premium',
            'requiere_coach' => true
        ]
    ]);
});
Route::get('/rutinas', [RutinaController::class, 'index']);

// ── Tienda pública (no requiere login) ───────────────────
Route::prefix('tienda')->group(function () {
    Route::get('/productos',        [TiendaController::class, 'productos']);
    Route::get('/productos/{id}',   [TiendaController::class, 'producto']);
    Route::get('/categorias',       [TiendaController::class, 'categorias']);
    Route::get('/marcas',           [TiendaController::class, 'marcas']);
});

// ── Reportes públicos (descarga sin Sanctum) ─────────────
Route::get('/reports/pdf',   [ReportController::class, 'pdf']);
Route::get('/reports/excel', [ReportController::class, 'excel']);

// ── Dashboard público ────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index']);

// ── Ventas públicas ──────────────────────────────────────
Route::get('/sales', [SaleController::class, 'index']);

// ── Productos: todas públicas (admin usa tokens Supabase, no Sanctum) ───────
Route::get('/products',               [ProductController::class, 'index']);
Route::get('/products/stock/summary', [ProductController::class, 'stockSummary']);
Route::get('/products/{product}',     [ProductController::class, 'show']);
Route::post('/products',              [ProductController::class, 'store']);
Route::post('/products/import',       [ProductController::class, 'import']);
Route::put('/products/{product}',     [ProductController::class, 'update']);
Route::patch('/products/{product}',   [ProductController::class, 'update']);
Route::delete('/products/{product}',  [ProductController::class, 'destroy']);

// ── Protegidas con Sanctum ───────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales',       [SaleController::class, 'store']);


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

    Route::prefix('coach')->group(function () {
        Route::get('/miembros', [CoachController::class, 'miembros']);
        Route::get('/rutina/{id_usuario}', [CoachController::class, 'obtenerRutina']);
        Route::post('/asignar', [CoachController::class, 'asignar']);
        Route::post('/rutina/{id_usuario}', [CoachController::class, 'crearRutina']);
    });
});