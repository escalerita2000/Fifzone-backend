<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'description', 'image',
        'cost_price', 'internal_price', 'sale_price', 'stock',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'     => 'decimal:2',
            'internal_price' => 'decimal:2',
            'sale_price'     => 'decimal:2',
            'stock'          => 'integer',
        ];
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Devuelve: 'agotado' | 'bajo' | 'disponible'
    public function getStockStatusAttribute(): string
    {
        if ($this->stock === 0)  return 'agotado';
        if ($this->stock <= 5)   return 'bajo';
        return 'disponible';
    }

    public function decrementStock(int $qty): void
    {
        if ($this->stock < $qty) {
            throw new \Exception(
                "Stock insuficiente para '{$this->name}'. Disponible: {$this->stock}"
            );
        }
        $this->decrement('stock', $qty);
    }
}