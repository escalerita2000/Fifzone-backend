<?php
namespace App\Http\Controllers\Api;

set_time_limit(120);

use App\Http\Controllers\Controller;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function applySalesFilters($query, Request $request)
    {
        if ($request->filled('anio')) {
            $query->whereYear('fecha', $request->query('anio'));
        }

        if ($request->filled('mes')) {
            $query->whereMonth('fecha', $request->query('mes'));
        }

        if ($request->filled('dia')) {
            $query->whereDay('fecha', $request->query('dia'));
        }

        if ($request->filled('categoria') || $request->filled('marca')) {
            $query->whereHas('ventaItems', function ($q) use ($request) {
                $q->whereHas('producto', function ($pq) use ($request) {
                    if ($request->filled('categoria')) {
                        $pq->whereHas('categoria', function ($cq) use ($request) {
                            $cq->where('nombre', $request->query('categoria'));
                        });
                    }
                    if ($request->filled('marca')) {
                        $pq->whereHas('marca', function ($mq) use ($request) {
                            $mq->where('nombre', $request->query('marca'));
                        });
                    }
                });
            }, '>=', 1);
        }

        return $query;
    }

    public function pdf(Request $request)
    {
        $type = $request->query('type', 'sales');

        if ($type === 'sales') {
            $query = Venta::with(['usuario']);
            $query = $this->applySalesFilters($query, $request);
            $sales = $query->latest('fecha')->get();

            $ventaIds = $sales->pluck('id_venta')->toArray();
            $allItems = VentaItem::whereIn('id_venta', $ventaIds)
                ->with('producto')
                ->get()
                ->groupBy('id_venta');

            $total_revenue = $sales->sum('total');
            $total_profit = $sales->sum(function ($s) use ($allItems) {
                $items = $allItems[$s->id_venta] ?? [];
                return $s->total - collect($items)->sum(fn($i) => ($i->producto?->precio_costo ?? ($i->precio_unitario * 0.65)) * $i->cantidad);
            });

            $pdf = Pdf::loadView('reports.sales', [
                'sales'         => $sales,
                'ventaItems'    => $allItems,
                'total_revenue' => $total_revenue,
                'total_profit'  => $total_profit,
                'generated_at'  => now()->format('d/m/Y H:i'),
            ])->setPaper('a4', 'landscape');
            return $pdf->download('fitzone-ventas-' . now()->format('Ymd') . '.pdf');
        }

        $products = Producto::with(['categoria', 'marca'])->where('activo', true)->get();
        $total_value = $products->sum(fn($p) => $p->stock_actual * ($p->precio_costo ?? 0));
        $out_of_stock = $products->where('stock_actual', 0)->count();
        $low_stock = $products->filter(fn($p) => $p->stock_actual > 0 && $p->stock_actual <= 5)->count();

        $pdf = Pdf::loadView('reports.stock', [
            'products'     => $products,
            'total_value'  => $total_value,
            'out_of_stock' => $out_of_stock,
            'low_stock'    => $low_stock,
            'generated_at' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');
        return $pdf->download('fitzone-inventario-' . now()->format('Ymd') . '.pdf');
    }

    public function excel(Request $request)
    {
        $type = $request->query('type', 'sales');
        if ($type === 'sales') {
            $filters = [
                'anio' => $request->query('anio'),
                'mes' => $request->query('mes'),
                'dia' => $request->query('dia'),
                'categoria' => $request->query('categoria'),
                'marca' => $request->query('marca'),
            ];
            return Excel::download(new SalesExport($filters), 'fitzone-ventas-' . now()->format('Ymd') . '.xlsx');
        }
        return Excel::download(new StockExport, 'fitzone-inventario-' . now()->format('Ymd') . '.xlsx');
    }
}
