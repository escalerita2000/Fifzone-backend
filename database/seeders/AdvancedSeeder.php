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
            $categorias[$name] = Categoria::firstOrCreate(
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
            $marcas[$name] = Marca::firstOrCreate(
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
        $defaultCoach = User::where('rol', 'coach')->first();
        if ($defaultCoach) {
            $coaches[] = $defaultCoach;
        }

        // Generar 4 coaches más para completar 5 (contraseña fija Coach123*)
        $coachesCountNeeded = 5 - count($coaches);
        for ($i = 0; $i < $coachesCountNeeded; $i++) {
            $email = "coach" . ($i + 1) . "@fitzone.com";
            $coaches[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => $faker->name . ' (Coach)',
                    'password_hash' => Hash::make('coach123'),
                    'rol' => 'coach',
                    'activo' => true,
                ]
            );
        }

        // --- 4. Generar Usuarios (contraseña fija FitZone123*) ---
        $existingUsersCount = User::count();
        $usersToCreate = max(100 - $existingUsersCount, 1);

        $usuariosCreados = [];
        for ($i = 0; $i < $usersToCreate; $i++) {
            $rol = 'user';
            $email = $faker->unique()->safeEmail;
            $randPlan = $faker->randomElement(['FIT', 'SMART', 'BLACK', null]);
            $idCoach = null;
            if ($randPlan === 'BLACK') {
                $coach = $faker->randomElement($coaches);
                $idCoach = $coach->id_usuario;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => $faker->name,
                    'password_hash' => Hash::make('user123'),
                    'rol' => $rol,
                    'activo' => $faker->boolean(90),
                    'plan' => $randPlan,
                    'id_coach' => $idCoach,
                    'ultimo_acceso' => $faker->dateTimeBetween('-30 days', 'now'),
                ]
            );
            $usuariosCreados[] = $user;
        }

        // --- 5. Generar Productos Reales en COP ---
        $productTemplates = [
            // Proteína Whey (80.000 - 220.000 COP)
            [
                'nombre' => 'Proteína Whey Gold Standard 2lb',
                'descripcion' => 'Aislado de proteína de suero de leche de alta pureza. Ideal para ganar masa muscular y acelerar la recuperación.',
                'categoria' => 'Proteínas',
                'marca' => 'Optimum Nutrition',
                'precio_venta' => 185000,
                'imagen_url' => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'NitroTech Whey Isolate 4lb',
                'descripcion' => 'Proteína de suero de leche ultra-filtrada enriquecida con aminoácidos y péptidos para la máxima absorción.',
                'categoria' => 'Proteínas',
                'marca' => 'MuscleTech',
                'precio_venta' => 210000,
                'imagen_url' => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Dymatize ISO100 Hydrolyzed 3lb',
                'descripcion' => 'Proteína hidrolizada libre de gluten y lactosa, con 25g de proteína pura de absorción ultra rápida.',
                'categoria' => 'Proteínas',
                'marca' => 'Dymatize',
                'precio_venta' => 220000,
                'imagen_url' => 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80',
            ],
            // Creatina (50.000 - 150.000 COP)
            [
                'nombre' => 'Creatina Monohidrato Dymatize 300g',
                'descripcion' => 'Creatina monohidratada pura 100% micronizada para potenciar la fuerza muscular y la resistencia en entrenamientos.',
                'categoria' => 'Creatinas',
                'marca' => 'Dymatize',
                'precio_venta' => 85000,
                'imagen_url' => 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Creatina CellTech Creador 500g',
                'descripcion' => 'Creatina formulada con carbohidratos de rápida asimilación para maximizar el transporte celular y el volumen.',
                'categoria' => 'Creatinas',
                'marca' => 'MuscleTech',
                'precio_venta' => 125000,
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            // Shakers (20.000 - 50.000 COP)
            [
                'nombre' => 'Vaso Mezclador Shaker Pro 700ml',
                'descripcion' => 'Shaker de alta calidad con rejilla mezcladora a prueba de fugas y compartimento para polvos de proteína.',
                'categoria' => 'Accesorios',
                'marca' => 'Optimum Nutrition',
                'precio_venta' => 28000,
                'imagen_url' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Smartshaker 3 en 1 Premium',
                'descripcion' => 'Mezclador deportivo multifuncional con 3 compartimentos independientes para guardar pastillas, polvos y líquido.',
                'categoria' => 'Accesorios',
                'marca' => 'Universal Nutrition',
                'precio_venta' => 38000,
                'imagen_url' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=800&q=80',
            ],
            // Bandas de resistencia (25.000 - 80.000 COP)
            [
                'nombre' => 'Set de Bandas de Resistencia Látex',
                'descripcion' => 'Kit de 5 bandas elásticas de látex natural con diferentes niveles de intensidad. Incluye bolsa de transporte.',
                'categoria' => 'Accesorios',
                'marca' => 'BSN',
                'precio_venta' => 48000,
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Banda de Tela Antideslizante para Glúteos',
                'descripcion' => 'Banda elástica de tela tejida de alta resistencia que no se enrolla ni se desliza durante los ejercicios de piernas.',
                'categoria' => 'Accesorios',
                'marca' => 'BSN',
                'precio_venta' => 32000,
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            // Mancuernas (100.000 - 400.000 COP)
            [
                'nombre' => 'Mancuerna Hexagonal de Goma 10kg',
                'descripcion' => 'Mancuerna hexagonal de hierro fundido recubierta de goma premium para proteger el suelo y reducir el ruido.',
                'categoria' => 'Accesorios',
                'marca' => 'Universal Nutrition',
                'precio_venta' => 135000,
                'imagen_url' => 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Kit de Mancuernas Ajustables 20kg',
                'descripcion' => 'Conjunto completo con discos intercambiables, barra cromada y cierres de rosca para regular el peso deseado.',
                'categoria' => 'Accesorios',
                'marca' => 'Universal Nutrition',
                'precio_venta' => 380000,
                'imagen_url' => 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?auto=format&fit=crop&w=800&q=80',
            ],
            // Guantes de gimnasio (30.000 - 90.000 COP)
            [
                'nombre' => 'Guantes de Gym Grip Pro Acolchados',
                'descripcion' => 'Guantes deportivos transpirables con palma reforzada antideslizante para un agarre óptimo en pesas y barras.',
                'categoria' => 'Accesorios',
                'marca' => 'Dymatize',
                'precio_venta' => 48000,
                'imagen_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a25f1?auto=format&fit=crop&w=800&q=80',
            ],
            // Botellas deportivas (20.000 - 60.000 COP)
            [
                'nombre' => 'Botella Deportiva Térmica Acero 1L',
                'descripcion' => 'Botella de acero inoxidable con doble pared aislada al vacío que mantiene el agua fría hasta por 24 horas.',
                'categoria' => 'Accesorios',
                'marca' => 'Cellucor',
                'precio_venta' => 55000,
                'imagen_url' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=800&q=80',
            ],
            // Camisetas deportivas (50.000 - 120.000 COP)
            [
                'nombre' => 'Camiseta Deportiva Dry-Fit FitZone',
                'descripcion' => 'Camiseta transpirable con tecnología Dry-Fit para absorber la humedad y mantenerte fresco durante los entrenamientos.',
                'categoria' => 'Accesorios',
                'marca' => 'BSN',
                'precio_venta' => 65000,
                'imagen_url' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=800&q=80',
            ],
            // Leggings (60.000 - 150.000 COP)
            [
                'nombre' => 'Leggings Deportivos de Compresión High',
                'descripcion' => 'Leggings de cintura alta de compresión con tejido elástico en 4 direcciones. Brinda soporte muscular ideal.',
                'categoria' => 'Accesorios',
                'marca' => 'BSN',
                'precio_venta' => 95000,
                'imagen_url' => 'https://images.unsplash.com/photo-1506152983158-b4a74a01c721?auto=format&fit=crop&w=800&q=80',
            ],
            // Pre-entrenos (90.000 - 160.000 COP)
            [
                'nombre' => 'Pre-Workout Psychotic Gold 35 serv',
                'descripcion' => 'Energía mental y muscular extrema para tus sesiones más exigentes, con estimulación y bombeo prolongados.',
                'categoria' => 'Pre-entreno',
                'marca' => 'Cellucor',
                'precio_venta' => 135000,
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            // Vitaminas (40.000 - 90.000 COP)
            [
                'nombre' => 'Animal Pak Multivitamin 30 packs',
                'descripcion' => 'El complejo vitamínico líder para deportistas de alto rendimiento, cargado con más de 60 ingredientes clave.',
                'categoria' => 'Vitaminas',
                'marca' => 'Universal Nutrition',
                'precio_venta' => 88000,
                'imagen_url' => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        // Duplicar con variaciones para tener 51 productos
        $index = 1;
        foreach ($productTemplates as $tpl) {
            $cat = $categorias[$tpl['categoria']];
            $brand = $marcas[$tpl['marca']];

            // Generamos 3 variaciones por cada plantilla
            $variations = [];
            if (in_array($tpl['categoria'], ['Proteínas', 'Pre-entreno', 'Creatinas'])) {
                $variations = ['Vainilla', 'Chocolate', 'Fresa'];
            } elseif (in_array($tpl['categoria'], ['Vitaminas'])) {
                $variations = ['Fórmula Clásica', 'Fórmula Advanced', 'Fórmula Ultra'];
            } elseif ($tpl['nombre'] == 'Set de Bandas de Resistencia Látex' || $tpl['nombre'] == 'Banda de Tela Antideslizante para Glúteos') {
                $variations = ['Resistencia Media', 'Resistencia Alta', 'Resistencia Extra Alta'];
            } elseif ($tpl['nombre'] == 'Mancuerna Hexagonal de Goma 10kg' || $tpl['nombre'] == 'Kit de Mancuernas Ajustables 20kg') {
                $variations = ['Negro Mate', 'Goma Premium', 'Acero Profesional'];
            } elseif ($tpl['nombre'] == 'Guantes de Gym Grip Pro Acolchados') {
                $variations = ['Talla M', 'Talla L', 'Talla XL'];
            } elseif ($tpl['nombre'] == 'Botella Deportiva Térmica Acero 1L' || $tpl['nombre'] == 'Vaso Mezclador Shaker Pro 700ml' || $tpl['nombre'] == 'Smartshaker 3 en 1 Premium') {
                $variations = ['Negro', 'Rojo', 'Azul'];
            } else {
                $variations = ['Talla S', 'Talla M', 'Talla L'];
            }

            foreach ($variations as $var) {
                $sku = "PROD-" . str_pad($index, 4, '0', STR_PAD_LEFT);
                $nombre = $tpl['nombre'] . ' (' . $var . ')';
                $precio_venta = $tpl['precio_venta'];
                $precio_costo = round($precio_venta * 0.65);

                Producto::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'nombre' => $nombre,
                        'descripcion' => $tpl['descripcion'],
                        'id_categoria' => $cat->id_categoria,
                        'id_marca' => $brand->id_marca,
                        'precio_costo' => $precio_costo,
                        'precio_venta' => $precio_venta,
                        'stock_actual' => $faker->numberBetween(5, 50),
                        'stock_minimo' => 5,
                        'activo' => true,
                        'imagen_url' => $tpl['imagen_url'],
                        'is_deleted' => false,
                    ]
                );
                $index++;
            }
        }

        // --- 6. Generar 30 Ejercicios Reales ---
        $exerciseTemplates = [
            [
                'nombre' => 'Press de Banca Plano con Barra',
                'grupo_muscular' => 'Pecho',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '10-12',
                'descripcion' => 'Acuéstate en un banco plano, baja la barra al pecho de forma controlada y empuja hacia arriba extendiendo los brazos.',
                'imagen_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Sentadillas Traseras con Barra',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Avanzado',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '8-10',
                'descripcion' => 'Coloca la barra en tus trapecios, desciende flexionando las rodillas y caderas manteniendo la espalda recta, y vuelve a la posición inicial.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Peso Muerto Convencional',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Avanzado',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '6-8',
                'descripcion' => 'Levanta la barra desde el suelo manteniendo la espalda neutra, empujando con las piernas y extendiendo las caderas.',
                'imagen_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Curl de Bíceps con Mancuernas',
                'grupo_muscular' => 'Brazos',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '12',
                'descripcion' => 'De pie con una mancuerna en cada mano, flexiona los codos manteniendo la parte superior de los brazos inmóvil.',
                'imagen_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Dominadas Pronas',
                'grupo_muscular' => 'Espalda',
                'dificultad' => 'Avanzado',
                'equipamiento' => 'Peso Corporal',
                'series' => '4',
                'repeticiones' => '8-10',
                'descripcion' => 'Cuélgate de una barra con agarre prono y levanta tu cuerpo hasta que tu barbilla pase la barra, usando la fuerza de tu espalda.',
                'imagen_url' => 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Flexiones de Pecho (Push-ups)',
                'grupo_muscular' => 'Pecho',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Peso Corporal',
                'series' => '3',
                'repeticiones' => '15-20',
                'descripcion' => 'Apoya las manos en el suelo a la anchura de los hombros, baja el cuerpo manteniendo una línea recta y empuja hacia arriba.',
                'imagen_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Plancha Abdominal Estática',
                'grupo_muscular' => 'Core',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Peso Corporal',
                'series' => '3',
                'repeticiones' => '30s-60s',
                'descripcion' => 'Mantén el cuerpo alineado apoyándote en antebrazos y puntas de los pies, contrayendo el abdomen.',
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Press Militar de Hombros',
                'grupo_muscular' => 'Hombros',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '8-10',
                'descripcion' => 'Empuja la barra sobre tu cabeza desde los hombros hasta la extensión completa de los brazos, con el abdomen contraído.',
                'imagen_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Remo con Barra para Espalda',
                'grupo_muscular' => 'Espalda',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '10',
                'descripcion' => 'Inclínate adelante con la espalda recta y tira de la barra hacia tu abdomen bajo, retrayendo las escápulas.',
                'imagen_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a25f1?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Zancadas con Mancuernas',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '12 por pierna',
                'descripcion' => 'Da un paso adelante flexionando ambas rodillas a 90 grados, manteniendo el torso vertical, y regresa.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Elevaciones Laterales de Hombro',
                'grupo_muscular' => 'Hombros',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '4',
                'repeticiones' => '15',
                'descripcion' => 'Eleva los brazos lateralmente con las mancuernas hasta la altura de los hombros, controlando la bajada.',
                'imagen_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Extensiones de Tríceps en Polea',
                'grupo_muscular' => 'Brazos',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Polea',
                'series' => '3',
                'repeticiones' => '12-15',
                'descripcion' => 'Empuja la cuerda hacia abajo extendiendo completamente los codos, apretando el tríceps al final.',
                'imagen_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Sentadilla Búlgara con Mancuernas',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '10 por pierna',
                'descripcion' => 'Apoya un pie atrás en un banco y baja flexionando la pierna delantera hasta que el muslo quede paralelo al suelo.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Remo Sentado en Polea Baja',
                'grupo_muscular' => 'Espalda',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Polea',
                'series' => '3',
                'repeticiones' => '12',
                'descripcion' => 'Tira del agarre hacia tu abdomen manteniendo el pecho erguido y retrayendo con fuerza la espalda.',
                'imagen_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a25f1?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Fondos en Paralelas para Pecho',
                'grupo_muscular' => 'Pecho',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Peso Corporal',
                'series' => '3',
                'repeticiones' => '10-12',
                'descripcion' => 'Baja el cuerpo suspendido en las barras paralelas inclinando el torso adelante, y empuja hacia arriba.',
                'imagen_url' => 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Prensa de Piernas inclinada',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Máquina',
                'series' => '4',
                'repeticiones' => '10-12',
                'descripcion' => 'Empuja la plataforma con los pies hasta casi estirar las piernas y flexiona controladamente sin despegar la lumbar.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Aperturas de Pecho con Mancuernas',
                'grupo_muscular' => 'Pecho',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '12',
                'descripcion' => 'Tumbado en banco plano, abre los brazos lateralmente de forma semicircular y vuelve a juntar las pesas al centro.',
                'imagen_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Jalón al Pecho Agarre Abierto',
                'grupo_muscular' => 'Espalda',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Máquina',
                'series' => '4',
                'repeticiones' => '10-12',
                'descripcion' => 'Tira de la barra hacia la parte superior del pecho manteniendo los hombros abajo y los codos dirigidos al suelo.',
                'imagen_url' => 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Curl de Bíceps en Banco Scott',
                'grupo_muscular' => 'Brazos',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '3',
                'repeticiones' => '10-12',
                'descripcion' => 'Apoya los tríceps en el cojín Scott y realiza la flexión de codo aislando el bíceps por completo.',
                'imagen_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Copa de Tríceps a Dos Manos',
                'grupo_muscular' => 'Brazos',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '12',
                'descripcion' => 'Sujeta una mancuerna pesada tras tu nuca y extiéndela sobre tu cabeza manteniendo los codos cerrados.',
                'imagen_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Pájaros de Hombro Sentado',
                'grupo_muscular' => 'Hombros',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '15',
                'descripcion' => 'Inclina el torso sobre tus rodillas y eleva lateralmente las mancuernas para enfocar el deltoides posterior.',
                'imagen_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Peso Muerto Rumano con Barra',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '10',
                'descripcion' => 'Lleva la cadera hacia atrás flexionando levemente las rodillas, baja la barra cerca de las piernas sintiendo estirar femorales.',
                'imagen_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Hip Thrust en Banco',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Barra',
                'series' => '4',
                'repeticiones' => '10-12',
                'descripcion' => 'Apoya la parte alta de la espalda en el banco, empuja la barra sobre la pelvis extendiendo la cadera completamente.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Remo Unilateral con Mancuerna',
                'grupo_muscular' => 'Espalda',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '12 por lado',
                'descripcion' => 'Apoya rodilla y mano en banco plano y jala la mancuerna llevando el codo hacia la cadera.',
                'imagen_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a25f1?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Crunch Abdominal en Polea Alta',
                'grupo_muscular' => 'Core',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Polea',
                'series' => '4',
                'repeticiones' => '15-20',
                'descripcion' => 'De rodillas, sujeta la cuerda tras tu cabeza y contrae el abdomen llevando los codos en dirección a los muslos.',
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Elevaciones de Pierna Colgado',
                'grupo_muscular' => 'Core',
                'dificultad' => 'Intermedio',
                'equipamiento' => 'Peso Corporal',
                'series' => '3',
                'repeticiones' => '12-15',
                'descripcion' => 'Suspendido en barra, eleva las piernas rectas o flexionadas hasta los 90 grados, usando la fuerza del abdomen.',
                'imagen_url' => 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Rueda Abdominal (Rollouts)',
                'grupo_muscular' => 'Core',
                'dificultad' => 'Avanzado',
                'equipamiento' => 'Peso Corporal',
                'series' => '3',
                'repeticiones' => '10',
                'descripcion' => 'De rodillas, rueda la rueda abdominal hacia adelante manteniendo la espalda compacta y vuelve sin arquear la lumbar.',
                'imagen_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Zancadas Dinámicas Caminando',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Mancuernas',
                'series' => '3',
                'repeticiones' => '20 pasos',
                'descripcion' => 'Avanza alternando las piernas en zancadas continuas manteniendo el torso recto y la mirada al frente.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Curl Femoral en Máquina Tumbado',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Máquina',
                'series' => '4',
                'repeticiones' => '12',
                'descripcion' => 'Tumbado prono en la máquina, flexiona las rodillas tirando del rodillo hacia los glúteos de forma controlada.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Extensiones de Cuádriceps en Máquina',
                'grupo_muscular' => 'Piernas',
                'dificultad' => 'Principiante',
                'equipamiento' => 'Máquina',
                'series' => '4',
                'repeticiones' => '12-15',
                'descripcion' => 'Sentado en la máquina, extiende las rodillas completamente aguantando un segundo al final para máxima contracción.',
                'imagen_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $ejerciciosCreados = [];
        foreach ($exerciseTemplates as $tpl) {
            $ejercicio = Ejercicio::firstOrCreate(
                ['nombre' => $tpl['nombre']],
                [
                    'grupo_muscular' => $tpl['grupo_muscular'],
                    'dificultad' => $tpl['dificultad'],
                    'equipamiento' => $tpl['equipamiento'],
                    'series' => $tpl['series'],
                    'repeticiones' => $tpl['repeticiones'],
                    'descripcion' => $tpl['descripcion'],
                    'imagen_url' => $tpl['imagen_url'],
                    'is_deleted' => false,
                ]
            );
            $ejerciciosCreados[] = $ejercicio;
        }

        // --- 7. Generar 20 Rutinas ---
        $existingRoutinesCount = Rutina::count();
        $routinesToCreate = max(20 - $existingRoutinesCount, 1);
        $blackUsers = User::where('plan', 'BLACK')->get();
        $dificultades = ['Principiante', 'Intermedio', 'Avanzado'];

        for ($i = 0; $i < $routinesToCreate; $i++) {
            $numEjercicios = $faker->numberBetween(4, 7);
            $exercisesList = [];
            for ($j = 0; $j < $numEjercicios; $j++) {
                $ej = $faker->randomElement($ejerciciosCreados);
                $exercisesList[] = [
                    'nombre' => $ej->nombre,
                    'series' => $ej->series ?? 4,
                    'repeticiones' => $ej->repeticiones ?? '10-12',
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

            if ($user) {
                DB::table('rutina_usuario')->insert([
                    'id_usuario' => $user->id_usuario,
                    'id_rutina' => $rutina->id,
                    'asignado_at' => now(),
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

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

            if ($estadoPago === 'APPROVED') {
                $user->update([
                    'plan' => $membresia->nombre,
                ]);

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
