<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Permissions grouped by admin area. Manage these from
     * Admin > Roles — no code change needed for new areas.
     *
     * @return array<string, array<int, string>>
     */
    public static function grouped(): array
    {
        return [
            'dashboard' => ['dashboard.view'],
            'catalog' => ['products.view', 'products.manage', 'categories.manage', 'stock.manage'],
            'orders' => ['orders.view', 'orders.manage', 'returns.manage', 'reviews.moderate'],
            'customers' => ['customers.view', 'customers.manage'],
            'marketing' => ['campaigns.manage', 'coupons.manage', 'flash-sales.manage'],
            'content' => ['languages.manage', 'translations.manage', 'settings.manage', 'theme.manage'],
            'team' => ['users.view', 'users.manage', 'roles.manage'],
            'system' => ['queue.monitor', 'backups.manage', 'activity.view'],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (static::grouped() as $perms) {
            foreach ($perms as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }

        $all = Permission::pluck('name')->all();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web'])
            ->syncPermissions($all);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(array_diff($all, ['roles.manage']));

        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view', 'products.view', 'products.manage', 'categories.manage',
                'stock.manage', 'orders.view', 'orders.manage', 'returns.manage',
                'reviews.moderate', 'customers.view', 'campaigns.manage', 'coupons.manage',
                'flash-sales.manage', 'activity.view',
            ]);

        Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view', 'orders.view', 'returns.manage',
                'reviews.moderate', 'customers.view', 'activity.view',
            ]);
    }
}
