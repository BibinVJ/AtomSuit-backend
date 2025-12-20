<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;

class RoleService extends BaseService
{
    public function __construct(
        protected readonly RoleRepository $roleRepository,
    ) {
        $this->repository = $roleRepository;
    }

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

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $role): void
    {
        /** @var \App\Models\Role $role */
        if ($role->users()->exists()) {
            throw new \Exception('Role is assigned to users and cannot be hard deleted.');
        }
    }
}
