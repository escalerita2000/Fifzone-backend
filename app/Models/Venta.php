<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

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
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'fecha'           => 'datetime',
        'subtotal'        => 'decimal:2',
        'descuento_pct'   => 'decimal:2',
        'descuento_valor' => 'decimal:2',
        'impuesto_pct'    => 'decimal:2',
        'impuesto_valor'  => 'decimal:2',
        'total'           => 'decimal:2',
        'is_deleted'      => 'boolean',
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
