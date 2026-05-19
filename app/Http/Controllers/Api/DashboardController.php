<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales = Sale::whereDate('created_at', $today)->get();
        $monthSales = Sale::whereBetween('created_at', [$monthStart, Carbon::now()])->get();
        $allSales   = Sale::all();
        $products   = Product::all();

        $topProducts = SaleItem::select('product_id', 'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')->limit(5)->get();

        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date     = Carbon::today()->subDays($i);
            $daySales = Sale::whereDate('created_at', $date)->get();
            $last7Days->push([
                'date'    => $date->toDateString(),
                'revenue' => $daySales->sum('total'),
                'profit'  => $daySales->sum('profit'),
                'count'   => $daySales->count(),
            ]);
        }

        return response()->json([
            'success'   => true,
            'dashboard' => [
                'today'     => ['sales_count' => $todaySales->count(), 'revenue' => $todaySales->sum('total'), 'profit' => $todaySales->sum('profit')],
                'month'     => ['sales_count' => $monthSales->count(), 'revenue' => $monthSales->sum('total'), 'profit' => $monthSales->sum('profit')],
                'all_time'  => ['sales_count' => $allSales->count(), 'revenue' => $allSales->sum('total'), 'profit' => $allSales->sum('profit')],
                'inventory' => ['total_products' => $products->count(), 'out_of_stock' => $products->where('stock', 0)->count(), 'low_stock' => $products->whereBetween('stock', [1, 5])->count(), 'total_inventory_value' => $products->sum(fn($p) => $p->stock * $p->cost_price)],
                'top_products' => $topProducts,
                'last_7_days'  => $last7Days,
            ],
        ]);
    }
}