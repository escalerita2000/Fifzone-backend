<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getPaginatedUsers(array $filters, int $perPage = 10)
    {
        $query = User::query();

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('nombre', "%{$search}%")
                  ->orWhereLike('email', "%{$search}%");
            });
        }

        if (!empty($filters['rol'])) {
            $query->where('rol', strtolower($filters['rol']));
        }

        if (isset($filters['activo'])) {
            $query->where('activo', (bool) $filters['activo']);
        }

        if (!empty($filters['plan'])) {
            $query->where('plan', strtoupper($filters['plan']));
        }

        // Gestionar borrado lógico
        if (isset($filters['deleted'])) {
            if ($filters['deleted'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['deleted'] === 'with') {
                $query->withTrashed();
            }
        }

        return $query->latest('id_usuario')->paginate($perPage);
    }

    public function findById(int $id, bool $withTrashed = false)
    {
        $query = User::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $user = $this->findById($id, true);
        $user->update($data);
        return $user;
    }

    public function delete(int $id, ?int $deletedBy = null)
    {
        $user = $this->findById($id);
        $user->update([
            'is_deleted' => true,
            'deleted_by' => $deletedBy ?? (auth()->check() ? auth()->id() : null)
        ]);
        return $user->delete();
    }

    public function restore(int $id)
    {
        $user = $this->findById($id, true);
        $user->update([
            'is_deleted' => false,
            'deleted_by' => null
        ]);
        return $user->restore();
    }
}
