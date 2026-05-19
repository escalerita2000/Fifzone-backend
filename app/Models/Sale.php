<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer', 'type', 'total', 'profit',
        'status', 'registered_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total'  => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}