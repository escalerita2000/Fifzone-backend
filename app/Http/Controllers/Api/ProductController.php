<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // GET /api/products/{id}
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'marca'])
            ->where('id_producto', $id)
            ->firstOrFail();

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
            'imagen_url'    => $producto->imagen_url,
        ];

        return response()->json([
            'success' => true,
            'product' => $formatted,
            'data'    => $formatted,
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
        $id = DB::table('productos')->insertGetId([
            'nombre'       => $request->nombre,
            'sku'          => $request->sku,
            'descripcion'  => $request->descripcion ?? null,
            'id_categoria' => $request->id_categoria ?? null,
            'id_marca'     => $request->id_marca ?? null,
            'precio_venta' => $request->precio ?? 0,
            'precio_costo' => ($request->precio ?? 0) * 0.65,
            'stock_actual' => $request->stock ?? 0,
            'stock_minimo' => 5,
            'imagen_url'   => $request->imagen_url ?? null,
            'activo'       => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], 'id_producto');

        return response()->json(['success' => true, 'id' => $id], 201);
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $data = [];
        if ($request->has('nombre'))      $data['nombre']       = $request->nombre;
        if ($request->has('sku'))         $data['sku']          = $request->sku;
        if ($request->has('descripcion')) $data['descripcion']  = $request->descripcion;
        if ($request->has('precio'))      $data['precio_venta'] = $request->precio;
        if ($request->has('stock'))       $data['stock_actual'] = $request->stock;
        if ($request->has('imagen_url'))  $data['imagen_url']   = $request->imagen_url;
        if ($request->has('id_categoria')) $data['id_categoria'] = $request->id_categoria;
        if ($request->has('id_marca')) $data['id_marca'] = $request->id_marca;

        DB::table('productos')->where('id_producto', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Producto actualizado']);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        DB::table('productos')->where('id_producto', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Producto eliminado']);
    }

    // POST /api/products/import
    public function import(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.nombre' => 'required|string',
            'productos.*.sku' => 'required|string',
            'productos.*.precio' => 'required|numeric',
            'productos.*.stock' => 'required|integer',
        ]);

        $productos = array_map(function ($p) {
            return [
                'nombre'       => $p['nombre'],
                'sku'          => $p['sku'],
                'descripcion'  => $p['descripcion'] ?? null,
                'id_categoria' => $p['id_categoria'] ?? null,
                'id_marca'     => $p['id_marca'] ?? null,
                'precio_venta' => $p['precio'],
                'precio_costo' => $p['precio'] * 0.65,
                'stock_actual' => $p['stock'],
                'stock_minimo' => 5,
                'imagen_url'   => $p['imagen_url'] ?? null,
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }, $request->productos);

        Producto::insert($productos);

        return response()->json([
            'success' => true,
            'message' => 'Productos importados correctamente',
            'total' => count($productos)
        ], 201);
    }
}