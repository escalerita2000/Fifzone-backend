<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizar imágenes con URLs reales de fitness de Unsplash
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80' WHERE sku = 'WGS-001'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?auto=format&fit=crop&w=800&q=80' WHERE sku = 'C4-002'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRE-003'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1505576399279-565b52d4ac71?auto=format&fit=crop&w=800&q=80' WHERE sku = 'MVS-004'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1532384748853-8f54a8f476e2?auto=format&fit=crop&w=800&q=80' WHERE sku = 'BCA-005'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GUA-006'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ISO-007'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80' WHERE sku = 'OMG-008'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1548438294-1ad5d5f4f063?auto=format&fit=crop&w=800&q=80' WHERE sku = 'SHK-009'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1544991875-5dc1b05f5b9f?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GLU-010'");

        // Agregar 2 productos nuevos para llegar a 12 en total
        DB::statement("
            INSERT INTO productos
                (nombre, sku, descripcion, id_categoria, id_marca, precio_costo, precio_venta, stock_actual, stock_minimo, imagen_url, activo, created_at, updated_at)
            VALUES
                ('Cuerda para Saltar Speed', 'CRD-011', 'Cuerda de velocidad profesional con mangos ergonómicos.', 4, 2, 18000, 35000, 20, 5, 'https://images.unsplash.com/photo-1434608519344-49d77a124f7a?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW()),
                ('ZMA Recovery',             'ZMA-012', 'Zinc, Magnesio y Vitamina B6 para recuperación nocturna.', 3, 3, 28000, 52000, 15, 5, 'https://images.unsplash.com/photo-1559181567-c3190bfbf449?auto=format&fit=crop&w=800&q=80', true, NOW(), NOW())
        ");
    }

    public function down(): void
    {
        // Eliminar los 2 productos nuevos
        DB::statement("DELETE FROM productos WHERE sku IN ('CRD-011', 'ZMA-012')");

        // Revertir imágenes a las URLs anteriores (migración previa)
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1579722820903-f38d19b08c2d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'WGS-001'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?auto=format&fit=crop&w=800&q=80' WHERE sku = 'C4-002'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRE-003'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1550572017-edd951b55104?auto=format&fit=crop&w=800&q=80' WHERE sku = 'MVS-004'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1532384748853-8f54a8f476e2?auto=format&fit=crop&w=800&q=80' WHERE sku = 'BCA-005'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GUA-006'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ISO-007'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=800&q=80' WHERE sku = 'OMG-008'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1553530666-ba11a90a3dc4?auto=format&fit=crop&w=800&q=80' WHERE sku = 'SHK-009'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GLU-010'");
    }
};
