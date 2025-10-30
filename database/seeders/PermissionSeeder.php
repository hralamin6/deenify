<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $groups = [
            'dashboard' => ['dashboard.view'],
            'users' => [
                'users.view', 'users.create', 'users.update', 'users.delete', 'users.assign-roles',
            ],
            'roles' => [
                'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign-permissions',
            ],
            'settings' => [
                'settings.view', 'settings.update', 'settings.run-commands',
            ],
            'media' => [
                'media.view', 'media.upload', 'media.delete',
            ],
            'profile' => [
                'profile.update',
            ],
        ];

        // Create permissions
        foreach ($groups as $items) {
            foreach ($items as $name) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        // Create roles
        $super = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);

        // Assign permissions
        $allPerms = Permission::where('guard_name', $guard)->get();
        $super->syncPermissions($allPerms);

        $adminPerms = Permission::whereIn('name', [
            'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.assign-roles',
            'roles.view', 'roles.create', 'roles.update', 'roles.assign-permissions',
            'settings.view', 'settings.update',
            'media.view', 'media.upload',
            'profile.update',
        ])->get();
        $admin->syncPermissions($adminPerms);

        $userPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
        ])->get();
        $user->syncPermissions($userPerms);
    }
}

