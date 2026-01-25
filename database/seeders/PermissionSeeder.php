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
            'activity' => [
                'activity.dashboard', 'activity.feed', 'activity.delete', 'activity.my',
            ],
            'backup' => [
                'backups.view', 'backups.create', 'backups.download', 'backups.delete', 'backups.manage-schedules', 'backups.cleanup',
            ],
            'translate' => [
                'translations.view', 'translations.create', 'translations.update', 'translations.delete', 'translations.scan', 'translations.import', 'translations.export', 'translations.ai-translate',
            ],
            'pages' => [
                'pages.view', 'pages.create', 'pages.edit', 'pages.delete',
            ],
            'campaigns' => [
                'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
            ],
            'expense-categories' => [
                'expense-categories.view', 'expense-categories.create', 'expense-categories.edit', 'expense-categories.delete',
            ],
            'expenses' => [
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
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
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => $guard]);
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
            'activity.dashboard', 'activity.feed', 'activity.delete',
            'pages.view', 'pages.create', 'pages.edit',
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
            'expense-categories.view', 'expense-categories.create', 'expense-categories.edit', 'expense-categories.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
        ])->get();
        $admin->syncPermissions($adminPerms);

        $staffPerms = Permission::whereIn('name', [
            'dashboard.view',
            'campaigns.view',
            'expense-categories.view',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'pages.view',
            'profile.update',
        ])->get();
        $staff->syncPermissions($staffPerms);

        $userPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
        ])->get();
        $user->syncPermissions($userPerms);
    }
}
