<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rutinas')) {
            Schema::create('rutinas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 255);
                $table->string('nivel', 100)->nullable();
                $table->string('duracion', 100)->nullable();
                $table->text('imagen')->nullable();
                $table->jsonb('ejercicios')->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->foreign('id_usuario')
                      ->references('id_usuario')
                      ->on('usuarios')
                      ->nullOnDelete();
            });
        } else {
            Schema::table('rutinas', function (Blueprint $table) {
                if (!Schema::hasColumn('rutinas', 'id_usuario')) {
                    $table->unsignedInteger('id_usuario')->nullable();
                    $table->foreign('id_usuario')
                          ->references('id_usuario')
                          ->on('usuarios')
                          ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rutinas')) {
            Schema::table('rutinas', function (Blueprint $table) {
                try {
                    $table->dropForeign(['id_usuario']);
                } catch (\Exception $e) {}
                try {
                    $table->dropColumn('id_usuario');
                } catch (\Exception $e) {}
            });
        }
    }
};
