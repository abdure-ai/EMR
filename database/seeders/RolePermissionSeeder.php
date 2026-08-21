<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission set covers Modules 1-4 (auth/roles, patients, appointments,
     * queue) plus billing/settings pulled forward from Module 10 ahead of
     * schedule; later modules (prescriptions, inventory, reports) will add
     * their own permissions here as they're built.
     */
    protected array $permissions = [
        'patients.view',
        'patients.create',
        'patients.update',
        'patients.delete',
        'patients.medical.view',
        'patients.medical.update',
        'appointments.view',
        'appointments.create',
        'appointments.update',
        'queue.view',
        'queue.manage',
        'services.manage',
        'billing.view',
        'billing.process',
        'prescriptions.view',
        'prescriptions.dispense',
        'inventory.view',
        'inventory.manage',
        'followups.view',
        'followups.manage',
        'settings.manage',
        'users.manage',
        'reports.view',
        'audit.view',
    ];

    /**
     * Roles from the doc's §7 access table. Super Admin bypasses all checks via
     * Gate::before in AuthServiceProvider rather than being assigned every
     * permission explicitly.
     */
    protected array $rolePermissions = [
        'Clinic Manager' => [
            'patients.view', 'patients.create', 'patients.update', 'patients.delete',
            'patients.medical.view', 'patients.medical.update',
            'appointments.view', 'appointments.create', 'appointments.update',
            'queue.view', 'queue.manage', 'services.manage',
            'billing.view', 'billing.process',
            'prescriptions.view', 'prescriptions.dispense',
            'inventory.view', 'inventory.manage',
            'followups.view', 'followups.manage', 'settings.manage',
            'reports.view', 'audit.view',
        ],
        'Reception' => [
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update',
            'queue.view', 'queue.manage',
            'followups.view', 'followups.manage',
        ],
        'Practitioner' => [
            'patients.view', 'patients.medical.view', 'patients.medical.update',
            'appointments.view', 'queue.view', 'queue.manage',
        ],
        'Pharmacist' => [
            'patients.view', 'prescriptions.view', 'prescriptions.dispense',
            'inventory.view', 'inventory.manage',
        ],
        'Cashier' => [
            'patients.view', 'billing.view', 'billing.process',
        ],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
