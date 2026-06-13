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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios (Sincronización con Supabase Auth y Laravel Local)
        $seededUsers = [
            [
                'email' => 'admin@fitzone.com',
                'nombre' => 'Admin FitZone',
                'password' => 'fitzone123',
                'rol' => 'admin',
            ],
            [
                'email' => 'juan@fitzone.com',
                'nombre' => 'Juan Perez',
                'password' => 'user123',
                'rol' => 'user',
            ],
            [
                'email' => 'coach@fitzone.com',
                'nombre' => 'Carlos Coach',
                'password' => 'coach123',
                'rol' => 'coach',
            ],
        ];

        foreach ($seededUsers as $u) {
            $hashed = Hash::make($u['password']);

            // 1.1 Sincronizar con auth.users de Supabase
            $authUser = DB::table('auth.users')->where('email', $u['email'])->first();

            if (!$authUser) {
                $uuid = Str::uuid()->toString();
                DB::table('auth.users')->insert([
                    'instance_id' => '00000000-0000-0000-0000-000000000000',
                    'id' => $uuid,
                    'aud' => 'authenticated',
                    'role' => 'authenticated',
                    'email' => $u['email'],
                    'encrypted_password' => $hashed,
                    'email_confirmed_at' => now(),
                    'raw_app_meta_data' => json_encode(['provider' => 'email', 'providers' => ['email']]),
                    'raw_user_meta_data' => json_encode([
                        'sub' => $uuid,
                        'name' => $u['nombre'],
                        'email' => $u['email'],
                        'email_verified' => true,
                        'phone_verified' => false
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $uuid = $authUser->id;
                DB::table('auth.users')->where('id', $uuid)->update([
                    'encrypted_password' => $hashed,
                    'email_confirmed_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 1.2 Insertar o actualizar en public.usuarios con el mismo hash
            $existingUser = User::where('email', $u['email'])->first();
            if ($existingUser) {
                $existingUser->update([
                    'nombre' => $u['nombre'],
                    'password_hash' => $hashed,
                    'rol' => $u['rol'],
                    'activo' => true,
                ]);
            } else {
                User::create([
                    'nombre' => $u['nombre'],
                    'email' => $u['email'],
                    'password_hash' => $hashed,
                    'rol' => $u['rol'],
                    'activo' => true,
                ]);
            }
        }

        $admin = User::where('email', 'admin@fitzone.com')->first();

        // 2. Categorías
        $cat1 = Categoria::firstOrCreate(['nombre' => 'Proteínas'], ['slug' => 'proteinas', 'descripcion' => 'Suplementos proteicos']);
        $cat2 = Categoria::firstOrCreate(['nombre' => 'Pre-entreno'], ['slug' => 'pre-entreno', 'descripcion' => 'Suplementos energéticos para antes del entrenamiento']);
        $cat3 = Categoria::firstOrCreate(['nombre' => 'Vitaminas'], ['slug' => 'vitaminas', 'descripcion' => 'Complejos multivitamínicos']);
        $cat4 = Categoria::firstOrCreate(['nombre' => 'Accesorios'], ['slug' => 'accesorios', 'descripcion' => 'Equipamiento y ropa deportiva']);

        // 3. Marcas
        $m1 = Marca::firstOrCreate(['nombre' => 'Optimum Nutrition'], ['pais_origen' => 'USA', 'logo_url' => '']);
        $m2 = Marca::firstOrCreate(['nombre' => 'MuscleTech'], ['pais_origen' => 'USA', 'logo_url' => '']);
        $m3 = Marca::firstOrCreate(['nombre' => 'Dymatize'], ['pais_origen' => 'USA', 'logo_url' => '']);

        // 4. Productos
        $p1 = Producto::firstOrCreate(
            ['sku' => 'WGS-001'],
            [
                'nombre'        => 'Whey Gold Standard',
                'descripcion'   => 'Proteína whey de alta calidad con 24g por porción.',
                'id_categoria'  => $cat1->id_categoria,
                'id_marca'      => $m1->id_marca,
                'precio_costo'  => 120000,
                'precio_venta'  => 180000,
                'stock_actual'  => 15,
                'stock_minimo'  => 5,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?auto=format&fit=crop&w=800&q=80',
            ]
        );

        $p2 = Producto::firstOrCreate(
            ['sku' => 'C4-002'],
            [
                'nombre'        => 'Pre-workout C4',
                'descripcion'   => 'Energía explosiva para tus entrenamientos.',
                'id_categoria'  => $cat2->id_categoria,
                'id_marca'      => $m2->id_marca,
                'precio_costo'  => 60000,
                'precio_venta'  => 95000,
                'stock_actual'  => 3,
                'stock_minimo'  => 5,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ]
        );

        $p3 = Producto::firstOrCreate(
            ['sku' => 'CRE-003'],
            [
                'nombre'        => 'Creatina Monohidrato',
                'descripcion'   => 'Aumenta fuerza y rendimiento muscular.',
                'id_categoria'  => $cat1->id_categoria,
                'id_marca'      => $m3->id_marca,
                'precio_costo'  => 50000,
                'precio_venta'  => 75000,
                'stock_actual'  => 10,
                'stock_minimo'  => 5,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80',
            ]
        );

        $p4 = Producto::firstOrCreate(
            ['sku' => 'MVS-004'],
            [
                'nombre'        => 'Multivitamínico Sport',
                'descripcion'   => 'Vitaminas y minerales para deportistas.',
                'id_categoria'  => $cat3->id_categoria,
                'id_marca'      => $m1->id_marca,
                'precio_costo'  => 35000,
                'precio_venta'  => 55000,
                'stock_actual'  => 20,
                'stock_minimo'  => 5,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?auto=format&fit=crop&w=800&q=80',
            ]
        );

        $p5 = Producto::firstOrCreate(
            ['sku' => 'BCA-005'],
            [
                'nombre'        => 'BCAA 2:1:1',
                'descripcion'   => 'Aminoácidos esenciales para recuperación muscular.',
                'id_categoria'  => $cat1->id_categoria,
                'id_marca'      => $m2->id_marca,
                'precio_costo'  => 55000,
                'precio_venta'  => 88000,
                'stock_actual'  => 8,
                'stock_minimo'  => 5,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?auto=format&fit=crop&w=800&q=80',
            ]
        );

        $p6 = Producto::firstOrCreate(
            ['sku' => 'GUA-006'],
            [
                'nombre'        => 'Guantes de entreno',
                'descripcion'   => 'Guantes con grip reforzado para pesas.',
                'id_categoria'  => $cat4->id_categoria,
                'id_marca'      => $m3->id_marca,
                'precio_costo'  => 22000,
                'precio_venta'  => 45000,
                'stock_actual'  => 12,
                'stock_minimo'  => 3,
                'activo'        => true,
                'imagen_url'    => 'https://images.unsplash.com/photo-1605296867304-46d5465a25f1?auto=format&fit=crop&w=800&q=80',
            ]
        );

        // 5. Precios de Lista adicionales
        if ($p1->wasRecentlyCreated) {
            $p1->listaPrecios()->createMany([
                ['tipo_cliente' => 'mayorista', 'precio' => 150000, 'activo' => true],
                ['tipo_cliente' => 'minorista', 'precio' => 180000, 'activo' => true],
            ]);
        }
        if ($p2->wasRecentlyCreated) {
            $p2->listaPrecios()->createMany([
                ['tipo_cliente' => 'mayorista', 'precio' => 80000, 'activo' => true],
                ['tipo_cliente' => 'minorista', 'precio' => 95000, 'activo' => true],
            ]);
        }

        // 6. Venta de ejemplo
        $subtotal = ($p1->precio_venta * 2) + ($p4->precio_venta * 1);
        $total    = $subtotal;
        $venta = Venta::firstOrCreate(
            ['numero_factura'  => 'FAC-2026001'],
            [
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
            ]
        );

        if ($venta->wasRecentlyCreated) {
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

        // Llamar a AdvancedSeeder para poblar datos de prueba masivos
        $this->call(AdvancedSeeder::class);
    }
}