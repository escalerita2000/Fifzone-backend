<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    public $timestamps    = false;
    protected $table      = 'lista_precios';
    protected $primaryKey = 'id_precio';

    protected $fillable = [
        'id_producto', 'tipo_cliente', 'precio',
        'vigencia_desde', 'vigencia_hasta', 'activo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}