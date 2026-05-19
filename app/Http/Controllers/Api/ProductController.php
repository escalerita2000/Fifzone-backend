<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category'))     $query->where('category', $request->category);
        if ($request->boolean('low_stock'))   $query->where('stock', '<=', 5);
        if ($request->filled('search'))       $query->where('name', 'like', '%'.$request->search.'%');

        $products = $query->orderBy('name')->get()->map(function ($p) {
            $p->stock_status = $p->stock_status;
            return $p;
        });

        return response()->json(['success' => true, 'total' => $products->count(), 'products' => $products]);
    }

    public function show(Product $product)
    {
        $product->stock_status = $product->stock_status;
        return response()->json(['success' => true, 'product' => $product]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'category'       => 'required|string|max:80',
            'description'    => 'nullable|string',
            'image'          => 'nullable|url',
            'cost_price'     => 'required|numeric|min:0',
            'internal_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
        ]);

        $product = Product::create($data);
        return response()->json(['success' => true, 'message' => 'Producto creado.', 'product' => $product], 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:150',
            'category'       => 'sometimes|string|max:80',
            'description'    => 'nullable|string',
            'image'          => 'nullable|url',
            'cost_price'     => 'sometimes|numeric|min:0',
            'internal_price' => 'sometimes|numeric|min:0',
            'sale_price'     => 'sometimes|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
        ]);
        $product->update($data);
        return response()->json(['success' => true, 'message' => 'Producto actualizado.', 'product' => $product->fresh()]);
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();
        return response()->json(['success' => true, 'message' => "Producto \"{$name}\" eliminado."]);
    }

    public function stockSummary()
    {
        $products = Product::all();
        $items = $products->map(fn($p) => [
            'id' => $p->id, 'name' => $p->name, 'category' => $p->category,
            'stock' => $p->stock, 'status' => $p->stock_status,
            'stock_value' => $p->stock * $p->cost_price,
        ]);
        return response()->json([
            'success'               => true,
            'total_products'        => $products->count(),
            'out_of_stock'          => $products->where('stock', 0)->count(),
            'low_stock'             => $products->whereBetween('stock', [1, 5])->count(),
            'total_inventory_value' => $products->sum(fn($p) => $p->stock * $p->cost_price),
            'items'                 => $items,
        ]);
    }
}