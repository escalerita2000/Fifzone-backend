<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id_cliente');
            $table->string('nombre', 150);
            $table->string('tipo_documento', 20)->default('CC');
            $table->string('documento', 30)->unique();
            $table->string('email', 150)->unique()->nullable();
            $table->string('telefono', 30)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('tipo', 20)->default('minorista');
            $table->integer('puntos_acumulados')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('clientes'); }
};