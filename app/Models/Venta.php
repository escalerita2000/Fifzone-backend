<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'numero_factura',
        'id_cliente',
        'id_usuario',
        'fecha',
        'subtotal',
        'descuento_pct',
        'descuento_valor',
        'impuesto_pct',
        'impuesto_valor',
        'total',
        'estado',
        'canal',
        'notas',
        'reference',
        'items',
    ];

    protected $casts = [
        'fecha'           => 'datetime',
        'subtotal'        => 'decimal:2',
        'descuento_pct'   => 'decimal:2',
        'descuento_valor' => 'decimal:2',
        'impuesto_pct'    => 'decimal:2',
        'impuesto_valor'  => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(VentaItem::class, 'id_venta', 'id_venta');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
