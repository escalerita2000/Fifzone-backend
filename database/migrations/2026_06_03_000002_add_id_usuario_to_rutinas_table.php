<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario')->nullable()->after('id_rutina');
            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->dropColumn('id_usuario');
        });
    }
};
