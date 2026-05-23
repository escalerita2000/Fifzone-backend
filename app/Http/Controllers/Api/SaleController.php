<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    // GET /api/sales
    public function index()
    {
        $sales = Venta::with(['items.producto', 'usuario'])
            ->latest('fecha')
            ->get()
            ->map(function ($v) {
                return [
                    'id'         => $v->id_venta,
                    'fecha'      => $v->fecha->format('Y-m-d H:i:s'),
                    'customer'   => $v->customer ?? $v->usuario?->nombre ?? 'Cliente General',
                    'type'       => $v->canal === 'web' ? 'external' : 'internal',
                    'total'      => (float) $v->total,
                    'profit'     => (float) ($v->total - $v->items->sum(fn($i) => ($i->producto?->precio_costo ?? 0) * $i->cantidad)),
                    'items'      => $v->items->map(function ($i) {
                        return [
                            'product_name' => $i->producto?->nombre ?? 'Producto Eliminado',
                            'quantity'     => $i->cantidad,
                            'price'        => (float) $i->precio_unitario,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'sales'   => $sales,
            'data'    => $sales,
        ]);
    }

    // POST /api/sales
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer'           => 'nullable|string|max:120',
            'type'               => 'nullable|in:external,internal',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:productos,id_producto',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();

        try {
            $sale = DB::transaction(function () use ($data, $user) {
                $subtotal = 0;
                $total_cost = 0;
                $itemsToCreate = [];

                foreach ($data['items'] as $itemData) {
                    $product = Producto::lockForUpdate()->findOrFail($itemData['product_id']);

                    if ($product->stock_actual < $itemData['quantity']) {
                        throw new \Exception("Stock insuficiente para el producto: {$product->nombre}. Disponible: {$product->stock_actual}");
                    }

                    $itemSubtotal = $product->precio_venta * $itemData['quantity'];
                    $subtotal += $itemSubtotal;
                    $total_cost += ($product->precio_costo ?? ($product->precio_venta * 0.65)) * $itemData['quantity'];

                    // Descontar del inventario
                    $product->decrement('stock_actual', $itemData['quantity']);

                    $itemsToCreate[] = [
                        'id_producto'     => $product->id_producto,
                        'cantidad'        => $itemData['quantity'],
                        'precio_unitario' => $product->precio_venta,
                        'descuento_item'  => 0,
                        'subtotal'        => $itemSubtotal,
                    ];
                }

                $total = $subtotal;
                $customerName = $data['customer'] ?? $user?->nombre ?? 'Cliente Tienda';

                $sale = Venta::create([
                    'numero_factura'  => 'FAC-' . strtoupper(uniqid()),
                    'id_cliente'      => null,
                    'id_usuario'      => $user?->id_usuario ?? 1, // fallback si no hay autenticación completa
                    'fecha'           => now(),
                    'subtotal'        => $subtotal,
                    'descuento_pct'   => 0,
                    'descuento_valor' => 0,
                    'impuesto_pct'    => 0,
                    'impuesto_valor'  => 0,
                    'total'           => $total,
                    'estado'          => 'completada',
                    'canal'           => $data['type'] ?? 'external',
                    'notas'           => $data['notes'] ?? null,
                ]);

                foreach ($itemsToCreate as $item) {
                    $sale->items()->create($item);
                }

                return $sale;
            });

            // Recargar relaciones para formatear la respuesta
            $sale->load(['items.producto', 'usuario']);

            $formatted = [
                'id'       => $sale->id_venta,
                'fecha'    => $sale->fecha->format('Y-m-d H:i:s'),
                'customer' => $sale->customer ?? $sale->usuario?->nombre ?? 'Cliente General',
                'type'     => $sale->canal,
                'total'    => (float) $sale->total,
                'items'    => $sale->items->map(function ($i) {
                    return [
                        'product_name' => $i->producto?->nombre ?? 'Producto Eliminado',
                        'quantity'     => $i->cantidad,
                        'price'        => (float) $i->precio_unitario,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'sale'    => $formatted,
                'data'    => $formatted,
            ], 201);

        } catch (\Exception $e) {
            Log::error("Error registrando venta: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}