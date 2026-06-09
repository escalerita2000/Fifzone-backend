<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membresia extends Model
{
    use SoftDeletes;

    protected $table = 'membresias';
    protected $primaryKey = 'id_membresia';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_dias',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_dias' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function compras()
    {
        return $this->hasMany(CompraMembresia::class, 'id_membresia', 'id_membresia');
    }
}
