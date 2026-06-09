<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * GET /api/products
     * Listar productos con filtros y búsqueda
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'categoria_id', 'marca_id', 'deleted']);
        // Obtener sin paginación si se requiere para el listado antiguo, o con ella
        // En index anterior se traía todo: Producto::with(...)->get()
        $products = $this->productService->listProducts($filters, 0);

        $formatted = $products->map(function ($p) {
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
                'deleted_at'    => $p->deleted_at,
            ];
        });

        return response()->json([
            'success'   => true,
            'total'     => $formatted->count(),
            'data'      => $formatted,
            'products'  => $formatted,
        ]);
    }

    /**
     * GET /api/products/{id}
     * Obtener detalle de producto
     */
    public function show($id)
    {
        $product = $this->productService->getProduct($id);

        $formatted = [
            'id'            => $product->id_producto,
            'nombre'        => $product->nombre,
            'sku'           => $product->sku,
            'descripcion'   => $product->descripcion,
            'categoria'     => $product->categoria?->nombre,
            'marca'         => $product->marca?->nombre,
            'precio'        => (float) $product->precio_venta,
            'precio_costo'  => (float) $product->precio_costo,
            'stock'         => $product->stock_actual,
            'estado_stock'  => $product->estado_stock,
            'margen'        => $product->margen,
            'imagen_url'    => $product->imagen_url,
            'deleted_at'    => $product->deleted_at,
        ];

        return response()->json([
            'success' => true,
            'product' => $formatted,
            'data'    => $formatted,
        ]);
    }

    /**
     * GET /api/products/stock/summary
     * Resumen de stock
     */
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

    /**
     * POST /api/products
     * Crear producto (Admin)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:255',
            'sku'          => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'id_categoria' => 'nullable|integer',
            'id_marca'     => 'nullable|integer',
            'precio'       => 'required|numeric',
            'stock'        => 'required|integer',
            'imagen_url'   => 'nullable|string',
        ]);

        $product = $this->productService->createProduct([
            'nombre'       => $data['nombre'],
            'sku'          => $data['sku'],
            'descripcion'  => $data['descripcion'],
            'id_categoria' => $data['id_categoria'],
            'id_marca'     => $data['id_marca'],
            'precio_venta' => $data['precio'],
            'stock_actual' => $data['stock'],
            'imagen_url'   => $data['imagen_url'],
            'activo'       => true,
        ]);

        return response()->json(['success' => true, 'id' => $product->id_producto], 201);
    }

    /**
     * PUT /api/products/{id}
     * Actualizar producto (Admin)
     */
    public function update(Request $request, $id)
    {
        $payload = [];
        if ($request->has('nombre'))       $payload['nombre']       = $request->nombre;
        if ($request->has('sku'))          $payload['sku']          = $request->sku;
        if ($request->has('descripcion'))  $payload['descripcion']  = $request->descripcion;
        if ($request->has('precio'))       $payload['precio_venta'] = $request->precio;
        if ($request->has('stock'))        $payload['stock_actual'] = $request->stock;
        if ($request->has('imagen_url'))   $payload['imagen_url']   = $request->imagen_url;
        if ($request->has('id_categoria')) $payload['id_categoria'] = $request->id_categoria;
        if ($request->has('id_marca'))     $payload['id_marca']     = $request->id_marca;

        $this->productService->updateProduct($id, $payload);

        return response()->json(['success' => true, 'message' => 'Producto actualizado']);
    }

    /**
     * DELETE /api/products/{id}
     * Soft delete producto (Admin)
     */
    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return response()->json(['success' => true, 'message' => 'Producto inhabilitado correctamente.']);
    }

    /**
     * POST /api/products/{id}/restore
     * Restaurar producto (Admin)
     */
    public function restore($id)
    {
        $this->productService->restoreProduct($id);

        return response()->json(['success' => true, 'message' => 'Producto restaurado correctamente.']);
    }

    /**
     * POST /api/products/import
     * Importar productos (Admin)
     */
    public function import(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.nombre' => 'required|string',
            'productos.*.sku' => 'required|string',
            'productos.*.precio' => 'required|numeric',
            'productos.*.stock' => 'required|integer',
        ]);

        foreach ($request->productos as $p) {
            $this->productService->createProduct([
                'nombre'       => $p['nombre'],
                'sku'          => $p['sku'],
                'descripcion'  => $p['descripcion'] ?? null,
                'id_categoria' => $p['id_categoria'] ?? null,
                'id_marca'     => $p['id_marca'] ?? null,
                'precio_venta' => $p['precio'],
                'stock_actual' => $p['stock'],
                'imagen_url'   => $p['imagen_url'] ?? null,
                'activo'       => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Productos importados correctamente',
            'total' => count($request->productos)
        ], 201);
    }
}