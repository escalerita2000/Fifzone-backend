<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'nombre',
        'nit',
        'contacto_nombre',
        'telefono',
        'email',
        'direccion',
        'activo',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
