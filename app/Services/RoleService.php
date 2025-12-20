<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Models\Role;

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
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function delete(Role $role, bool $force = false)
    {
        if ($force) {
            if ($role->users()->exists()) {
                throw new \Exception('Role is assigned to users and cannot be hard deleted.');
            }
            return $this->roleRepository->forceDelete($role);
        }

        return $this->roleRepository->delete($role);
    }

    public function restore(int $id): Role
    {
        $role = Role::onlyTrashed()->findOrFail($id);
        $this->roleRepository->restore($role);

        return $role;
    }
}
