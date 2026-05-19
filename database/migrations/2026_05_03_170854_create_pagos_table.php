<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id_pago');
            $table->unsignedInteger('id_venta');
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->cascadeOnDelete();
            $table->string('metodo', 30);
            $table->decimal('monto', 14, 2);
            $table->string('referencia', 100)->nullable();
            $table->timestampTz('fecha')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('pagos'); }
};