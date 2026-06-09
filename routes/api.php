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
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ExerciseController;
use Illuminate\Support\Facades\Route;

// ── Pública ──────────────────────────────────────────
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/me',        [AuthController::class, 'me']);
Route::post('/wompi/signature', [WompiController::class, 'generateSignature']);
Route::post('/wompi/checkout',  [WompiController::class, 'checkoutConfig']);
Route::post('/wompi/webhook',   [WompiController::class, 'webhook']);
Route::get('/rutinas', [RutinaController::class, 'index']);
Route::get('/exercises', [ExerciseController::class, 'index']);
Route::get('/exercises/{id}', [ExerciseController::class, 'show']);

Route::get('/plans', function () {
    return response()->json([
        [
            'id' => 'fit', 'nombre' => 'FIT', 'precio' => 69900,
            'precio_display' => '$69.900/mes', 'periodo' => '/mes',
            'descripcion' => '1 sede, clases grupales, zona cardio y pesas, vestuarios, app de seguimiento, sin fidelidad obligatoria.',
            'beneficios' => ['1 sede','Clases grupales','Zona cardio y pesas','Vestuarios','App de seguimiento','Sin fidelidad obligatoria'],
            'color' => 'default',
        ],
        [
            'id' => 'smart', 'nombre' => 'SMART', 'precio' => 99900,
            'precio_display' => '$99.900/mes', 'periodo' => '/mes',
            'descripcion' => 'Multisede (hasta 3 sedes), clases ilimitadas, evaluación física mensual, descuentos 15% en tienda, soporte WhatsApp.',
            'beneficios' => ['Multisede (hasta 3 sedes)','Clases ilimitadas','Evaluación física mensual','Descuentos 15% en tienda','Soporte WhatsApp'],
            'color' => 'featured', 'badge' => 'Más popular',
        ],
        [
            'id' => 'black', 'nombre' => 'BLACK', 'precio' => 119900,
            'precio_display' => '$119.900/mes', 'periodo' => '/mes',
            'descripcion' => 'Todas las sedes Colombia, coach personalizado asignado, rutina exclusiva mensual, plan nutricional, Smart Spa, llevar invitado 5 veces/mes, acceso 24/7.',
            'beneficios' => ['Todas las sedes Colombia','Coach personalizado asignado','Rutina exclusiva mensual','Plan nutricional','Smart Spa','Llevar invitado 5 veces/mes','Acceso 24/7'],
            'color' => 'default', 'badge' => 'Premium', 'requiere_coach' => true,
        ],
    ]);
});

// ── Tienda pública ───────────────────────────────────
Route::prefix('tienda')->group(function () {
    Route::get('/productos',      [TiendaController::class, 'productos']);
    Route::get('/productos/{id}', [TiendaController::class, 'producto']);
    Route::get('/categorias',     [TiendaController::class, 'categorias']);
    Route::get('/marcas',         [TiendaController::class, 'marcas']);
});

// ── Reportes públicos ────────────────────────────────
Route::get('/reports/pdf',   [ReportController::class, 'pdf']);
Route::get('/reports/excel', [ReportController::class, 'excel']);

// ── Dashboard público ────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index']);

// ── Ventas públicas ──────────────────────────────────
Route::get('/sales', [SaleController::class, 'index']);

// ── Productos públicos ───────────────────────────────
Route::get('/products',               [ProductController::class, 'index']);
Route::get('/products/stock/summary', [ProductController::class, 'stockSummary']);
Route::get('/products/{product}',     [ProductController::class, 'show']);
Route::post('/products',              [ProductController::class, 'store']);
Route::post('/products/import',       [ProductController::class, 'import']);
Route::put('/products/{product}',     [ProductController::class, 'update']);
Route::patch('/products/{product}',   [ProductController::class, 'update']);
Route::delete('/products/{product}',  [ProductController::class, 'destroy']);

// ── Rutas Protegidas mediante Supabase Auth ───────────
Route::middleware(['supabase.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales',       [SaleController::class, 'store']);

    // Rutas exclusivas del Coach
    Route::middleware(['role:coach'])->prefix('coach')->group(function () {
        Route::get('/miembros',             [CoachController::class, 'miembros']);
        Route::get('/rutina/{id_usuario}',  [CoachController::class, 'obtenerRutina']);
        Route::post('/asignar',             [CoachController::class, 'asignar']);
        Route::post('/rutina/{id_usuario}', [CoachController::class, 'crearRutina']);
        Route::get('/email-logs',           [CoachController::class, 'emailLogs']);
    });

    // Rutas exclusivas del Administrador
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::post('/users/{id}/restore', [AdminController::class, 'restoreUser']);
        Route::get('/memberships', [AdminController::class, 'memberships']);
        Route::get('/dashboard-stats', [AdminController::class, 'dashboardStats']);
        
        // Ejercicios Admin CRUD
        Route::post('/exercises', [ExerciseController::class, 'store']);
        Route::put('/exercises/{id}', [ExerciseController::class, 'update']);
        Route::delete('/exercises/{id}', [ExerciseController::class, 'destroy']);
        Route::post('/exercises/{id}/restore', [ExerciseController::class, 'restore']);

        // Productos Admin
        Route::post('/products/{id}/restore', [ProductController::class, 'restore']);
    });
});
