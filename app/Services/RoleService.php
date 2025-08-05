<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
    ) {}

    public function create(array $data): Role
    {
        /** @var Role $role */
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['permissions'])) {
            $role->givePermissionTo($data['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function delete(Role $role)
    {
        $this->roleRepository->delete($role);
    }
}
