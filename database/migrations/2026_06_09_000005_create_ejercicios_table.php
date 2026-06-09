<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ejercicios')) {
            Schema::create('ejercicios', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 255)->unique();
                $table->string('grupo_muscular', 100);
                $table->string('dificultad', 100);
                $table->string('equipamiento', 100);
                $table->text('descripcion')->nullable();
                $table->text('imagen_url')->nullable();
                
                // Soft deletes columns
                $table->boolean('is_deleted')->default(false);
                $table->unsignedInteger('deleted_by')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                $table->foreign('deleted_by')->references('id_usuario')->on('usuarios')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ejercicios');
    }
};
