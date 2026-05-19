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

});