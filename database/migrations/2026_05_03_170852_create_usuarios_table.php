<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombre', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('rol', 30)->default('cajero');
            $table->boolean('activo')->default(true);
            $table->timestampTz('ultimo_acceso')->nullable();
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('usuarios'); }
};