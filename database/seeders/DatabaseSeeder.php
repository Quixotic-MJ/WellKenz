<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- THIS IS REQUIRED

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

        // ====================================================================
        // **** THIS IS THE FIX ****
        // This block re-synchronizes all the auto-increment counters
        // after the seeders have manually inserted data.
        // ====================================================================
        
        $this->command->info('Syncing auto-increment counters...');
        
        DB::select("SELECT setval('users_user_id_seq', COALESCE((SELECT MAX(user_id) FROM users), 1), (SELECT MAX(user_id) IS NOT NULL FROM users));");
        DB::select("SELECT setval('categories_cat_id_seq', COALESCE((SELECT MAX(cat_id) FROM categories), 1), (SELECT MAX(cat_id) IS NOT NULL FROM categories));");
        DB::select("SELECT setval('suppliers_sup_id_seq', COALESCE((SELECT MAX(sup_id) FROM suppliers), 1), (SELECT MAX(sup_id) IS NOT NULL FROM suppliers));");
        DB::select("SELECT setval('items_item_id_seq', COALESCE((SELECT MAX(item_id) FROM items), 1), (SELECT MAX(item_id) IS NOT NULL FROM items));");
        DB::select("SELECT setval('item_requests_item_req_id_seq', COALESCE((SELECT MAX(item_req_id) FROM item_requests), 1), (SELECT MAX(item_req_id) IS NOT NULL FROM item_requests));");
        DB::select("SELECT setval('requisitions_req_id_seq', COALESCE((SELECT MAX(req_id) FROM requisitions), 1), (SELECT MAX(req_id) IS NOT NULL FROM requisitions));");
        DB::select("SELECT setval('requisition_items_req_item_id_seq', COALESCE((SELECT MAX(req_item_id) FROM requisition_items), 1), (SELECT MAX(req_item_id) IS NOT NULL FROM requisition_items));");
        DB::select("SELECT setval('approved_request_items_req_item_id_seq', COALESCE((SELECT MAX(req_item_id) FROM approved_request_items), 1), (SELECT MAX(req_item_id) IS NOT NULL FROM approved_request_items));");
        DB::select("SELECT setval('purchase_orders_po_id_seq', COALESCE((SELECT MAX(po_id) FROM purchase_orders), 1), (SELECT MAX(po_id) IS NOT NULL FROM purchase_orders));");
        DB::select("SELECT setval('purchase_items_pi_id_seq', COALESCE((SELECT MAX(pi_id) FROM purchase_items), 1), (SELECT MAX(pi_id) IS NOT NULL FROM purchase_items));");
        DB::select("SELECT setval('inventory_transactions_trans_id_seq', COALESCE((SELECT MAX(trans_id) FROM inventory_transactions), 1), (SELECT MAX(trans_id) IS NOT NULL FROM inventory_transactions));");
        DB::select("SELECT setval('acknowledge_receipts_ar_id_seq', COALESCE((SELECT MAX(ar_id) FROM acknowledge_receipts), 1), (SELECT MAX(ar_id) IS NOT NULL FROM acknowledge_receipts));");
        DB::select("SELECT setval('memos_memo_id_seq', COALESCE((SELECT MAX(memo_id) FROM memos), 1), (SELECT MAX(memo_id) IS NOT NULL FROM memos));");
        
        // ** THIS LINE IS NOW FIXED (not_id -> notif_id) **
        DB::select("SELECT setval('notifications_notif_id_seq', COALESCE((SELECT MAX(notif_id) FROM notifications), 1), (SELECT MAX(notif_id) IS NOT NULL FROM notifications));");

        $this->command->info('All counters re-synced!');
    }
}