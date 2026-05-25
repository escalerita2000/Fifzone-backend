<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')->where('sku', 'god')->delete();
    }

    public function down(): void
    {
        // No se puede revertir un borrado sin los datos originales
    }
};
