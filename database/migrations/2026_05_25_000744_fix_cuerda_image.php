<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRD-011'");
    }

    public function down(): void
    {
        DB::statement("UPDATE productos SET imagen_url = 'https://images.unsplash.com/photo-1434608519344-49d77a124f7a?auto=format&fit=crop&w=800&q=80' WHERE sku = 'CRD-011'");
    }
};
