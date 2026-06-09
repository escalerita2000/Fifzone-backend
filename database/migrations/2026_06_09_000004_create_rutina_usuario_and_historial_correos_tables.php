<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rutina_usuario')) {
            Schema::create('rutina_usuario', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_usuario');
                $table->unsignedInteger('id_rutina');
                $table->timestampTz('asignado_at')->useCurrent();
                $table->boolean('activo')->default(true);
                $table->timestampsTz();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
                $table->foreign('id_rutina')->references('id')->on('rutinas')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('historial_correos')) {
            Schema::create('historial_correos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->unsignedInteger('id_rutina')->nullable();
                $table->string('email');
                $table->string('asunto');
                $table->text('cuerpo');
                $table->string('estado')->default('success');
                $table->timestampTz('sent_at')->useCurrent();
                $table->timestampsTz();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
                $table->foreign('id_rutina')->references('id')->on('rutinas')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_correos');
        Schema::dropIfExists('rutina_usuario');
    }
};
