<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $table = 'compras';
    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'id_proveedor',
        'id_usuario',
        'fecha_orden',
        'fecha_recibido',
        'numero_factura',
        'subtotal',
        'impuesto_valor',
        'total',
        'estado',
        'notas',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'fecha_orden' => 'datetime',
        'fecha_recibido' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuesto_valor' => 'decimal:2',
        'total' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
