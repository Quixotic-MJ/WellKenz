<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. CLEANUP (Disable Foreign Keys to truncate safely)
        DB::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE suppliers RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE categories RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE items RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE purchase_orders RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE purchase_order_items RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE receiving_reports RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE inventory_batches RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE requisitions RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE requisition_items RESTART IDENTITY CASCADE');

        // ==========================================
        // 2. SEED USERS (The 5 Core Roles)
        // Password for everyone: "password"
        // ==========================================
        $users = [
            ['name' => 'Admin Aris', 'email' => 'admin@bakery.com', 'role' => 'admin'],
            ['name' => 'Supervisor Maam Sarah', 'email' => 'supervisor@bakery.com', 'role' => 'supervisor'],
            ['name' => 'Buyer Ben', 'email' => 'purchasing@bakery.com', 'role' => 'purchasing'],
            ['name' => 'Bodega Boy Jun', 'email' => 'inventory@bakery.com', 'role' => 'inventory'],
            ['name' => 'Baker Juan', 'email' => 'baker@bakery.com', 'role' => 'employee'],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'password_hash' => Hash::make('password'), // Laravel standard hashing
                'role' => $user['role'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================================
        // 3. SEED SUPPLIERS (Cebu Context)
        // ==========================================
        $suppliers = [
            ['name' => 'General Milling Corp', 'lead_time' => 2, 'tin' => '123-456-789'],
            ['name' => 'Carbon Market Egg Vendor', 'lead_time' => 1, 'tin' => '987-654-321'],
            ['name' => 'San Miguel Packaging', 'lead_time' => 5, 'tin' => '444-555-666'],
            ['name' => 'Cebu Dairy Distributors', 'lead_time' => 3, 'tin' => '111-222-333'],
            ['name' => 'Metro Gaisano Wholesale', 'lead_time' => 1, 'tin' => '777-888-999'],
        ];

        foreach ($suppliers as $s) {
            DB::table('suppliers')->insert([
                'name' => $s['name'],
                'lead_time_days' => $s['lead_time'],
                'tin_number' => $s['tin'],
                'created_at' => now(),
            ]);
        }

        // ==========================================
        // 4. SEED CATEGORIES & ITEMS (The "Truth")
        // ==========================================
        $categories = ['Ingredients', 'Packaging', 'Dairy', 'Cleaning'];
        foreach ($categories as $cat) {
            DB::table('categories')->insert(['name' => $cat]);
        }

        // Items Array: [CatID, Name, PurchaseUnit, StockUnit, Conversion, ReorderLvl, Perishable]
        $items = [
            [1, 'Bread Flour', 'Sack', 'kg', 25.00, 50.00, false], // 1 Sack = 25kg
            [1, 'White Sugar', 'Sack', 'kg', 50.00, 20.00, false], // 1 Sack = 50kg
            [3, 'Fresh Eggs (Large)', 'Tray', 'pcs', 30.00, 100.00, true], // 1 Tray = 30pcs
            [3, 'Full Cream Milk', 'Box', 'liters', 12.00, 10.00, true], // 1 Box = 12 Liters
            [1, 'Yeast', 'Pack', 'grams', 500.00, 1000.00, true], // 1 Pack = 500g
            [2, 'Cake Box (10x10)', 'Bundle', 'pcs', 100.00, 200.00, false],
            [1, 'Unsalted Butter', 'Block', 'grams', 225.00, 5000.00, true],
            [1, 'Cocoa Powder', 'Tin', 'grams', 1000.00, 3000.00, false],
        ];

        foreach ($items as $i) {
            DB::table('items')->insert([
                'category_id' => $i[0],
                'name' => $i[1],
                'sku' => 'SKU-' . strtoupper(substr($i[1], 0, 3)) . rand(100, 999),
                'purchase_unit' => $i[2],
                'stock_unit' => $i[3],
                'conversion_factor' => $i[4],
                'reorder_level' => $i[5],
                'is_perishable' => $i[6],
                'created_at' => now(),
            ]);
        }

        // ==========================================
        // 5. GENERATE 20 PURCHASE ORDERS & BATCHES
        // This populates the Warehouse
        // ==========================================
        for ($x = 1; $x <= 20; $x++) {
            $status = ($x <= 15) ? 'completed' : 'ordered'; // First 15 delivered, 5 pending
            $supplierId = rand(1, 5);
            
            // Create PO
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => 'PO-2023-' . str_pad($x, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierId,
                'created_by_user_id' => 3, // Purchasing Officer
                'status' => $status,
                'total_amount' => rand(5000, 20000),
                'ordered_at' => Carbon::now()->subDays(rand(5, 30)),
                'created_at' => now(),
            ]);

            // Add Random Items to PO
            $itemId = rand(1, 8);
            $qty = rand(5, 20); // e.g., 5 Sacks
            
            DB::table('purchase_order_items')->insert([
                'purchase_order_id' => $poId,
                'item_id' => $itemId,
                'requested_qty' => $qty,
                'received_qty' => ($status == 'completed') ? $qty : 0,
                'unit_price' => rand(500, 2500),
            ]);

            // IF COMPLETED: Create Receiving Report & FIFO Batch
            if ($status == 'completed') {
                // 1. Create Receiving Report
                $rrId = DB::table('receiving_reports')->insertGetId([
                    'purchase_order_id' => $poId,
                    'received_by_user_id' => 4, // Inventory Staff
                    'reference_no' => 'DR-' . rand(10000, 99999),
                    'remarks' => 'Received in good condition',
                    'received_at' => now(),
                ]);

                // 2. Calculate Stock Unit Qty (Sacks -> Kg)
                $item = DB::table('items')->where('id', $itemId)->first();
                $stockQty = $qty * $item->conversion_factor;

                // 3. Create Inventory Batch (FIFO)
                DB::table('inventory_batches')->insert([
                    'item_id' => $itemId,
                    'receiving_report_id' => $rrId,
                    'batch_code' => 'BATCH-' . date('Ymd') . '-' . $poId,
                    'initial_qty' => $stockQty,
                    'current_qty' => $stockQty, // Full batch
                    'cost_per_unit' => rand(10, 100),
                    'expiry_date' => ($item->is_perishable) ? Carbon::now()->addDays(rand(7, 90)) : null,
                    'created_at' => now(),
                ]);
            }
        }

        // ==========================================
        // 6. GENERATE 20 REQUISITIONS (Baker Requests)
        // Some Approved, Some Pending
        // ==========================================
        for ($y = 1; $y <= 20; $y++) {
            $reqStatus = ($y <= 10) ? 'approved' : 'pending';
            
            $reqId = DB::table('requisitions')->insertGetId([
                'control_no' => 'REQ-' . date('m') . '-' . str_pad($y, 4, '0', STR_PAD_LEFT),
                'requested_by_user_id' => 5, // Baker Juan
                'status' => $reqStatus,
                'approved_by_user_id' => ($reqStatus == 'approved') ? 2 : null, // Supervisor Sarah
                'approved_at' => ($reqStatus == 'approved') ? now() : null,
                'remarks' => 'Production for ' . date('l'),
                'created_at' => now(),
            ]);

            // Add Item to Requisition
            $reqItem = rand(1, 8);
            DB::table('requisition_items')->insert([
                'requisition_id' => $reqId,
                'item_id' => $reqItem,
                'requested_qty' => rand(2, 10), // Requesting 2-10 KG/PCS
                'issued_qty' => 0, // Not issued yet for this seed
                'notes' => 'Urgent'
            ]);
        }

        // ==========================================
        // 7. SEED SAMPLE NOTIFICATIONS
        // ==========================================
        $this->call(NotificationSeeder::class);
        $this->call(PurchasingNotificationSeeder::class);
    }
}