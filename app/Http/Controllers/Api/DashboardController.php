<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\VentaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // GET /api/dashboard - Público (sin autenticación)
    public function index()
    {
        $totalProductosActivos = Producto::where('activo', true)->count();
        $totalVentas = (float) Venta::sum('total');
        $totalUsuarios = DB::table('user_count')->value('total') ?? 0;
        $totalOrdenes = Venta::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_productos_activos' => $totalProductosActivos,
                'total_ventas' => $totalVentas,
                'total_usuarios' => $totalUsuarios,
                'total_ordenes' => $totalOrdenes,
            ]
        ]);
    }
}