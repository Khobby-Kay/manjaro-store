<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AlibabaPermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Alibaba permissions
        $permissions = [
            'view_alibaba_module',
            'configure_alibaba_api',
            'manage_alibaba_suppliers',
            'manage_alibaba_products',
            'manage_alibaba_orders',
            'view_alibaba_import_logs',
            'import_alibaba_products',
            'sync_alibaba_orders'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign permissions to super admin role if exists
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        $this->command->info('Alibaba permissions created and assigned to admin roles.');
    }
}
