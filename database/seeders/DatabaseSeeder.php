<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // Users (must be first - other tables reference users)
            AdminSeeder::class,
            EmployeeSeeder::class,
            
            // Master data (categories and suppliers)
            CategoriesSeeder::class,
            SuppliersSeeder::class,
            
            // Items (referenced by many tables)
            ItemsSeeder::class,
            
            // Item requests and requisitions
            ItemRequestsSeeder::class,
            RequisitionsSeeder::class,
            RequisitionItemsSeeder::class,
            
            // New approved request items table
            ApprovedRequestItemsSeeder::class,
            
            // Purchase orders and related tables
            PurchaseOrdersSeeder::class,
            PurchaseItemsSeeder::class,
            
            // Transactions and receipts
            InventoryTransactionsSeeder::class,
            AcknowledgeReceiptsSeeder::class,
            
            // Supporting tables
            MemosSeeder::class,
            NotificationsSeeder::class,
        ]);
    }
}