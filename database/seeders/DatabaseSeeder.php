<?php
namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'     => 'Admin FitZone',
            'email'    => 'admin@fitzone.com',
            'password' => Hash::make('fitzone2024'),
            'role'     => 'admin',
        ]);

        $p1 = Product::create(['name' => 'Camiseta Dry-Fit Pro',   'category' => 'Camisetas',  'cost_price' => 18000, 'internal_price' => 28000, 'sale_price' => 45000, 'stock' => 42]);
        $p2 = Product::create(['name' => 'Leggins Compresión Elite','category' => 'Pantalones', 'cost_price' => 32000, 'internal_price' => 48000, 'sale_price' => 79000, 'stock' => 28]);
        $p3 = Product::create(['name' => 'Sudadera Hoodie Sport',   'category' => 'Sudaderas',  'cost_price' => 45000, 'internal_price' => 62000, 'sale_price' => 95000, 'stock' => 15]);
        $p4 = Product::create(['name' => 'Short Running Ultralight','category' => 'Pantalones', 'cost_price' => 22000, 'internal_price' => 34000, 'sale_price' => 55000, 'stock' => 36]);
        $p5 = Product::create(['name' => 'Top Deportivo Mujer',     'category' => 'Tops',       'cost_price' => 20000, 'internal_price' => 30000, 'sale_price' => 48000, 'stock' => 5]);

        // Venta de ejemplo
        $sale = Sale::create([
            'customer' => 'Carlos Ramírez', 'type' => 'external',
            'total' => 145000, 'profit' => 84000,
            'status' => 'completed', 'registered_by' => $admin->id,
        ]);
        $sale->items()->createMany([
            ['product_id' => $p1->id, 'product_name' => $p1->name, 'quantity' => 2, 'unit_price' => 45000, 'subtotal' => 90000],
            ['product_id' => $p4->id, 'product_name' => $p4->name, 'quantity' => 1, 'unit_price' => 55000, 'subtotal' => 55000],
        ]);
    }
}