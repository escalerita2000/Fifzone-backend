<?php

namespace App\Services;

use App\Repositories\ExerciseRepository;
use App\Services\AuditService;

class ExerciseService
{
    protected $repository;

    public function __construct(ExerciseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listExercises(array $filters)
    {
        return $this->repository->getFilteredExercises($filters);
    }

    public function getExercise(int $id)
    {
        return $this->repository->findById($id);
    }

    public function createExercise(array $data)
    {
        $exercise = $this->repository->create($data);
        AuditService::log('crear', 'ejercicio', $exercise->id, "Ejercicio '{$exercise->nombre}' creado.");
        return $exercise;
    }

    public function updateExercise(int $id, array $data)
    {
        $exercise = $this->repository->update($id, $data);
        AuditService::log('actualizar', 'ejercicio', $id, "Ejercicio '{$exercise->nombre}' actualizado.");
        return $exercise;
    }

    public function deleteExercise(int $id)
    {
        $this->repository->delete($id);
        AuditService::log('eliminar', 'ejercicio', $id, "Ejercicio con ID {$id} eliminado lógicamente.");
    }

    public function restoreExercise(int $id)
    {
        $this->repository->restore($id);
        AuditService::log('restaurar', 'ejercicio', $id, "Ejercicio con ID {$id} restaurado.");
    }
}
