<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Producto;
use App\Models\Rutina;
use App\Models\Venta;
use App\Models\CompraMembresia;

class AdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * GET /api/admin/users
     * Listar usuarios paginados con filtros
     */
    public function users(Request $request)
    {
        $filters = $request->only(['search', 'rol', 'activo', 'plan', 'deleted']);
        $perPage = $request->integer('per_page', 10);

        $users = $this->userService->listUsers($filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => $users->items(),
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ]
        ]);
    }

    /**
     * PUT /api/admin/users/{id}
     * Actualizar usuario
     */
    public function updateUser(Request $request, $id)
    {
        $data = $request->validate([
            'nombre'   => 'sometimes|string|max:150',
            'email'    => 'sometimes|email|unique:usuarios,email,' . $id . ',id_usuario',
            'rol'      => 'sometimes|string|in:admin,coach,user',
            'activo'   => 'sometimes|boolean',
            'plan'     => 'nullable|string|max:20',
            'id_coach' => 'nullable|integer|exists:usuarios,id_usuario',
        ]);

        $user = $this->userService->updateUser($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
            'user'    => $user
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}
     * Soft delete usuario
     */
    public function deleteUser($id)
    {
        $this->userService->deleteUser($id);

        return response()->json([
            'success' => true,
            'message' => 'Usuario inhabilitado correctamente.'
        ]);
    }

    /**
     * POST /api/admin/users/{id}/restore
     * Restaurar usuario
     */
    public function restoreUser($id)
    {
        $this->userService->restoreUser($id);

        return response()->json([
            'success' => true,
            'message' => 'Usuario restaurado correctamente.'
        ]);
    }

    /**
     * GET /api/admin/memberships
     * Historial de membresías compradas con paginación y búsqueda
     */
    public function memberships(Request $request)
    {
        $query = CompraMembresia::with(['usuario', 'membresia']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('usuario', function ($q) use ($search) {
                $q->whereLike('nombre', "%{$search}%")
                  ->orWhereLike('email', "%{$search}%");
            });
        }

        if ($request->filled('plan')) {
            $planName = $request->plan;
            $query->whereHas('membresia', function ($q) use ($planName) {
                $q->where('nombre', strtoupper($planName));
            });
        }

        $perPage = $request->integer('per_page', 10);
        $compras = $query->latest('id_compra_membresia')->paginate($perPage);

        $formatted = collect($compras->items())->map(function ($c) {
            return [
                'id'              => $c->id_compra_membresia,
                'nombre'          => $c->usuario?->nombre ?? 'Usuario Eliminado',
                'email'           => $c->usuario?->email ?? 'N/A',
                'plan'            => $c->membresia?->nombre ?? 'N/A',
                'fecha_compra'    => $c->fecha_compra->format('Y-m-d H:i:s'),
                'referencia_pago' => $c->referencia_pago,
                'estado_pago'     => $c->estado_pago,
                'precio'          => $c->membresia?->precio ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formatted,
            'meta'    => [
                'current_page' => $compras->currentPage(),
                'last_page'    => $compras->lastPage(),
                'per_page'     => $compras->perPage(),
                'total'        => $compras->total(),
            ]
        ]);
    }

    /**
     * GET /api/admin/dashboard-stats
     * Estadísticas consolidadas con caché
     */
    public function dashboardStats()
    {
        $stats = Cache::remember('admin_dashboard_stats', 60, function () {
            // Usuarios
            $totalUsers = User::count();
            $activeUsers = User::where('activo', true)->count();
            $admins = User::where('rol', 'admin')->count();
            $coaches = User::where('rol', 'coach')->count();

            // Membresías activas / usuarios por plan
            $fitCount = User::where('plan', 'FIT')->count();
            $smartCount = User::where('plan', 'SMART')->count();
            $blackCount = User::where('plan', 'BLACK')->count();

            // Productos
            $totalProducts = Producto::withTrashed()->count();
            $activeProducts = Producto::count();
            $deletedProducts = Producto::onlyTrashed()->count();

            // Rutinas
            $totalRoutines = Rutina::withTrashed()->count();
            $sentRoutines = Rutina::whereNotNull('sent_at')->count();

            // Ingresos (Ventas registradas)
            $approvedIncome = Venta::where('estado', 'completada')->sum('total');
            $pendingIncome = Venta::where('estado', 'pendiente')->sum('total');
            $totalIncome = Venta::sum('total');

            return [
                'usuarios' => [
                    'total' => $totalUsers,
                    'activos' => $activeUsers,
                    'admins' => $admins,
                    'coaches' => $coaches,
                ],
                'membresias' => [
                    'fit' => $fitCount,
                    'smart' => $smartCount,
                    'black' => $blackCount,
                ],
                'productos' => [
                    'total' => $totalProducts,
                    'activos' => $activeProducts,
                    'eliminados' => $deletedProducts,
                ],
                'rutinas' => [
                    'total' => $totalRoutines,
                    'enviadas' => $sentRoutines,
                ],
                'ingresos' => [
                    'aprobados' => (float) $approvedIncome,
                    'pendientes' => (float) $pendingIncome,
                    'total' => (float) $totalIncome,
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $stats
        ]);
    }
}
