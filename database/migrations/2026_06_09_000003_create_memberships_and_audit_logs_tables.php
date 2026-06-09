<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla membresias
        if (!Schema::hasTable('membresias')) {
            Schema::create('membresias', function (Blueprint $table) {
                $table->increments('id_membresia');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 14, 2);
                $table->integer('duracion_dias')->default(30);
                $table->timestampTz('deleted_at')->nullable();
                $table->unsignedInteger('deleted_by')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestampsTz();

                $table->foreign('deleted_by')->references('id_usuario')->on('usuarios')->nullOnDelete();
            });

            // Sembrar membresías por defecto
            DB::table('membresias')->insert([
                [
                    'id_membresia' => 1,
                    'nombre' => 'FIT',
                    'descripcion' => '1 sede, clases grupales, zona cardio y pesas, vestuarios, app de seguimiento, sin fidelidad obligatoria.',
                    'precio' => 69900.00,
                    'duracion_dias' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_membresia' => 2,
                    'nombre' => 'SMART',
                    'descripcion' => 'Multisede (hasta 3 sedes), clases ilimitadas, evaluación física mensual, descuentos 15% en tienda, soporte WhatsApp.',
                    'precio' => 99900.00,
                    'duracion_dias' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_membresia' => 3,
                    'nombre' => 'BLACK',
                    'descripcion' => 'Todas las sedes Colombia, coach personalizado asignado, rutina exclusiva mensual, plan nutricional, Smart Spa, llevar invitado 5 veces/mes, acceso 24/7.',
                    'precio' => 119900.00,
                    'duracion_dias' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Ajustar secuencia de id_membresia
            DB::statement("SELECT setval('membresias_id_membresia_seq', COALESCE((SELECT MAX(id_membresia)+1 FROM membresias), 1), false)");
        }

        // 2. Tabla compras_membresias
        if (!Schema::hasTable('compras_membresias')) {
            Schema::create('compras_membresias', function (Blueprint $table) {
                $table->increments('id_compra_membresia');
                $table->unsignedInteger('id_usuario');
                $table->unsignedInteger('id_membresia');
                $table->string('estado_pago', 50)->default('pendiente');
                $table->timestampTz('fecha_compra')->useCurrent();
                $table->string('referencia_pago', 100)->unique();
                $table->timestampTz('deleted_at')->nullable();
                $table->unsignedInteger('deleted_by')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestampsTz();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
                $table->foreign('id_membresia')->references('id_membresia')->on('membresias')->cascadeOnDelete();
                $table->foreign('deleted_by')->references('id_usuario')->on('usuarios')->nullOnDelete();
            });
        }

        // 3. Tabla audit_logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->increments('id_audit');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('accion', 100);
                $table->string('entidad', 100);
                $table->unsignedInteger('entidad_id')->nullable();
                $table->text('descripcion')->nullable();
                $table->timestampTz('created_at')->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('compras_membresias');
        Schema::dropIfExists('membresias');
    }
};
