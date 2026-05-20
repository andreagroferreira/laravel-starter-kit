<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage settings',
            'impersonate users',
            'view audit log',
            'manage customers',
            'manage content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $superAdmin->syncPermissions($permissions);
        $admin->syncPermissions([
            'view dashboard',
            'manage users',
            'manage settings',
            'view audit log',
            'manage customers',
            'manage content',
        ]);
        $editor->syncPermissions([
            'view dashboard',
            'manage customers',
            'manage content',
        ]);
        $viewer->syncPermissions([
            'view dashboard',
        ]);
    }
}
