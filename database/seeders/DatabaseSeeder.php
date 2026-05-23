<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios
        $admin = User::create([
            'nombre'        => 'Admin FitZone',
            'email'         => 'admin@fitzone.com',
            'password_hash' => Hash::make('fitzone2024'),
            'rol'           => 'admin',
            'activo'        => true,
        ]);

        $usuario = User::create([
            'nombre'        => 'Juan Perez',
            'email'         => 'juan@fitzone.com',
            'password_hash' => Hash::make('fitzone2024'),
            'rol'           => 'user',
            'activo'        => true,
        ]);

        // 2. Categorías
        $cat1 = Categoria::create(['nombre' => 'Proteínas',   'slug' => 'proteinas',   'descripcion' => 'Suplementos proteicos']);
        $cat2 = Categoria::create(['nombre' => 'Pre-entreno', 'slug' => 'pre-entreno', 'descripcion' => 'Suplementos energéticos para antes del entrenamiento']);
        $cat3 = Categoria::create(['nombre' => 'Vitaminas',   'slug' => 'vitaminas',   'descripcion' => 'Complejos multivitamínicos']);
        $cat4 = Categoria::create(['nombre' => 'Accesorios',  'slug' => 'accesorios',  'descripcion' => 'Equipamiento y ropa deportiva']);

        // 3. Marcas
        $m1 = Marca::create(['nombre' => 'Optimum Nutrition', 'pais_origen' => 'USA', 'logo_url' => '']);
        $m2 = Marca::create(['nombre' => 'MuscleTech',        'pais_origen' => 'USA', 'logo_url' => '']);
        $m3 = Marca::create(['nombre' => 'Dymatize',          'pais_origen' => 'USA', 'logo_url' => '']);

        // 4. Productos
        $p1 = Producto::create([
            'nombre'        => 'Whey Gold Standard',
            'sku'           => 'WGS-001',
            'descripcion'   => 'Proteína whey de alta calidad con 24g por porción.',
            'id_categoria'  => $cat1->id_categoria,
            'id_marca'      => $m1->id_marca,
            'precio_costo'  => 120000,
            'precio_venta'  => 180000,
            'stock_actual'  => 15,
            'stock_minimo'  => 5,
            'activo'        => true,
        ]);

        $p2 = Producto::create([
            'nombre'        => 'Pre-workout C4',
            'sku'           => 'C4-002',
            'descripcion'   => 'Energía explosiva para tus entrenamientos.',
            'id_categoria'  => $cat2->id_categoria,
            'id_marca'      => $m2->id_marca,
            'precio_costo'  => 60000,
            'precio_venta'  => 95000,
            'stock_actual'  => 3,
            'stock_minimo'  => 5,
            'activo'        => true,
        ]);

        $p3 = Producto::create([
            'nombre'        => 'Creatina Monohidrato',
            'sku'           => 'CRE-003',
            'descripcion'   => 'Aumenta fuerza y rendimiento muscular.',
            'id_categoria'  => $cat1->id_categoria,
            'id_marca'      => $m3->id_marca,
            'precio_costo'  => 50000,
            'precio_venta'  => 75000,
            'stock_actual'  => 10,
            'stock_minimo'  => 5,
            'activo'        => true,
        ]);

        $p4 = Producto::create([
            'nombre'        => 'Multivitamínico Sport',
            'sku'           => 'MVS-004',
            'descripcion'   => 'Vitaminas y minerales para deportistas.',
            'id_categoria'  => $cat3->id_categoria,
            'id_marca'      => $m1->id_marca,
            'precio_costo'  => 35000,
            'precio_venta'  => 55000,
            'stock_actual'  => 20,
            'stock_minimo'  => 5,
            'activo'        => true,
        ]);

        $p5 = Producto::create([
            'nombre'        => 'BCAA 2:1:1',
            'sku'           => 'BCA-005',
            'descripcion'   => 'Aminoácidos esenciales para recuperación muscular.',
            'id_categoria'  => $cat1->id_categoria,
            'id_marca'      => $m2->id_marca,
            'precio_costo'  => 55000,
            'precio_venta'  => 88000,
            'stock_actual'  => 8,
            'stock_minimo'  => 5,
            'activo'        => true,
        ]);

        $p6 = Producto::create([
            'nombre'        => 'Guantes de entreno',
            'sku'           => 'GUA-006',
            'descripcion'   => 'Guantes con grip reforzado para pesas.',
            'id_categoria'  => $cat4->id_categoria,
            'id_marca'      => $m3->id_marca,
            'precio_costo'  => 22000,
            'precio_venta'  => 45000,
            'stock_actual'  => 12,
            'stock_minimo'  => 3,
            'activo'        => true,
        ]);

        // 5. Precios de Lista adicionales
        $p1->listaPrecios()->createMany([
            ['tipo_cliente' => 'mayorista', 'precio' => 150000, 'activo' => true],
            ['tipo_cliente' => 'minorista', 'precio' => 180000, 'activo' => true],
        ]);
        $p2->listaPrecios()->createMany([
            ['tipo_cliente' => 'mayorista', 'precio' => 80000, 'activo' => true],
            ['tipo_cliente' => 'minorista', 'precio' => 95000, 'activo' => true],
        ]);

        // 6. Venta de ejemplo
        $subtotal = ($p1->precio_venta * 2) + ($p4->precio_venta * 1);
        $total    = $subtotal;
        $venta = Venta::create([
            'numero_factura'  => 'FAC-2026001',
            'id_cliente'      => null,
            'id_usuario'      => $admin->id_usuario,
            'fecha'           => now(),
            'subtotal'        => $subtotal,
            'descuento_pct'   => 0,
            'descuento_valor' => 0,
            'impuesto_pct'    => 0,
            'impuesto_valor'  => 0,
            'total'           => $total,
            'estado'          => 'completada',
            'canal'           => 'web',
            'notas'           => 'Venta inicial de prueba',
        ]);

        $venta->items()->createMany([
            [
                'id_producto'     => $p1->id_producto,
                'cantidad'        => 2,
                'precio_unitario' => $p1->precio_venta,
                'subtotal'        => $p1->precio_venta * 2,
            ],
            [
                'id_producto'     => $p4->id_producto,
                'cantidad'        => 1,
                'precio_unitario' => $p4->precio_venta,
                'subtotal'        => $p4->precio_venta * 1,
            ],
        ]);

        // Descontar inventario
        $p1->decrement('stock_actual', 2);
        $p4->decrement('stock_actual', 1);
    }
}