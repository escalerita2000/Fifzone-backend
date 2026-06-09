<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que existan las categorías referenciadas
        DB::table('categorias')->insertOrIgnore([
            ['id_categoria' => 1, 'nombre' => 'Proteínas',   'slug' => 'proteinas',   'descripcion' => 'Suplementos proteicos', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 2, 'nombre' => 'Pre-entreno', 'slug' => 'pre-entreno', 'descripcion' => 'Energéticos',           'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 3, 'nombre' => 'Vitaminas',   'slug' => 'vitaminas',   'descripcion' => 'Multivitaminas',        'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 4, 'nombre' => 'Accesorios',  'slug' => 'accesorios',  'descripcion' => 'Equipamiento',          'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::statement("SELECT setval('categorias_id_categoria_seq', COALESCE((SELECT MAX(id_categoria)+1 FROM categorias), 1), false)");

        // Asegurar que existan las marcas referenciadas
        DB::table('marcas')->insertOrIgnore([
            ['id_marca' => 1, 'nombre' => 'Optimum Nutrition', 'pais_origen' => 'USA', 'logo_url' => '', 'created_at' => now(), 'updated_at' => now()],
            ['id_marca' => 2, 'nombre' => 'MuscleTech',        'pais_origen' => 'USA', 'logo_url' => '', 'created_at' => now(), 'updated_at' => now()],
            ['id_marca' => 3, 'nombre' => 'Dymatize',          'pais_origen' => 'USA', 'logo_url' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::statement("SELECT setval('marcas_id_marca_seq', COALESCE((SELECT MAX(id_marca)+1 FROM marcas), 1), false)");

        // Actualizar imágenes de productos existentes (columna: imagen_url)
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1579722820903-f38d19b08c2d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'WGS-001'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?auto=format&fit=crop&w=800&q=80' WHERE sku = 'C4-002'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRE-003'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1550572017-edd951b55104?auto=format&fit=crop&w=800&q=80' WHERE sku = 'MVS-004'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1532384748853-8f54a8f476e2?auto=format&fit=crop&w=800&q=80' WHERE sku = 'BCA-005'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GUA-006'");

        // Agregar 4 productos nuevos
        DB::statement("
            INSERT INTO productos
                (nombre, sku, descripcion, id_categoria, id_marca, precio_costo, precio_venta, stock_actual, stock_minimo, imagen_url, activo, created_at, updated_at)
            VALUES
                ('Proteína Isolada Zero', 'ISO-007', 'Proteína isolada sin lactosa, 27g de proteína por porción.',       1, 1, 90000, 145000, 20, 5, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW()),
                ('Omega 3 Fish Oil',      'OMG-008', 'Ácidos grasos esenciales para salud cardiovascular y articular.', 3, 3, 25000,  48000, 30, 5, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW()),
                ('Shaker Pro 700ml',      'SHK-009', 'Vaso mezclador con malla antigrumos y tapa hermética.',           4, 2, 15000,  32000, 25, 5, 'https://images.unsplash.com/photo-1553530666-ba11a90a3dc4?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW()),
                ('Glutamina Pura 300g',   'GLU-010', 'Aminoácido para recuperación muscular y sistema inmune.',         1, 1, 40000,  72000, 12, 5, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW())
        ");
    }

    public function down(): void
    {
        // Revertir los 4 productos nuevos
        DB::statement("DELETE FROM productos WHERE sku IN ('ISO-007', 'OMG-008', 'SHK-009', 'GLU-010')");

        // Limpiar imágenes de los productos existentes
        DB::statement("UPDATE productos SET imagen_url = NULL WHERE sku IN ('WGS-001', 'C4-002', 'CRE-003', 'MVS-004', 'BCA-005', 'GUA-006')");
    }
};
