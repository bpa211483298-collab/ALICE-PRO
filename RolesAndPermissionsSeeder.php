<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'edit profile',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage settings',
            'view reports',
            'create projects',
            'edit projects',
            'delete projects',
            'deploy projects',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'admin']);
        $adminPermissions = [
            'view dashboard',
            'edit profile',
            'manage users',
            'view reports',
            'create projects',
            'edit projects',
            'delete projects',
            'deploy projects',
        ];
        $role->givePermissionTo($adminPermissions);

        $role = Role::create(['name' => 'user']);
        $userPermissions = [
            'view dashboard',
            'edit profile',
            'create projects',
            'edit projects',
            'deploy projects',
        ];
        $role->givePermissionTo($userPermissions);

        // Assign super-admin role to first user
        if ($user = \App\Models\User::first()) {
            $user->assignRole('super-admin');
        }
    }
}
