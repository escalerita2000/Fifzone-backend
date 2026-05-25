<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1709976142888-6dc0ed1ed78c?q=80&w=870&auto=format&fit=crop' WHERE sku = 'BCA-005'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1724160167551-2ffc3d7ca809?q=80&w=870&auto=format&fit=crop' WHERE sku = 'CRE-003'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1520334298038-4182dac472e8?q=80&w=466&auto=format&fit=crop' WHERE sku = 'CRD-011'");
        DB::statement("UPDATE productos SET imagen_url = 'https://plus.unsplash.com/premium_photo-1778938141663-aa4006fbead4?q=80&w=871&auto=format&fit=crop' WHERE sku = 'GLU-010'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1557127972-1c446ea89ea5?q=80&w=387&auto=format&fit=crop' WHERE sku = 'GUA-006'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1665758574784-7e549d53c7f7?q=80&w=580&auto=format&fit=crop' WHERE sku = 'MVS-004'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1662673145204-c843cb1d6ff0?q=80&w=387&auto=format&fit=crop' WHERE sku = 'OMG-008'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1693996047008-1b6210099be1?q=80&w=870&auto=format&fit=crop' WHERE sku = 'C4-002'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1693996045300-521e9d08cabc?q=80&w=870&auto=format&fit=crop' WHERE sku = 'ISO-007'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1680265346124-ba1b82b19d5f?q=80&w=447&auto=format&fit=crop' WHERE sku = 'SHK-009'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1775199603318-7f8a9a63b40d?q=80&w=870&auto=format&fit=crop' WHERE sku = 'WGS-001'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1771530072228-56adc093083f?q=80&w=1034&auto=format&fit=crop' WHERE sku = 'ZMA-012'");
    }

    public function down(): void
    {
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1709976142888-6dc0ed1ed78c?auto=format&fit=crop&w=800&q=80' WHERE sku = 'BCA-005'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRE-003'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRD-011'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613576-2b22c76fd955?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GLU-010'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GUA-006'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1505576399279-565b52d4ac71?auto=format&fit=crop&w=800&q=80' WHERE sku = 'MVS-004'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80' WHERE sku = 'OMG-008'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?auto=format&fit=crop&w=800&q=80' WHERE sku = 'C4-002'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ISO-007'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1548438294-1ad5d5f4f063?auto=format&fit=crop&w=800&q=80' WHERE sku = 'SHK-009'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80' WHERE sku = 'WGS-001'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ZMA-012'");
    }
};
