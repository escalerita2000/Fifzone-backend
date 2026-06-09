<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Membresia;
use App\Models\CompraMembresia;
use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Models\HistorialCorreo;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AdvancedSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // --- 1. Crear Membresías Base si no existen ---
        $membresiaFit = Membresia::firstOrCreate(
            ['nombre' => 'FIT'],
            [
                'descripcion' => '1 sede, clases grupales, zona cardio y pesas, vestuarios.',
                'precio' => 69900.00,
                'duracion_dias' => 30,
            ]
        );

        $membresiaSmart = Membresia::firstOrCreate(
            ['nombre' => 'SMART'],
            [
                'descripcion' => 'Multisede, clases ilimitadas, evaluación física mensual.',
                'precio' => 99900.00,
                'duracion_dias' => 30,
            ]
        );

        $membresiaBlack = Membresia::firstOrCreate(
            ['nombre' => 'BLACK'],
            [
                'descripcion' => 'Todas las sedes, coach personalizado, rutina exclusiva, plan nutricional.',
                'precio' => 119900.00,
                'duracion_dias' => 30,
            ]
        );

        // --- 2. Crear Categorías y Marcas si no existen ---
        $categorias = [];
        $catNames = ['Proteínas', 'Pre-entreno', 'Vitaminas', 'Accesorios', 'Creatinas', 'Aminoácidos', 'Quemadores'];
        foreach ($catNames as $name) {
            $categorias[] = Categoria::firstOrCreate(
                ['nombre' => $name],
                [
                    'slug' => strtolower(str_replace(' ', '-', $name)),
                    'descripcion' => "Categoría de suplementación: {$name}",
                    'activo' => true,
                ]
            );
        }

        $marcas = [];
        $marcaNames = ['Optimum Nutrition', 'MuscleTech', 'Dymatize', 'Cellucor', 'BSN', 'Universal Nutrition'];
        foreach ($marcaNames as $name) {
            $marcas[] = Marca::firstOrCreate(
                ['nombre' => $name],
                [
                    'pais_origen' => 'USA',
                    'logo_url' => '',
                    'activo' => true,
                ]
            );
        }

        // --- 3. Generar Coaches ---
        $coaches = [];
        // Coach por defecto de DatabaseSeeder o nuevo
        $defaultCoach = User::where('rol', 'coach')->first();
        if ($defaultCoach) {
            $coaches[] = $defaultCoach;
        }

        // Generar 4 coaches más para completar 5
        $coachesCountNeeded = 5 - count($coaches);
        for ($i = 0; $i < $coachesCountNeeded; $i++) {
            $email = "coach" . ($i + 1) . "@fitzone.com";
            $coaches[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => $faker->name . ' (Coach)',
                    'password_hash' => Hash::make('fitzone2024'),
                    'rol' => 'coach',
                    'activo' => true,
                ]
            );
        }

        // --- 4. Generar Usuarios (hasta completar 100 usuarios en total) ---
        $existingUsersCount = User::count();
        $usersToCreate = max(100 - $existingUsersCount, 1);

        $usuariosCreados = [];
        for ($i = 0; $i < $usersToCreate; $i++) {
            $rol = 'user';
            $email = $faker->unique()->safeEmail;
            // Repartir planes
            $randPlan = $faker->randomElement(['FIT', 'SMART', 'BLACK', null]);
            $idCoach = null;
            if ($randPlan === 'BLACK') {
                // Asignar coach
                $coach = $faker->randomElement($coaches);
                $idCoach = $coach->id_usuario;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => $faker->name,
                    'password_hash' => Hash::make('fitzone2024'),
                    'rol' => $rol,
                    'activo' => $faker->boolean(90), // 90% activos
                    'plan' => $randPlan,
                    'id_coach' => $idCoach,
                    'ultimo_acceso' => $faker->dateTimeBetween('-30 days', 'now'),
                ]
            );
            $usuariosCreados[] = $user;
        }

        // --- 5. Generar 50 Productos ---
        $existingProductsCount = Producto::count();
        $productsToCreate = max(50 - $existingProductsCount, 1);

        for ($i = 0; $i < $productsToCreate; $i++) {
            $categoria = $faker->randomElement($categorias);
            $marca = $faker->randomElement($marcas);
            $costo = $faker->randomFloat(2, 20000, 120000);
            $venta = $costo * $faker->randomFloat(2, 1.3, 1.7); // 30% a 70% margen
            $sku = strtoupper($faker->unique()->lexify('???-???-')) . $faker->unique()->numerify('####');

            Producto::firstOrCreate(
                ['sku' => $sku],
                [
                    'nombre' => $faker->words(3, true),
                    'descripcion' => $faker->paragraph,
                    'id_categoria' => $categoria->id_categoria,
                    'id_marca' => $marca->id_marca,
                    'precio_costo' => $costo,
                    'precio_venta' => $venta,
                    'stock_actual' => $faker->numberBetween(0, 100),
                    'stock_minimo' => 5,
                    'activo' => $faker->boolean(85), // 85% activos
                    'is_deleted' => false,
                ]
            );
        }

        // --- 6. Generar 30 Ejercicios ---
        $existingExercisesCount = Ejercicio::count();
        $exercisesToCreate = max(30 - $existingExercisesCount, 1);

        $musculos = ['Pecho', 'Espalda', 'Pierna (Cuádriceps)', 'Pierna (Femoral)', 'Hombro', 'Bíceps', 'Tríceps', 'Abdomen', 'Cardio'];
        $dificultades = ['Principiante', 'Intermedio', 'Avanzado'];
        $equipos = ['Mancuernas', 'Barra', 'Máquina', 'Peso Corporal', 'Bandas de Resistencia', 'Polea'];

        $ejerciciosCreados = [];
        for ($i = 0; $i < $exercisesToCreate; $i++) {
            $nombre = $faker->unique()->words(2, true);
            $ejercicio = Ejercicio::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'grupo_muscular' => $faker->randomElement($musculos),
                    'dificultad' => $faker->randomElement($dificultades),
                    'equipamiento' => $faker->randomElement($equipos),
                    'descripcion' => 'Instrucciones: ' . $faker->sentence(12),
                    'imagen_url' => null,
                    'is_deleted' => false,
                ]
            );
            $ejerciciosCreados[] = $ejercicio;
        }

        if (empty($ejerciciosCreados)) {
            $ejerciciosCreados = Ejercicio::all()->toArray();
        }

        // --- 7. Generar 20 Rutinas ---
        $existingRoutinesCount = Rutina::count();
        $routinesToCreate = max(20 - $existingRoutinesCount, 1);

        // Agarrar todos los usuarios BLACK para asignación
        $blackUsers = User::where('plan', 'BLACK')->get();

        for ($i = 0; $i < $routinesToCreate; $i++) {
            // Estructurar ejercicios de rutina
            $numEjercicios = $faker->numberBetween(4, 7);
            $exercisesList = [];
            for ($j = 0; $j < $numEjercicios; $j++) {
                $ej = $faker->randomElement($ejerciciosCreados);
                $exercisesList[] = [
                    'nombre' => $ej['nombre'] ?? $ej->nombre,
                    'series' => $faker->randomElement([3, 4]),
                    'repeticiones' => $faker->randomElement(['10-12', '12', '8-10', '15']),
                    'descanso' => '60s',
                ];
            }

            $user = $blackUsers->isNotEmpty() ? $faker->randomElement($blackUsers) : null;
            $coach = $faker->randomElement($coaches);

            $rutina = Rutina::create([
                'nombre' => 'Rutina ' . $faker->randomElement(['Hipertrofia', 'Definición', 'Fuerza', 'Resistencia']) . ' ' . $faker->word,
                'duracion' => $faker->randomElement(['4 semanas', '6 semanas', '8 semanas']),
                'nivel' => $faker->randomElement($dificultades),
                'imagen' => null,
                'ejercicios' => $exercisesList,
                'id_usuario' => $user ? $user->id_usuario : null,
                'id_coach' => $coach->id_usuario,
                'sent_at' => $faker->dateTimeBetween('-15 days', 'now'),
                'is_deleted' => false,
            ]);

            // Asignación relacional en rutina_usuario
            if ($user) {
                DB::table('rutina_usuario')->insert([
                    'id_usuario' => $user->id_usuario,
                    'id_rutina' => $rutina->id,
                    'asignado_at' => now(),
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Simular historial de correos
                HistorialCorreo::create([
                    'id_usuario' => $user->id_usuario,
                    'id_rutina' => $rutina->id,
                    'email' => $user->email,
                    'asunto' => "Nueva rutina asignada: " . $rutina->nombre,
                    'cuerpo' => "Hola " . $user->nombre . ", tu coach " . $coach->nombre . " te ha asignado una nueva rutina de entrenamiento. ¡A entrenar con toda!",
                    'estado' => 'enviado',
                    'sent_at' => now(),
                ]);
            }
        }

        // --- 8. Generar 200 Compras/Membresías ---
        $existingPurchasesCount = CompraMembresia::count();
        $purchasesToCreate = max(200 - $existingPurchasesCount, 1);

        // Todos los usuarios no-admin, no-coach
        $regularUsers = User::where('rol', 'user')->get();
        $membresias = [$membresiaFit, $membresiaSmart, $membresiaBlack];

        for ($i = 0; $i < $purchasesToCreate; $i++) {
            $user = $regularUsers->isNotEmpty() ? $faker->randomElement($regularUsers) : User::first();
            $membresia = $faker->randomElement($membresias);
            $estadoPago = $faker->randomElement(['APPROVED', 'APPROVED', 'APPROVED', 'PENDING', 'DECLINED']);

            $compra = CompraMembresia::create([
                'id_usuario' => $user->id_usuario,
                'id_membresia' => $membresia->id_membresia,
                'estado_pago' => $estadoPago,
                'fecha_compra' => $faker->dateTimeBetween('-60 days', 'now'),
                'referencia_pago' => 'WOMPI-' . strtoupper($faker->unique()->bothify('??##?#?##?')),
                'is_deleted' => false,
            ]);

            // Si el pago es aprobado, podemos actualizar la membresía activa del usuario
            if ($estadoPago === 'APPROVED') {
                $user->update([
                    'plan' => $membresia->nombre,
                ]);

                // Crear log de auditoría
                AuditLog::create([
                    'id_usuario' => $user->id_usuario,
                    'accion' => 'compra_membresia',
                    'entidad' => 'compras_membresias',
                    'entidad_id' => $compra->id_compra_membresia,
                    'descripcion' => "El usuario {$user->nombre} adquirió la membresía {$membresia->nombre} con referencia {$compra->referencia_pago}.",
                ]);
            }
        }
    }
}
