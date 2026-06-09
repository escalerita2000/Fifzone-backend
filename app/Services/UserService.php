<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\AuditService;

class UserService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listUsers(array $filters, int $perPage = 10)
    {
        return $this->repository->getPaginatedUsers($filters, $perPage);
    }

    public function getUserById(int $id)
    {
        return $this->repository->findById($id, true);
    }

    public function updateUser(int $id, array $data)
    {
        $user = $this->repository->update($id, $data);
        AuditService::log('actualizar', 'usuario', $id, "Usuario {$user->nombre} actualizado.");
        return $user;
    }

    public function deleteUser(int $id)
    {
        $this->repository->delete($id);
        AuditService::log('eliminar', 'usuario', $id, "Usuario con ID {$id} eliminado lógicamente.");
    }

    public function restoreUser(int $id)
    {
        $this->repository->restore($id);
        AuditService::log('restaurar', 'usuario', $id, "Usuario con ID {$id} restaurado.");
    }
}
