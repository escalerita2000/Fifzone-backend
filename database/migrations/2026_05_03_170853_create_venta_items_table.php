<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('venta_items', function (Blueprint $table) {
            $table->increments('id_item');
            $table->unsignedInteger('id_venta');
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->cascadeOnDelete();
            $table->unsignedInteger('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 14, 2);
            $table->decimal('descuento_item', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
        });
    }
    public function down(): void { Schema::dropIfExists('venta_items'); }
};