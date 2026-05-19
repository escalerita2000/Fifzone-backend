<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->increments('id_proveedor');
            $table->string('nombre', 150);
            $table->string('nit', 30)->unique()->nullable();
            $table->string('representante', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('condiciones_pago', 80)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('proveedores'); }
};