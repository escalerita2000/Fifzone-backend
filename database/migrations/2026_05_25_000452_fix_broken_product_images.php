<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1434608519344-49d77a124f7a?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRD-011'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1571019613576-2b22c76fd955?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GLU-010'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ZMA-012'");
    }

    public function down(): void
    {
        // Revertir a las URLs anteriores
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1434608519344-49d77a124f7a?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRD-011'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1544991875-5dc1b05f5b9f?auto=format&fit=crop&w=800&q=80' WHERE sku = 'GLU-010'");
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1559181567-c3190bfbf449?auto=format&fit=crop&w=800&q=80' WHERE sku = 'ZMA-012'");
    }
};
