<?php

namespace App\Repositories;

use App\Models\Membresia;
use App\Models\CompraMembresia;

class MembershipRepository
{
    public function findByName($name)
    {
        return Membresia::where('nombre', strtoupper($name))->first();
    }

    public function findById($id)
    {
        return Membresia::find($id);
    }

    public function createPurchase(array $data)
    {
        return CompraMembresia::create($data);
    }

    public function getPurchaseByReference($reference)
    {
        return CompraMembresia::where('referencia_pago', $reference)->first();
    }
}
