<?php

namespace App\Repositories;

use App\Models\Producto;

class ProductRepository
{
    public function getFilteredProducts(array $filters, int $perPage = 10)
    {
        $query = Producto::with(['categoria', 'marca']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('nombre', "%{$search}%")
                  ->orWhereLike('sku', "%{$search}%");
            });
        }

        if (!empty($filters['categoria_id'])) {
            $query->where('id_categoria', $filters['categoria_id']);
        }

        if (!empty($filters['marca_id'])) {
            $query->where('id_marca', $filters['marca_id']);
        }

        if (isset($filters['activo'])) {
            $query->where('activo', (bool) $filters['activo']);
        }

        if (isset($filters['deleted'])) {
            if ($filters['deleted'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['deleted'] === 'with') {
                $query->withTrashed();
            }
        } else {
            // Por defecto ocultar eliminados
            $query->withoutTrashed();
        }

        $orden = $filters['orden'] ?? 'nombre';
        match ($orden) {
            'precio_asc'  => $query->orderBy('precio_venta', 'asc'),
            'precio_desc' => $query->orderBy('precio_venta', 'desc'),
            'nuevo'       => $query->latest('created_at'),
            default       => $query->orderBy('nombre', 'asc'),
        };

        // Si se pide paginación, retornar paginado. Si no, retornar todos.
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(int $id, bool $withTrashed = false): Producto
    {
        $query = Producto::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Producto
    {
        return Producto::create($data);
    }

    public function update(int $id, array $data): Producto
    {
        $product = $this->findById($id, true);
        $product->update($data);
        return $product;
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $product = $this->findById($id);
        $product->update([
            'is_deleted' => true,
            'deleted_by' => $deletedBy ?? (auth()->check() ? auth()->id() : null)
        ]);
        return $product->delete();
    }

    public function restore(int $id): bool
    {
        $product = $this->findById($id, true);
        $product->update([
            'is_deleted' => false,
            'deleted_by' => null
        ]);
        return $product->restore();
    }
}
