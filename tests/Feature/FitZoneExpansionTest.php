<?php

namespace Tests\Feature;

use Tests\TestCase;
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
use App\Models\Venta;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class FitZoneExpansionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar secrets de Wompi para las pruebas
        Config::set('services.wompi.events_secret', 'test-events-secret');
        putenv('WOMPI_EVENTS_SECRET=test-events-secret');
    }

    protected function generateMockJwt(string $email, int $expirationOffset = 3600): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'email' => $email,
            'exp' => time() + $expirationOffset,
        ]));
        $signature = base64_encode('mock-signature');
        return "{$header}.{$payload}.{$signature}";
    }

    // --- 1. Pruebas de Autenticación y Middleware ---

    public function test_supabase_auth_middleware_denies_access_without_token()
    {
        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(401)
                 ->assertJsonFragment(['success' => false, 'message' => 'Token no proporcionado.']);
    }

    public function test_supabase_auth_middleware_denies_access_with_expired_token()
    {
        $user = User::create([
            'nombre' => 'Test Admin',
            'email' => 'admin-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        $token = $this->generateMockJwt($user->email, -100); // Expired 100 seconds ago

        $response = $this->getJson('/api/admin/users', [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment(['success' => false, 'message' => 'El token ha expirado.']);
    }

    public function test_supabase_auth_middleware_allows_access_to_active_user()
    {
        $user = User::create([
            'nombre' => 'Test Admin',
            'email' => 'admin-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        $token = $this->generateMockJwt($user->email);

        $response = $this->getJson('/api/admin/users', [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_role_middleware_blocks_non_admin_from_admin_routes()
    {
        $user = User::create([
            'nombre' => 'Regular User',
            'email' => 'user-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'user',
            'activo' => true,
        ]);

        $token = $this->generateMockJwt($user->email);

        $response = $this->getJson('/api/admin/users', [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(403);
    }

    // --- 2. Pruebas de Webhook de Wompi ---

    public function test_wompi_webhook_saves_approved_purchase_and_updates_plan()
    {
        $user = User::create([
            'nombre' => 'Juan Perez',
            'email' => 'juan-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'user',
            'activo' => true,
            'plan' => null,
        ]);

        // Crear membresía
        $membresia = Membresia::firstOrCreate(
            ['nombre' => 'SMART'],
            [
                'descripcion' => 'Plan SMART',
                'precio' => 99900.00,
                'duracion_dias' => 30,
            ]
        );

        // Crear Venta
        $venta = Venta::create([
            'numero_factura' => 'FAC-TEST-001',
            'id_usuario' => $user->id_usuario,
            'subtotal' => 99900.00,
            'total' => 99900.00,
            'estado' => 'pendiente',
            'canal' => 'web',
            'reference' => 'wompi-ref-12345',
            'items' => json_encode([
                [
                    'product_id' => 'plan_smart',
                    'nombre' => 'Plan SMART',
                    'price' => 99900.00,
                    'quantity' => 1
                ]
            ])
        ]);

        // Firmar webhook
        $timestamp = 1718040000;
        $transactionId = 'txn-approved-999';
        $transactionStatus = 'APPROVED';
        $amountCents = 9990000;
        
        $concatString = $transactionId . $transactionStatus . $amountCents . $timestamp . 'test-events-secret';
        $checksum = hash('sha256', $concatString);

        $payload = [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => $transactionId,
                    'amount_in_cents' => $amountCents,
                    'reference' => 'wompi-ref-12345',
                    'status' => $transactionStatus,
                ]
            ],
            'timestamp' => $timestamp,
            'signature' => [
                'checksum' => $checksum,
                'properties' => [
                    'transaction.id',
                    'transaction.status',
                    'transaction.amount_in_cents'
                ]
            ]
        ];

        $response = $this->postJson('/api/wompi/webhook', $payload);

        $response->assertStatus(200);

        // Verificar que la venta se completó
        $venta->refresh();
        $this->assertEquals('completada', $venta->estado);

        // Verificar que el plan se actualizó en el usuario
        $user->refresh();
        $this->assertEquals('SMART', $user->plan);

        // Verificar que la compra de membresía se registró
        $compra = CompraMembresia::where('id_usuario', $user->id_usuario)->first();
        $this->assertNotNull($compra);
        $this->assertEquals('aprobado', $compra->estado_pago);
        $this->assertEquals($membresia->id_membresia, $compra->id_membresia);

        // Verificar log de auditoría
        $log = AuditLog::where('id_usuario', $user->id_usuario)->first();
        $this->assertNotNull($log);
        $this->assertEquals('compra', $log->accion);
        $this->assertStringContainsString('SMART', $log->descripcion);
    }

    // --- 3. Pruebas de CRUD de Ejercicios y Soft Deletes ---

    public function test_admin_can_create_update_delete_and_restore_exercises()
    {
        $admin = User::create([
            'nombre' => 'Test Admin',
            'email' => 'admin-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        $token = $this->generateMockJwt($admin->email);

        // 1. Create exercise
        $exerciseData = [
            'nombre' => 'Sentadilla Búlgara',
            'grupo_muscular' => 'Pierna (Cuádriceps)',
            'dificultad' => 'Intermedio',
            'equipamiento' => 'Mancuernas',
            'descripcion' => 'Apoya un pie atrás y desciende.',
        ];

        $response = $this->postJson('/api/admin/exercises', $exerciseData, [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'data']);

        $exerciseId = $response->json('data.id');
        $this->assertDatabaseHas('ejercicios', ['id' => $exerciseId, 'nombre' => 'Sentadilla Búlgara']);

        // 2. Update exercise
        $updateData = [
            'nombre' => 'Sentadilla Búlgara con Barra',
            'dificultad' => 'Avanzado',
        ];

        $response = $this->putJson("/api/admin/exercises/{$exerciseId}", $updateData, [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ejercicios', ['id' => $exerciseId, 'nombre' => 'Sentadilla Búlgara con Barra', 'dificultad' => 'Avanzado']);

        // 3. Soft Delete exercise
        $response = $this->deleteJson("/api/admin/exercises/{$exerciseId}", [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200);
        
        $ejercicio = Ejercicio::withTrashed()->find($exerciseId);
        $this->assertNotNull($ejercicio->deleted_at);
        $this->assertTrue($ejercicio->is_deleted);

        // 4. Restore exercise
        $response = $this->postJson("/api/admin/exercises/{$exerciseId}/restore", [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200);
        
        $ejercicio->refresh();
        $this->assertNull($ejercicio->deleted_at);
        $this->assertFalse($ejercicio->is_deleted);
    }

    // --- 4. Pruebas de CRUD de Productos y Soft Deletes ---

    public function test_admin_can_soft_delete_and_restore_products()
    {
        $admin = User::create([
            'nombre' => 'Test Admin',
            'email' => 'admin-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        $token = $this->generateMockJwt($admin->email);

        // Crear marca y categoria
        $cat = Categoria::create(['nombre' => 'Suplementos', 'slug' => 'sups']);
        $marca = Marca::create(['nombre' => 'FitBrand', 'pais_origen' => 'CO']);

        $producto = Producto::create([
            'nombre' => 'Creatina Pura',
            'sku' => 'CRE-TEST-99',
            'descripcion' => '100% Monohidratada',
            'id_categoria' => $cat->id_categoria,
            'id_marca' => $marca->id_marca,
            'precio_costo' => 50000,
            'precio_venta' => 80000,
            'stock_actual' => 10,
            'activo' => true,
        ]);

        $productId = $producto->id_producto;

        // 1. Soft Delete product
        $response = $this->deleteJson("/api/products/{$productId}", [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200);
        
        $producto = Producto::withTrashed()->find($productId);
        $this->assertNotNull($producto->deleted_at);
        $this->assertTrue($producto->is_deleted);

        // 2. Restore product
        $response = $this->postJson("/api/admin/products/{$productId}/restore", [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200);
        
        $producto->refresh();
        $this->assertNull($producto->deleted_at);
        $this->assertFalse($producto->is_deleted);
    }

    // --- 5. Pruebas de Asignación de Rutinas y Simulación de Correo ---

    public function test_coach_can_assign_routines_and_logs_simulated_email()
    {
        $coach = User::create([
            'nombre' => 'Carlos Coach',
            'email' => 'coach-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'coach',
            'activo' => true,
        ]);

        $user = User::create([
            'nombre' => 'Juan Perez User',
            'email' => 'user-test@fitzone.com',
            'password_hash' => Hash::make('password'),
            'rol' => 'user',
            'activo' => true,
            'plan' => 'BLACK',
            'id_coach' => $coach->id_usuario,
        ]);

        $token = $this->generateMockJwt($coach->email);

        $rutinaData = [
            'nombre' => 'Rutina Fuerza Cuádriceps',
            'duracion' => '4 semanas',
            'nivel' => 'Avanzado',
            'ejercicios' => [
                [
                    'nombre' => 'Sentadillas',
                    'series' => 4,
                    'repeticiones' => 10,
                    'descanso' => '90s'
                ]
            ]
        ];

        $response = $this->postJson("/api/coach/rutina/{$user->id_usuario}", $rutinaData, [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['success' => true]);

        // Verificar que la rutina se creó en la base de datos
        $rutina = Rutina::where('id_usuario', $user->id_usuario)->first();
        $this->assertNotNull($rutina);
        $this->assertEquals('Rutina Fuerza Cuádriceps', $rutina->nombre);

        // Verificar que el historial de correos se registró con la simulación
        $emailLog = HistorialCorreo::where('id_usuario', $user->id_usuario)->first();
        $this->assertNotNull($emailLog);
        $this->assertEquals($user->email, $emailLog->email);
        $this->assertEquals('success', $emailLog->estado);
        $this->assertStringContainsString('Rutina Fuerza Cuádriceps', $emailLog->asunto);
    }
}
