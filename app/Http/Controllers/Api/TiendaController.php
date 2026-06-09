<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Services\ProductService;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // GET /api/tienda/productos
    // Devuelve productos con filtros para la página de tienda
    public function productos(Request $request)
    {
        $filters = [
            'search'       => $request->input('buscar') ?? $request->input('search'),
            'categoria_id' => $request->input('categoria') ?? $request->input('categoria_id'),
            'marca_id'     => $request->input('marca') ?? $request->input('marca_id'),
            'orden'        => $request->input('orden', 'nombre'),
            'activo'       => true,
        ];

        $perPage = $request->input('per_page', 9); // default 9 products per page for public Tienda

        if ($perPage > 0) {
            $paginated = $this->productService->listProducts($filters, $perPage);
            $items = $paginated->items();
            $total = $paginated->total();
            $currentPage = $paginated->currentPage();
            $lastPage = $paginated->lastPage();
        } else {
            $items = $this->productService->listProducts($filters, 0);
            $total = count($items);
            $currentPage = 1;
            $lastPage = 1;
        }

        $productos = collect($items)->map(function ($p) {
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
                'imagen_url'    => $p->imagen_url,
            ];
        });

        return response()->json([
            'success'      => true,
            'total'        => $total,
            'current_page' => $currentPage,
            'last_page'    => $lastPage,
            'per_page'     => (int) $perPage,
            'productos'    => $productos,
        ]);
    }

    // GET /api/tienda/productos/{id}
    public function producto(int $id)
    {
        $p = Producto::with(['categoria', 'marca', 'listaPrecios'])
            ->where('id_producto', $id)
            ->where('activo', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'producto' => [
                'id'           => $p->id_producto,
                'nombre'       => $p->nombre,
                'sku'          => $p->sku,
                'descripcion'  => $p->descripcion,
                'categoria'    => $p->categoria?->nombre,
                'marca'        => $p->marca?->nombre,
                'precio'       => (float) $p->precio_venta,
                'stock'        => $p->stock_actual,
                'estado_stock' => $p->estado_stock,
                'imagen_url'   => $p->imagen_url,
                'precios'      => $p->listaPrecios->map(fn($lp) => [
                    'tipo'   => $lp->tipo_cliente,
                    'precio' => (float) $lp->precio,
                ]),
            ],
        ]);
    }

    // GET /api/tienda/categorias
    public function categorias()
    {
        $categorias = Categoria::where('activo', true)
            ->withCount(['productos' => fn($q) => $q->where('activo', true)])
            ->orderBy('nombre')
            ->get(['id_categoria', 'nombre', 'slug']);

        return response()->json([
            'success'    => true,
            'categorias' => $categorias,
        ]);
    }

    // GET /api/tienda/marcas
    public function marcas()
    {
        $marcas = Marca::where('activo', true)
            ->withCount(['productos' => fn($q) => $q->where('activo', true)])
            ->orderBy('nombre')
            ->get(['id_marca', 'nombre', 'logo_url']);

        return response()->json([
            'success' => true,
            'marcas'  => $marcas,
        ]);
    }
}