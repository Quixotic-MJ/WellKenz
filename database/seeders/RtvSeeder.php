<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RtvTransaction;
use App\Models\RtvItem;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Item;
use Carbon\Carbon;

class RtvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $suppliers = Supplier::take(3)->get();
        $purchaseOrders = PurchaseOrder::take(2)->get();
        $items = Item::take(5)->get();

        if ($suppliers->isEmpty() || $items->isEmpty()) {
            $this->command->info('No suppliers or items found. Please run other seeders first.');
            return;
        }

        // Create sample RTV transactions
        $rtvData = [
            [
                'rtv_number' => 'RTV-2024-001',
                'purchase_order_id' => $purchaseOrders->first()?->id,
                'supplier_id' => $suppliers->first()?->id,
                'return_date' => Carbon::now()->subDays(5),
                'status' => 'pending',
                'total_value' => 2800.00,
                'notes' => 'Items damaged during delivery'
            ],
            [
                'rtv_number' => 'RTV-2024-002',
                'purchase_order_id' => $purchaseOrders->skip(1)->first()?->id,
                'supplier_id' => $suppliers->skip(1)->first()?->id,
                'return_date' => Carbon::now()->subDays(10),
                'status' => 'completed',
                'total_value' => 960.00,
                'notes' => 'Near expiry date items returned'
            ],
            [
                'rtv_number' => 'RTV-2024-003',
                'purchase_order_id' => $purchaseOrders->first()?->id,
                'supplier_id' => $suppliers->first()?->id,
                'return_date' => Carbon::now()->subDays(3),
                'status' => 'processed',
                'total_value' => 1500.00,
                'notes' => 'Wrong specifications received'
            ]
        ];

        foreach ($rtvData as $data) {
            $rtv = RtvTransaction::create($data);

            // Create associated RTV items
            RtvItem::create([
                'rtv_id' => $rtv->id,
                'item_id' => $items->first()?->id,
                'quantity_returned' => 10.000,
                'unit_cost' => 280.00,
                'reason' => $data['rtv_number'] === 'RTV-2024-001' ? 'Damaged/Wet upon delivery' : 
                           ($data['rtv_number'] === 'RTV-2024-002' ? 'Near Expiry Date' : 'Wrong specifications received')
            ]);

            // Add a second item for some RTVs
            if ($rtv->id % 2 == 0 && $items->count() > 1) {
                RtvItem::create([
                    'rtv_id' => $rtv->id,
                    'item_id' => $items->skip(1)->first()?->id,
                    'quantity_returned' => 5.000,
                    'unit_cost' => 120.00,
                    'reason' => 'Damaged/Wet upon delivery'
                ]);
            }
        }

        $this->command->info('RTV sample data created successfully!');
    }
}