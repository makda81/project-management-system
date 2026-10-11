<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $managerPermissions = [
            'project.create',
            'project.update',
            'project.delete',
            'member.manage',
            'task.create',
            'task.update',
            'task.delete',
            'task.approve',
        ];

        foreach ([...$managerPermissions, 'task.move'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('manager', 'web')->syncPermissions($managerPermissions);
        Role::findOrCreate('member', 'web')->syncPermissions(['task.move']);
    }
}
