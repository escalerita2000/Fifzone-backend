<?php

namespace App\Services;

use App\Repositories\MembershipRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    protected $repository;

    public function __construct(MembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Registrar la compra de una membresía aprobada.
     */
    public function registerPurchase($userId, $planName, $reference, $status = 'aprobado')
    {
        return DB::transaction(function () use ($userId, $planName, $reference, $status) {
            // 1. Buscar la membresía en la base de datos
            $membresia = $this->repository->findByName($planName);
            if (!$membresia) {
                // Si no se encuentra por nombre exacto, intentar mapear fallbacks
                $cleanName = strtoupper(trim($planName));
                if (str_contains($cleanName, 'BLACK')) {
                    $membresia = $this->repository->findByName('BLACK');
                } elseif (str_contains($cleanName, 'SMART')) {
                    $membresia = $this->repository->findByName('SMART');
                } else {
                    $membresia = $this->repository->findByName('FIT');
                }
            }

            // 2. Registrar la compra
            $purchase = $this->repository->createPurchase([
                'id_usuario'      => $userId,
                'id_membresia'    => $membresia->id_membresia,
                'estado_pago'     => $status,
                'fecha_compra'    => now(),
                'referencia_pago' => $reference,
            ]);

            // 3. Compatibilidad hacia atrás: actualizar el plan del usuario si está aprobado
            if (in_array(strtolower($status), ['aprobado', 'approved', 'completada'])) {
                $user = User::find($userId);
                if ($user) {
                    $user->plan = strtoupper($membresia->nombre);
                    $user->save();
                }
            }

            return $purchase;
        });
    }
}
