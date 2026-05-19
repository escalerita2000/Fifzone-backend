<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['items', 'registeredBy:id,name']);
        if ($request->filled('from'))   $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))     $query->whereDate('created_at', '<=', $request->to);
        if ($request->filled('type'))   $query->where('type', $request->type);

        $sales = $query->latest()->get();
        return response()->json(['success' => true, 'total' => $sales->count(), 'sales' => $sales]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'registeredBy:id,name']);
        return response()->json(['success' => true, 'sale' => $sale]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer'           => 'required|string|max:120',
            'type'               => 'required|in:external,internal',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $total = 0; $profit = 0; $saleItems = [];

            foreach ($data['items'] as $item) {
                $product   = Product::lockForUpdate()->findOrFail($item['product_id']);
                $unitPrice = $data['type'] === 'internal' ? $product->internal_price : $product->sale_price;
                $subtotal  = $unitPrice * $item['quantity'];

                $total  += $subtotal;
                $profit += ($unitPrice - $product->cost_price) * $item['quantity'];
                $product->decrementStock($item['quantity']);

                $saleItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $unitPrice,
                    'subtotal'     => $subtotal,
                ];
            }

            $sale = Sale::create([
                'customer'      => $data['customer'],
                'type'          => $data['type'],
                'total'         => $total,
                'profit'        => $profit,
                'status'        => 'completed',
                'registered_by' => $request->user()->id,
                'notes'         => $data['notes'] ?? null,
            ]);

            foreach ($saleItems as $itemData) {
                $sale->items()->create($itemData);
            }

            return $sale->load('items');
        });

        return response()->json(['success' => true, 'message' => 'Venta registrada.', 'sale' => $sale], 201);
    }
}