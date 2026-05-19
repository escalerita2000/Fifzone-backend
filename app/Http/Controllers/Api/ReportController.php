<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function pdf(Request $request)
    {
        $type = $request->query('type', 'sales');

        if ($type === 'sales') {
            $sales = Sale::with('items')->latest()->get();
            $pdf   = Pdf::loadView('reports.sales', [
                'sales'         => $sales,
                'total_revenue' => $sales->sum('total'),
                'total_profit'  => $sales->sum('profit'),
                'generated_at'  => now()->format('d/m/Y H:i'),
            ])->setPaper('a4', 'landscape');
            return $pdf->download('fitzone-ventas-'.now()->format('Ymd').'.pdf');
        }

        $products = Product::all();
        $pdf = Pdf::loadView('reports.stock', [
            'products'     => $products,
            'total_value'  => $products->sum(fn($p) => $p->stock * $p->cost_price),
            'out_of_stock' => $products->where('stock', 0)->count(),
            'low_stock'    => $products->whereBetween('stock', [1, 5])->count(),
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