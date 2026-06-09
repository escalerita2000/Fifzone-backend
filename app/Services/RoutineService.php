<?php

namespace App\Services;

use App\Repositories\RoutineRepository;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;

class RoutineService
{
    protected $repository;

    public function __construct(RoutineRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createRoutine(array $data)
    {
        $routine = $this->repository->create($data);
        AuditService::log('crear', 'rutina', $routine->id, "Rutina '{$routine->nombre}' creada.");
        return $routine;
    }

    public function getRoutine(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateRoutine(int $id, array $data)
    {
        $routine = $this->repository->update($id, $data);
        AuditService::log('actualizar', 'rutina', $id, "Rutina '{$routine->nombre}' actualizada.");
        return $routine;
    }

    public function deleteRoutine(int $id)
    {
        $this->repository->delete($id);
        AuditService::log('eliminar', 'rutina', $id, "Rutina con ID {$id} eliminada lógicamente.");
    }

    public function assignRoutineToUser(int $routineId, int $userId)
    {
        $this->repository->assignToUser($routineId, $userId);
        
        $routine = $this->repository->findById($routineId);
        AuditService::log('asignar', 'rutina', $routineId, "Rutina '{$routine->nombre}' asignada al usuario con ID {$userId}.");
        
        // Simular envío de correo al asignar
        $this->sendSimulatedRoutineEmail($routineId, $userId);
    }

    public function sendSimulatedRoutineEmail(int $routineId, int $userId)
    {
        $routine = $this->repository->findById($routineId);
        $user = \App\Models\User::findOrFail($userId);

        $asunto = "Tu Coach ha asignado una nueva rutina: {$routine->nombre}";
        $cuerpo = "Hola {$user->nombre},\n\n" .
                  "Tu coach te ha asignado una nueva rutina de entrenamiento en FitZone.\n\n" .
                  "Detalles de la rutina:\n" .
                  "- Nombre: {$routine->nombre}\n" .
                  "- Nivel: " . ($routine->nivel ?? 'No especificado') . "\n" .
                  "- Duración estimada: " . ($routine->duracion ?? 'No especificada') . "\n\n" .
                  "¡Ingresa a la aplicación para ver la rutina completa y comenzar a entrenar!\n\n" .
                  "Atentamente,\n" .
                  "El equipo de FitZone";

        // Registrar en historial_correos
        $emailLog = $this->repository->logEmail([
            'id_usuario' => $userId,
            'id_rutina' => $routineId,
            'email' => $user->email,
            'asunto' => $asunto,
            'cuerpo' => $cuerpo,
            'estado' => 'success',
            'sent_at' => now(),
        ]);

        // Registrar en los logs de Laravel (simulación física de envío)
        Log::info("Simulación de envío de correo de rutina exitoso a {$user->email}. Asunto: {$asunto}");

        // Actualizar fecha de envío en la rutina
        $routine->update(['sent_at' => now()]);

        AuditService::log('envio_correo', 'rutina', $routineId, "Correo de rutina '{$routine->nombre}' simulado para el usuario {$user->email}.");

        return $emailLog;
    }

    public function getCoachRoutines(int $coachId)
    {
        return $this->repository->getByCoach($coachId);
    }

    public function getUserRoutines(int $userId)
    {
        return $this->repository->getAssignedToUser($userId);
    }

    public function getEmailLogs(?int $userId = null)
    {
        return $this->repository->getEmailHistory($userId);
    }
}
