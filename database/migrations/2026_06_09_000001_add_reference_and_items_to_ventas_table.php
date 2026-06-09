<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'reference')) {
                $table->string('reference', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('ventas', 'items')) {
                $table->jsonb('items')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['reference', 'items']);
        });
    }
};
