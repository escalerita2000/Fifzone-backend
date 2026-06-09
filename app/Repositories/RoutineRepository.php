<?php

namespace App\Repositories;

use App\Models\Rutina;
use App\Models\HistorialCorreo;
use App\Models\User;

class RoutineRepository
{
    public function create(array $data): Rutina
    {
        return Rutina::create($data);
    }

    public function findById(int $id, bool $withTrashed = false): Rutina
    {
        $query = Rutina::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    public function update(int $id, array $data): Rutina
    {
        $routine = $this->findById($id);
        $routine->update($data);
        return $routine;
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $routine = $this->findById($id);
        $routine->update([
            'is_deleted' => true,
            'deleted_by' => $deletedBy ?? (auth()->check() ? auth()->id() : null)
        ]);
        return $routine->delete();
    }

    public function restore(int $id): bool
    {
        $routine = $this->findById($id, true);
        $routine->update([
            'is_deleted' => false,
            'deleted_by' => null
        ]);
        return $routine->restore();
    }

    public function assignToUser(int $routineId, int $userId): void
    {
        $routine = $this->findById($routineId);
        $user = User::findOrFail($userId);

        // Many-to-many attach (avoiding duplicates)
        if (!$user->rutinas()->where('rutina_usuario.id_rutina', $routineId)->exists()) {
            $user->rutinas()->attach($routineId, ['asignado_at' => now(), 'activo' => true]);
        }
        
        // Backward compatibility fallback: update id_usuario column directly in rutinas table
        $routine->update(['id_usuario' => $userId]);
    }

    public function getByCoach(int $coachId)
    {
        return Rutina::where('id_coach', $coachId)->latest()->get();
    }

    public function getAssignedToUser(int $userId)
    {
        return User::findOrFail($userId)->rutinas()->where('activo', true)->get();
    }

    public function logEmail(array $emailData): HistorialCorreo
    {
        return HistorialCorreo::create($emailData);
    }

    public function getEmailHistory(?int $userId = null)
    {
        $query = HistorialCorreo::with(['usuario', 'rutina']);
        if ($userId) {
            $query->where('id_usuario', $userId);
        }
        return $query->latest()->get();
    }
}
