<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table      = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre', 'sku', 'descripcion', 'id_categoria',
        'id_marca', 'precio_costo', 'precio_venta',
        'stock_actual', 'stock_minimo', 'imagen_url', 'activo',
        'deleted_at', 'deleted_by', 'is_deleted',
    ];

    protected $casts = [
        'precio_costo'  => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'stock_actual'  => 'integer',
        'activo'        => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function listaPrecios()
    {
        return $this->hasMany(ListaPrecio::class, 'id_producto', 'id_producto');
    }

    // Precio para un tipo de cliente específico
    public function precioParaCliente(string $tipo = 'minorista'): float
    {
        $precio = $this->listaPrecios()
            ->where('tipo_cliente', $tipo)
            ->where('activo', true)
            ->whereNull('vigencia_hasta')
            ->orWhereDate('vigencia_hasta', '>=', now())
            ->first();

        return $precio ? (float) $precio->precio : (float) $this->precio_venta;
    }

    // Estado del stock
    public function getEstadoStockAttribute(): string
    {
        if ($this->stock_actual <= 0)                   return 'agotado';
        if ($this->stock_actual <= $this->stock_minimo) return 'stock bajo';
        return 'disponible';
    }

    // Margen de ganancia
    public function getMargenAttribute(): float
    {
        if ($this->precio_venta == 0) return 0;
        return round((($this->precio_venta - $this->precio_costo) / $this->precio_venta) * 100, 2);
    }

    // Scope: solo activos con stock
    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)->where('stock_actual', '>', 0);
    }
}