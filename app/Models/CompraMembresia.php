<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraMembresia extends Model
{
    use SoftDeletes;

    protected $table = 'compras_membresias';
    protected $primaryKey = 'id_compra_membresia';

    protected $fillable = [
        'id_usuario',
        'id_membresia',
        'estado_pago',
        'fecha_compra',
        'referencia_pago',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'fecha_compra' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'id_membresia', 'id_membresia');
    }
}
