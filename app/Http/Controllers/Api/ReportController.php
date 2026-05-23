<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function pdf(Request $request)
    {
        $type = $request->query('type', 'sales');

        if ($type === 'sales') {
            $sales = Venta::with(['items.producto', 'usuario'])->latest('fecha')->get();
            $total_revenue = $sales->sum('total');
            $total_profit = $sales->sum(fn($s) => $s->total - $s->items->sum(fn($i) => ($i->producto?->precio_costo ?? ($i->precio_unitario * 0.65)) * $i->cantidad));

            $pdf   = Pdf::loadView('reports.sales', [
                'sales'         => $sales,
                'total_revenue' => $total_revenue,
                'total_profit'  => $total_profit,
                'generated_at'  => now()->format('d/m/Y H:i'),
            ])->setPaper('a4', 'landscape');
            return $pdf->download('fitzone-ventas-'.now()->format('Ymd').'.pdf');
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
        return $pdf->download('fitzone-inventario-'.now()->format('Ymd').'.pdf');
    }

    public function excel(Request $request)
    {
        $type = $request->query('type', 'sales');
        if ($type === 'sales') {
            return Excel::download(new SalesExport, 'fitzone-ventas-'.now()->format('Ymd').'.xlsx');
        }
        return Excel::download(new StockExport, 'fitzone-inventario-'.now()->format('Ymd').'.xlsx');
    }
}