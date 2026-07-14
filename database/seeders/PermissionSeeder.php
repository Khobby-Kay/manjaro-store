<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Alibaba Integration Permissions
        $alibabaPermissions = [
            'alibaba_management',
            'alibaba_dashboard',
            'alibaba_settings',
            'alibaba_suppliers',
            'alibaba_products',
            'alibaba_orders',
            'alibaba_import_logs',
            'alibaba_export_data',
            'alibaba_api_testing',
        ];

        // Staff Activity System Permissions
        $staffActivityPermissions = [
            'view_staff_activity',
            'view_staff_activity_dashboard',
            'view_staff_activity_logs',
            'view_staff_activity_users',
            'export_staff_activity',
            'clear_staff_activity_logs',
            'manage_staff_activity_settings',
        ];

        $allNewPermissions = array_merge($alibabaPermissions, $staffActivityPermissions);

        foreach ($allNewPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign all new permissions to the 'Super Admin' role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($allNewPermissions);
        }
    }
}