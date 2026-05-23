<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca'])->where('activo', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $productos = $query->orderBy('nombre')->get()->map(function ($p) {
            return [
                'id'            => $p->id_producto,
                'nombre'        => $p->nombre,
                'sku'           => $p->sku,
                'descripcion'   => $p->descripcion,
                'categoria'     => $p->categoria?->nombre,
                'marca'         => $p->marca?->nombre,
                'precio'        => (float) $p->precio_venta,
                'precio_costo'  => (float) $p->precio_costo,
                'stock'         => $p->stock_actual,
                'estado_stock'  => $p->estado_stock,
                'margen'        => $p->margen,
                'imagen'        => $p->imagen_url,
            ];
        });

        return response()->json([
            'success'   => true,
            'total'     => $productos->count(),
            'data'      => $productos,
            'products'  => $productos,
        ]);
    }

    public function stockSummary()
    {
        $totalProducts = Producto::where('activo', true)->count();
        $totalStock = Producto::where('activo', true)->sum('stock_actual');
        $lowStockProducts = Producto::where('activo', true)
            ->where(function ($query) {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                      ->orWhere('stock_actual', '<=', 5);
            })->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock' => $totalStock,
                'low_stock' => $lowStockProducts,
            ]
        ]);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:150',
            'sku'         => 'required|string|max:60|unique:productos,sku',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $producto = Producto::create([
            'nombre'        => $data['nombre'],
            'sku'           => $data['sku'],
            'precio_venta'  => $data['precio'],
            'precio_costo'  => $data['precio'] * 0.65, // Generar un costo aproximado del 65% para cálculo de ganancias
            'stock_actual'  => $data['stock'],
            'stock_minimo'  => 5,
            'descripcion'   => $data['descripcion'] ?? null,
            'activo'        => true,
        ]);

        $formatted = [
            'id'            => $producto->id_producto,
            'nombre'        => $producto->nombre,
            'sku'           => $producto->sku,
            'descripcion'   => $producto->descripcion,
            'categoria'     => null,
            'marca'         => null,
            'precio'        => (float) $producto->precio_venta,
            'precio_costo'  => (float) $producto->precio_costo,
            'stock'         => $producto->stock_actual,
            'estado_stock'  => $producto->estado_stock,
            'margen'        => $producto->margen,
            'imagen'        => $producto->imagen_url,
        ];

        return response()->json([
            'success' => true,
            'product' => $formatted,
            'data'    => $formatted,
        ], 201);
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $producto = Producto::where('id_producto', $id)->firstOrFail();

        $data = $request->validate([
            'nombre'      => 'required|string|max:150',
            'sku'         => "required|string|max:60|unique:productos,sku,{$id},id_producto",
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $producto->update([
            'nombre'       => $data['nombre'],
            'sku'          => $data['sku'],
            'precio_venta' => $data['precio'],
            'precio_costo' => $data['precio'] * 0.65,
            'stock_actual' => $data['stock'],
            'descripcion'  => $data['descripcion'] ?? null,
        ]);

        $formatted = [
            'id'            => $producto->id_producto,
            'nombre'        => $producto->nombre,
            'sku'           => $producto->sku,
            'descripcion'   => $producto->descripcion,
            'categoria'     => $producto->categoria?->nombre,
            'marca'         => $producto->marca?->nombre,
            'precio'        => (float) $producto->precio_venta,
            'precio_costo'  => (float) $producto->precio_costo,
            'stock'         => $producto->stock_actual,
            'estado_stock'  => $producto->estado_stock,
            'margen'        => $producto->margen,
            'imagen'        => $producto->imagen_url,
        ];

        return response()->json([
            'success' => true,
            'product' => $formatted,
            'data'    => $formatted,
        ]);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $producto = Producto::where('id_producto', $id)->firstOrFail();
        // Desactivar lógicamente para no romper el historial de ventas
        $producto->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente.'
        ]);
    }
}