<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | POS / Sales
            |--------------------------------------------------------------------------
            */
            'pos.use',
            'sale.view',
            'sale.create',
            'sale.return',
            'sale.cancel',
            'receipt.print',

            /*
            |--------------------------------------------------------------------------
            | Sales Returns
            |--------------------------------------------------------------------------
            */
            'sales_return.view',
            'sales_return.create',
            'sales_return.approve',
            'sales_return.reject',
            'sales_return.cancel',

            /*
            |--------------------------------------------------------------------------
            | Intelligence
            |--------------------------------------------------------------------------
            */
            'intelligence.view',
            'intelligence.generate',
            'intelligence.alert.manage',

            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'product.manage',

            /*
            |--------------------------------------------------------------------------
            | Product Setup
            |--------------------------------------------------------------------------
            */
            'product_type.manage',
            'product_category.manage',
            'unit.manage',
            'pricing.manage',

            /*
            |--------------------------------------------------------------------------
            | Suppliers / Purchases
            |--------------------------------------------------------------------------
            */
            'supplier.view',
            'supplier.manage',

            'purchase.view',
            'purchase.create',
            'purchase.update',
            'purchase.manage',

            'supplier_payment.manage',

            /*
            |--------------------------------------------------------------------------
            | Inventory / Stock
            |--------------------------------------------------------------------------
            */
            'stock.view',
            'stock.adjust',
            'stock.count',
            'stock.movement.view',
            'expiry.view',

            /*
            |--------------------------------------------------------------------------
            | Stock Adjustments
            |--------------------------------------------------------------------------
            */
            'stock_adjustment.view',
            'stock_adjustment.create',
            'stock_adjustment.approve',
            'stock_adjustment.reject',
            'stock_adjustment.cancel',

            /*
            |--------------------------------------------------------------------------
            | Stock Transfers
            |--------------------------------------------------------------------------
            */
            'stock_transfer.view',
            'stock_transfer.create',
            'stock_transfer.approve',
            'stock_transfer.reject',
            'stock_transfer.cancel',
            'stock_transfer.dispatch',
            'stock_transfer.receive',

            /*
            |--------------------------------------------------------------------------
            | Inventory Alerts
            |--------------------------------------------------------------------------
            */
            'inventory_alert.view',
            'inventory_alert.manage',
            'inventory_alert.generate',

            /*
            |--------------------------------------------------------------------------
            | Prescription
            |--------------------------------------------------------------------------
            */
            'prescription.view',
            'prescription.manage',

            /*
            |--------------------------------------------------------------------------
            | Expenses
            |--------------------------------------------------------------------------
            */
            'expense_category.manage',
            'expense.view',
            'expense.create',
            'expense.update',
            'expense.void',
            'expense.manage',

            /*
            |--------------------------------------------------------------------------
            | Daily Closing
            |--------------------------------------------------------------------------
            */
            'daily_closing.view',
            'daily_closing.create',
            'daily_closing.submit',
            'daily_closing.verify',
            'daily_closing.reject',
            'daily_closing.manage',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */
            'report.view',
            'report.sales',
            'report.stock',
            'report.purchase',
            'report.profit',
            'report.expense',
            'report.prescription',

            /*
            |--------------------------------------------------------------------------
            | Users / Settings
            |--------------------------------------------------------------------------
            */
            'user.view',
            'user.manage',
            'setting.view',
            'setting.manage',

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */
            'ai.use',
            'ai.use_basic',
            'ai.history.view',

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        $admin = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $owner = Role::query()->firstOrCreate([
            'name' => 'Owner',
            'guard_name' => 'web',
        ]);

        $pharmacist = Role::query()->firstOrCreate([
            'name' => 'Pharmacist',
            'guard_name' => 'web',
        ]);

        $cashier = Role::query()->firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);

        $storekeeper = Role::query()->firstOrCreate([
            'name' => 'Storekeeper',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Admin / Owner
        |--------------------------------------------------------------------------
        | Full system control.
        */
        $admin->syncPermissions($permissions);
        $owner->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Pharmacist
        |--------------------------------------------------------------------------
        | Can sell, view products/stock, manage prescriptions, create returns,
        | create stock adjustments, receive transfers for branch, and close day.
        | Cannot approve/reject sensitive actions.
        */
        $pharmacist->syncPermissions([
            'dashboard.view',

            'pos.use',
            'sale.view',
            'sale.create',
            'sale.return',
            'receipt.print',

            'sales_return.view',
            'sales_return.create',

            'product.view',

            'stock.view',
            'stock.movement.view',
            'expiry.view',

            'stock_adjustment.view',
            'stock_adjustment.create',

            'stock_transfer.view',
            'stock_transfer.receive',

            'inventory_alert.view',

            'prescription.view',
            'prescription.manage',

            'expense.view',
            'expense.create',

            'daily_closing.view',
            'daily_closing.create',
            'daily_closing.submit',

            'report.view',
            'report.sales',
            'report.stock',
            'report.prescription',

            'ai.use',
            'ai.history.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cashier
        |--------------------------------------------------------------------------
        | POS-focused. Can sell, print receipts, record POS expenses,
        | create sales return request, and submit daily closing.
        | Cannot approve returns, transfers, adjustments, or verify closing.
        */
        $cashier->syncPermissions([
            'dashboard.view',

            'pos.use',
            'sale.view',
            'sale.create',
            'receipt.print',

            'sales_return.view',
            'sales_return.create',

            'product.view',

            'stock.view',
            'expiry.view',

            'inventory_alert.view',

            'expense.view',
            'expense.create',

            'daily_closing.view',
            'daily_closing.create',
            'daily_closing.submit',

            'ai.use_basic',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Storekeeper
        |--------------------------------------------------------------------------
        | Stock/purchase-focused. Can manage products, purchases, stock,
        | stock adjustments, and stock transfers. Can dispatch/receive transfers.
        | Cannot approve/reject final stock decisions unless Owner/Admin.
        */
        $storekeeper->syncPermissions([
            'dashboard.view',

            'product.view',
            'product.create',
            'product.update',
            'product.manage',

            'product_type.manage',
            'product_category.manage',
            'unit.manage',
            'pricing.manage',

            'supplier.view',
            'supplier.manage',

            'purchase.view',
            'purchase.create',
            'purchase.update',
            'purchase.manage',

            'stock.view',
            'stock.adjust',
            'stock.count',
            'stock.movement.view',
            'expiry.view',

            'stock_adjustment.view',
            'stock_adjustment.create',

            'stock_transfer.view',
            'stock_transfer.create',
            'stock_transfer.dispatch',
            'stock_transfer.receive',

            'inventory_alert.view',

            'report.view',
            'report.stock',
            'report.purchase',

            'ai.use_basic',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}