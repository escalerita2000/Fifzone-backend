<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(string $action, string $entity, ?int $entityId = null, ?string $description = null, ?int $userId = null): void
    {
        try {
            AuditLog::create([
                'id_usuario'  => $userId ?? (auth()->check() ? auth()->id() : null),
                'accion'      => $action,
                'entidad'     => $entity,
                'entidad_id'  => $entityId,
                'descripcion' => $description,
                'created_at'  => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error registrando log de auditoría: " . $e->getMessage());
        }
    }
}
