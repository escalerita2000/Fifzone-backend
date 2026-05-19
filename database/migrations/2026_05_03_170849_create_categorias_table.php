<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categorias', function (Blueprint $table) {
            $table->increments('id_categoria');
            $table->string('nombre', 100);
            $table->string('slug', 110)->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('id_padre')->nullable();
            $table->foreign('id_padre')->references('id_categoria')->on('categorias')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('categorias'); }
};