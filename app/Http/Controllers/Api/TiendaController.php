<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    // GET /api/tienda/productos
    // Devuelve productos con filtros para la página de tienda
    public function productos(Request $request)
    {
        $query = Producto::with(['categoria', 'marca'])
            ->disponibles();  // scope: activo=true y stock>0

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Filtro por marca
        if ($request->filled('marca')) {
            $query->where('id_marca', $request->marca);
        }

        // Búsqueda por nombre
        if ($request->filled('buscar')) {
            $query->where('nombre', 'ilike', '%' . $request->buscar . '%');
        }

        // Ordenar
        $orden = $request->get('orden', 'nombre');
        match ($orden) {
            'precio_asc'  => $query->orderBy('precio_venta', 'asc'),
            'precio_desc' => $query->orderBy('precio_venta', 'desc'),
            'nuevo'       => $query->latest('creado_en'),
            default       => $query->orderBy('nombre'),
        };

        $productos = $query->get()->map(function ($p) {
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
            'success'  => true,
            'total'    => $productos->count(),
            'productos'=> $productos,
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