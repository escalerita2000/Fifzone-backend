<?php

namespace App\Repositories;

use App\Models\Ejercicio;

class ExerciseRepository
{
    public function getFilteredExercises(array $filters)
    {
        $query = Ejercicio::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('nombre', "%{$search}%")
                  ->orWhereLike('descripcion', "%{$search}%");
            });
        }

        if (!empty($filters['grupo_muscular'])) {
            $query->where('grupo_muscular', $filters['grupo_muscular']);
        }

        if (!empty($filters['dificultad'])) {
            $query->where('dificultad', $filters['dificultad']);
        }

        if (!empty($filters['equipamiento'])) {
            $query->where('equipamiento', $filters['equipamiento']);
        }

        if (isset($filters['deleted'])) {
            if ($filters['deleted'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['deleted'] === 'with') {
                $query->withTrashed();
            }
        }

        return $query->latest()->get();
    }

    public function findById(int $id, bool $withTrashed = false): Ejercicio
    {
        $query = Ejercicio::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Ejercicio
    {
        return Ejercicio::create($data);
    }

    public function update(int $id, array $data): Ejercicio
    {
        $exercise = $this->findById($id);
        $exercise->update($data);
        return $exercise;
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $exercise = $this->findById($id);
        $exercise->update([
            'is_deleted' => true,
            'deleted_by' => $deletedBy ?? (auth()->check() ? auth()->id() : null)
        ]);
        return $exercise->delete();
    }

    public function restore(int $id): bool
    {
        $exercise = $this->findById($id, true);
        $exercise->update([
            'is_deleted' => false,
            'deleted_by' => null
        ]);
        return $exercise->restore();
    }
}
