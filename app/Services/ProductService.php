<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Services\AuditService;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listProducts(array $filters, int $perPage = 10)
    {
        return $this->repository->getFilteredProducts($filters, $perPage);
    }

    public function getProduct(int $id)
    {
        return $this->repository->findById($id);
    }

    public function createProduct(array $data)
    {
        $data['precio_costo'] = $data['precio_costo'] ?? ($data['precio_venta'] * 0.65);
        $data['stock_minimo'] = $data['stock_minimo'] ?? 5;
        $data['activo'] = $data['activo'] ?? true;
        
        $product = $this->repository->create($data);
        AuditService::log('crear', 'producto', $product->id_producto, "Producto '{$product->nombre}' creado.");
        return $product;
    }

    public function updateProduct(int $id, array $data)
    {
        if (isset($data['precio_venta']) && !isset($data['precio_costo'])) {
            $data['precio_costo'] = $data['precio_venta'] * 0.65;
        }

        $product = $this->repository->update($id, $data);
        AuditService::log('actualizar', 'producto', $id, "Producto '{$product->nombre}' actualizado.");
        return $product;
    }

    public function deleteProduct(int $id)
    {
        $this->repository->delete($id);
        AuditService::log('eliminar', 'producto', $id, "Producto con ID {$id} eliminado lógicamente.");
    }

    public function restoreProduct(int $id)
    {
        $this->repository->restore($id);
        AuditService::log('restaurar', 'producto', $id, "Producto con ID {$id} restaurado.");
    }
}
