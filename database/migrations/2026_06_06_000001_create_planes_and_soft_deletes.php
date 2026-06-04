<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla planes
        if (!Schema::hasTable('planes')) {
            Schema::create('planes', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('nombre', 100);
                $table->integer('precio');
                $table->string('precio_display', 100);
                $table->string('periodo', 50);
                $table->text('descripcion');
                $table->jsonb('beneficios');
                $table->string('color', 50);
                $table->string('badge', 50)->nullable();
                $table->boolean('requiere_coach')->default(false);
                $table->timestampsTz();
            });

            // Sembrar planes iniciales
            DB::table('planes')->insert([
                [
                    'id' => 'fit',
                    'nombre' => 'FIT',
                    'precio' => 69900,
                    'precio_display' => '$69.900/mes',
                    'periodo' => '/mes',
                    'descripcion' => '1 sede, clases grupales, zona cardio y pesas, vestuarios, app de seguimiento, sin fidelidad obligatoria.',
                    'beneficios' => json_encode(['1 sede', 'Clases grupales', 'Zona cardio y pesas', 'Vestuarios', 'App de seguimiento', 'Sin fidelidad obligatoria']),
                    'color' => 'default',
                    'badge' => null,
                    'requiere_coach' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 'smart',
                    'nombre' => 'SMART',
                    'precio' => 99900,
                    'precio_display' => '$99.900/mes',
                    'periodo' => '/mes',
                    'descripcion' => 'Multisede (hasta 3 sedes), clases ilimitadas, evaluación física mensual, descuentos 15% en tienda, soporte WhatsApp.',
                    'beneficios' => json_encode(['Multisede (hasta 3 sedes)', 'Clases ilimitadas', 'Evaluación física mensual', 'Descuentos 15% en tienda', 'Soporte WhatsApp']),
                    'color' => 'featured',
                    'badge' => 'Más popular',
                    'requiere_coach' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 'black',
                    'nombre' => 'BLACK',
                    'precio' => 119900,
                    'precio_display' => '$119.900/mes',
                    'periodo' => '/mes',
                    'descripcion' => 'Todas las sedes Colombia, coach personalizado asignado, rutina exclusiva mensual, plan nutricional, Smart Spa, llevar invitado 5 veces/mes, acceso 24/7.',
                    'beneficios' => json_encode(['Todas las sedes Colombia', 'Coach personalizado asignado', 'Rutina exclusiva mensual', 'Plan nutricional', 'Smart Spa', 'Llevar invitado 5 veces/mes', 'Acceso 24/7']),
                    'color' => 'default',
                    'badge' => 'Premium',
                    'requiere_coach' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Agregar columnas de borrado lógico a las tablas
        // 2. Agregar columnas de borrado lógico a las tablas
$tables = [
    'usuarios',
    'productos',
    'rutinas',
    'ventas',
    'categorias',
    'compras',
    'marcas',
    'proveedores',
    'planes'
];

foreach ($tables as $t) {

    if (!Schema::hasTable($t)) {
        continue;
    }

    Schema::table($t, function (Blueprint $table) use ($t) {

        if (!Schema::hasColumn($t, 'deleted_at')) {
            $table->timestampTz('deleted_at')->nullable();
        }

        if (!Schema::hasColumn($t, 'deleted_by')) {
            $table->unsignedInteger('deleted_by')->nullable();
        }

        if (!Schema::hasColumn($t, 'is_deleted')) {
            $table->boolean('is_deleted')->default(false);
        }
    });
}

// Agregar llaves foráneas para deleted_by
foreach ($tables as $t) {

    if (!Schema::hasTable($t) || $t === 'usuarios') {
        continue;
    }

    try {
        Schema::table($t, function (Blueprint $table) {
            $table->foreign('deleted_by')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete();
        });
    } catch (\Throwable $e) {
        // ignorar si ya existe
    }
}

        // 3. Crear trigger de sincronización con auth.users en Supabase
        DB::unprepared("
        CREATE OR REPLACE FUNCTION public.handle_auth_user_change()
        RETURNS trigger AS $$
        DECLARE
          var_nombre varchar;
          var_rol varchar;
        BEGIN
          -- Determinar nombre
          IF (new.raw_user_meta_data->>'name' IS NOT NULL) THEN
            var_nombre := new.raw_user_meta_data->>'name';
          ELSIF (new.raw_user_meta_data->>'full_name' IS NOT NULL) THEN
            var_nombre := new.raw_user_meta_data->>'full_name';
          ELSE
            var_nombre := split_part(new.email, '@', 1);
          END IF;

          -- Determinar rol inicial
          IF (new.email = 'admin@fitzone.com') THEN
            var_rol := 'admin';
          ELSIF (new.email = 'coach@test.com' OR new.email = 'rondonbarrerow@gmail.com') THEN
            var_rol := 'coach';
          ELSE
            var_rol := 'user';
          END IF;

          IF (TG_OP = 'INSERT') THEN
            INSERT INTO public.usuarios (nombre, email, password_hash, rol, activo, created_at, updated_at)
            VALUES (
              var_nombre,
              new.email,
              COALESCE(new.encrypted_password, ''),
              var_rol,
              (new.email_confirmed_at IS NOT NULL),
              new.created_at,
              new.updated_at
            )
            ON CONFLICT (email) DO UPDATE
            SET
              nombre = EXCLUDED.nombre,
              password_hash = EXCLUDED.password_hash,
              activo = EXCLUDED.activo,
              updated_at = EXCLUDED.updated_at;
          ELSIF (TG_OP = 'UPDATE') THEN
            UPDATE public.usuarios
            SET
              nombre = var_nombre,
              email = new.email,
              password_hash = COALESCE(new.encrypted_password, password_hash),
              activo = (new.email_confirmed_at IS NOT NULL),
              rol = CASE WHEN rol IN ('admin', 'coach') THEN rol ELSE var_rol END,
              updated_at = new.updated_at
            WHERE email = old.email;
          END IF;
          RETURN new;
        END;
        $$ LANGUAGE plpgsql SECURITY DEFINER;
        ");

        DB::unprepared("
        DROP TRIGGER IF EXISTS on_auth_user_change ON auth.users;
        CREATE TRIGGER on_auth_user_change
          AFTER INSERT OR UPDATE ON auth.users
          FOR EACH ROW EXECUTE FUNCTION public.handle_auth_user_change();
        ");

        // 4. Retroalimentar usuarios existentes desde auth.users
        // 4. Retroalimentar usuarios existentes desde auth.users
DB::unprepared("
INSERT INTO public.usuarios (
    nombre,
    email,
    password_hash,
    rol,
    activo,
    created_at,
    updated_at
)
SELECT
    COALESCE(
        raw_user_meta_data->>'name',
        raw_user_meta_data->>'full_name',
        split_part(email, '@', 1)
    ) AS nombre,
    email,
    COALESCE(encrypted_password, '') AS password_hash,
    CASE
        WHEN email = 'admin@fitzone.com' THEN 'admin'
        WHEN email IN ('coach@test.com', 'rondonbarrerow@gmail.com') THEN 'coach'
        ELSE 'user'
    END AS rol,
    (email_confirmed_at IS NOT NULL) AS activo,
    created_at,
    updated_at
FROM auth.users
ON CONFLICT (email) DO UPDATE
SET
    nombre = EXCLUDED.nombre,
    password_hash = EXCLUDED.password_hash,
    activo = EXCLUDED.activo,
    updated_at = NOW();
");

        // 5. Sembrar el usuario Coach de prueba (coach@test.com / Coach123*)
        $coachEmail = 'coach@test.com';
        $coachPasswordHash = Hash::make('Coach123*');
        $coachUuid = 'a3977c7f-f772-4638-9cf8-b3d9571f5495';

        // $existsInAuth = DB::table('auth.users')->where('email', $coachEmail)->exists();
        // if (!$existsInAuth) {
        //     DB::table('auth.users')->insert([
        //         'id' => $coachUuid,
        //         'instance_id' => '00000000-0000-0000-0000-000000000000',
        //         'aud' => 'authenticated',
        //         'role' => 'authenticated',
        //         'email' => $coachEmail,
        //         'encrypted_password' => $coachPasswordHash,
        //         'email_confirmed_at' => now(),
        //         'raw_app_meta_data' => json_encode(['provider' => 'email', 'providers' => ['email']]),
        //         'raw_user_meta_data' => json_encode(['name' => 'Coach Test', 'full_name' => 'Coach Test', 'email_verified' => true]),
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // Sembrar en public.usuarios también para asegurar
        DB::table('usuarios')->insertOrIgnore([
            'nombre' => 'Coach Test',
            'email' => $coachEmail,
            'password_hash' => $coachPasswordHash,
            'rol' => 'coach',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Eliminar trigger y función
        DB::unprepared("DROP TRIGGER IF EXISTS on_auth_user_change ON auth.users;");
        DB::unprepared("DROP FUNCTION IF EXISTS public.handle_auth_user_change();");

        // Quitar columnas
        $tables = ['usuarios', 'productos', 'rutinas', 'ventas', 'categorias', 'compras', 'marcas', 'proveedores', 'planes'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                try {
                    $table->dropForeign([$t . '_deleted_by_foreign']);
                } catch (\Exception $e) {}
                $table->dropColumn(['deleted_at', 'deleted_by', 'is_deleted']);
            });
        }

        Schema::dropIfExists('planes');
    }
};
