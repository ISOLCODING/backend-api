<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Semua permission yang tersedia di sistem KASIRIN AJA.
     */
    private array $permissions = [
        'manage-products',
        'manage-stock',
        'manage-staff',
        'manage-roles',
        'create-transaction',
        'view-transactions',
        'view-reports',
        'export-reports',
        'manage-settings',
        'manage-printers',
        'manage-tax',
        'manage-backup',
    ];

    /**
     * Mapping permission per role.
     * +---------------------------+----------+-------------+--------+
     * | Permission                | Admin    | Supervisor  | Kasir  |
     * +---------------------------+----------+-------------+--------+
     */
    private array $rolePermissions = [
        'admin' => [
            'manage-products',
            'manage-stock',
            'manage-staff',
            'manage-roles',
            'create-transaction',
            'view-transactions',
            'view-reports',
            'export-reports',
            'manage-settings',
            'manage-printers',
            'manage-tax',
            'manage-backup',
        ],
        'supervisor' => [
            'manage-products',
            'manage-stock',
            'create-transaction',
            'view-transactions',
            'view-reports',
            'export-reports',
            'manage-settings',
            'manage-printers',
            'manage-tax',
        ],
        'kasir' => [
            'create-transaction',
            'view-transactions',
            'manage-printers',
        ],
    ];

    public function run(): void
    {
        // Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat semua permission
        foreach ($this->permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'sanctum']);
        }

        // Buat roles dan assign permission
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);

            // Juga buat role untuk guard sanctum (API)
            $roleApi = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
            $roleApi->syncPermissions(
                Permission::whereIn('name', $permissions)->where('guard_name', 'sanctum')->get()
            );
        }

        $this->command->info('✅ Permissions & Roles seeded successfully.');
        $this->command->table(
            ['Role', 'Jumlah Permission'],
            collect($this->rolePermissions)->map(fn ($perms, $role) => [$role, count($perms)])->toArray()
        );
    }
}
