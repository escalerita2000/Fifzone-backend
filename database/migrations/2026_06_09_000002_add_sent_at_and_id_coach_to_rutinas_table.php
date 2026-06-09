<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            if (!Schema::hasColumn('rutinas', 'id_coach')) {
                $table->unsignedInteger('id_coach')->nullable();
                $table->foreign('id_coach')->references('id_usuario')->on('usuarios')->nullOnDelete();
            }
            if (!Schema::hasColumn('rutinas', 'sent_at')) {
                $table->timestampTz('sent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_coach']);
            } catch (\Exception $e) {}
            $table->dropColumn(['id_coach', 'sent_at']);
        });
    }
};
