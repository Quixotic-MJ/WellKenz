<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovedRequestItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // This table should be populated by items that were approved 
        // from the 'item_requests' table and are waiting to be created.
        $approvedItems = [
            [
                // This corresponds to 'item_req_id' => 2 from ItemRequestsSeeder
                'req_id' => null, // It's from an item_request, not a requisition
                'item_id' => null, // It's not a system item yet
                'item_name' => 'Cake Turntable (12-inch)',
                'item_description' => 'Heavy-duty cake turntable for professional decorating',
                'item_unit' => 'piece',
                'requested_quantity' => 2.00,
                'approved_quantity' => 2.00,
                'req_ref' => 'IR-2', // Matches the 'IR-' + item_req_id logic
                'created_as_item' => false, // This is KEY. It means it's PENDING.
                'created_at' => '2024-11-14 16:45:00',
                'updated_at' => '2024-11-14 16:45:00'
            ],
            [
                // This corresponds to 'item_req_id' => 7 from ItemRequestsSeeder
                'req_id' => null, 
                'item_id' => null,
                'item_name' => 'Silicone Baking Mats',
                'item_description' => 'Non-stick silicone baking mats for various sheet pan sizes',
                'item_unit' => 'piece',
                'requested_quantity' => 6.00,
                'approved_quantity' => 6.00,
                'req_ref' => 'IR-7',
                'created_as_item' => false, // This is KEY. It means it's PENDING.
                'created_at' => '2024-11-14 14:30:00',
                'updated_at' => '2024-11-14 14:30:00'
            ],
            // Note: 'Professional Mixing Bowls Set' (ID 3) was 'fulfilled',
            // so we assume it was already created.
            // We are only seeding the 'approved' but not-yet-created items.
        ];

        foreach ($approvedItems as $item) {
            DB::table('approved_request_items')->insert($item);
        }
    }
}