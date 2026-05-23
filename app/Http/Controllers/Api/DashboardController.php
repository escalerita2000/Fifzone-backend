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
    // GET /api/dashboard
    public function index()
    {
        // 1. Ventas de hoy
        $todaySalesCount = Venta::whereDate('fecha', today())->count();
        $todayRevenue = (float) Venta::whereDate('fecha', today())->sum('total');
        
        // Calcular ganancia hoy
        $todayProfit = 0;
        $todaySales = Venta::whereDate('fecha', today())->with('items.producto')->get();
        foreach ($todaySales as $v) {
            foreach ($v->items as $i) {
                $cost = $i->producto?->precio_costo ?? ($i->precio_unitario * 0.65);
                $todayProfit += ($i->precio_unitario - $cost) * $i->cantidad;
            }
        }

        // 2. Ingresos del mes
        $monthRevenue = (float) Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('total');

        $monthProfit = 0;
        $monthSales = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->with('items.producto')
            ->get();
        foreach ($monthSales as $v) {
            foreach ($v->items as $i) {
                $cost = $i->producto?->precio_costo ?? ($i->precio_unitario * 0.65);
                $monthProfit += ($i->precio_unitario - $cost) * $i->cantidad;
            }
        }

        // 3. Productos con stock bajo (menor o igual a su stock_minimo, o menor o igual a 5)
        $lowStockCount = Producto::where('activo', true)
            ->where(function ($query) {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                      ->orWhere('stock_actual', '<=', 5);
            })
            ->count();

        // 4. Top productos más vendidos
        $topProductsQuery = VentaItem::select('id_producto', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('id_producto')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $topProductsFormatted = [];
        foreach ($topProductsQuery as $item) {
            $prod = Producto::find($item->id_producto);
            if ($prod) {
                $topProductsFormatted[] = [
                    'id'       => $prod->id_producto,
                    'nombre'   => $prod->nombre,
                    'cantidad' => (int) $item->total_vendido,
                ];
            }
        }

        // Si no hay productos vendidos en absoluto, rellenamos con vacíos o mocks para la UX
        if (empty($topProductsFormatted)) {
            $topProductsFormatted = Producto::where('activo', true)
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'id'       => $p->id_producto,
                    'nombre'   => $p->nombre,
                    'cantidad' => 0,
                ])
                ->toArray();
        }

        return response()->json([
            'success'       => true,
            // Formato plano esperado por Admin.jsx (TabDashboard)
            'ventas_hoy'    => $todaySalesCount,
            'ingresos_mes'  => $monthRevenue,
            'stock_bajo'    => $lowStockCount,
            'top_productos' => $topProductsFormatted,
            // Formato estructurado original compatible con otros usos
            'dashboard' => [
                'today' => [
                    'sales_count' => $todaySalesCount,
                    'revenue'     => $todayRevenue,
                    'profit'      => $todayProfit,
                ],
                'month' => [
                    'sales_count' => Venta::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->count(),
                    'revenue'     => $monthRevenue,
                    'profit'      => $monthProfit,
                ],
                'inventory' => [
                    'low_stock'   => $lowStockCount,
                ],
                'top_products' => $topProductsFormatted,
            ]
        ]);
    }
}