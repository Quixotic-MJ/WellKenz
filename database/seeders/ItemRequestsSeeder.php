<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemRequestsSeeder extends Seeder
{
    public function run()
    {
        $itemRequests = [
            [
                'item_req_id' => 1,
                'item_req_name' => 'Extra Large Piping Tips Set',
                'item_req_unit' => 'set',
                'item_req_quantity' => 5,
                'item_req_description' => 'Professional piping tips set for cake decorating',
                'item_req_status' => 'pending',
                'requested_by' => 5, // Alice Brown
                'approved_by' => null,
                'created_at' => '2024-11-15 09:30:00',
                'updated_at' => '2024-11-15 09:30:00'
            ],
            [
                'item_req_id' => 2,
                'item_req_name' => 'Cake Turntable (12-inch)',
                'item_req_unit' => 'piece',
                'item_req_quantity' => 2,
                'item_req_description' => 'Heavy-duty cake turntable for professional decorating',
                'item_req_status' => 'approved',
                'requested_by' => 6, // Bob Wilson
                'approved_by' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-14 14:15:00',
                'updated_at' => '2024-11-14 16:45:00'
            ],
            [
                'item_req_id' => 3,
                'item_req_name' => 'Professional Mixing Bowls Set',
                'item_req_unit' => 'set',
                'item_req_quantity' => 3,
                'item_req_description' => 'Stainless steel mixing bowls set (3 sizes)',
                'item_req_status' => 'fulfilled',
                'requested_by' => 7, // Carol Davis
                'approved_by' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-13 11:20:00',
                'updated_at' => '2024-11-13 15:30:00'
            ],
            [
                'item_req_id' => 4,
                'item_req_name' => 'Rolling Pin (French Style)',
                'item_req_unit' => 'piece',
                'item_req_quantity' => 4,
                'item_req_description' => 'French style rolling pin for pastry work',
                'item_req_status' => 'pending',
                'requested_by' => 8, // David Lee
                'approved_by' => null,
                'created_at' => '2024-11-15 13:45:00',
                'updated_at' => '2024-11-15 13:45:00'
            ],
            [
                'item_req_id' => 5,
                'item_req_name' => 'Digital Food Scale',
                'item_req_unit' => 'piece',
                'item_req_quantity' => 1,
                'item_req_description' => 'Precision digital food scale for accurate measurements',
                'item_req_status' => 'rejected',
                'requested_by' => 9, // Emma Taylor
                'approved_by' => 2, // Jane Smith (Supervisor)
                'item_req_reject_reason' => 'Not needed at this time',
                'created_at' => '2024-11-12 16:30:00',
                'updated_at' => '2024-11-13 09:15:00'
            ],
            [
                'item_req_id' => 6,
                'item_req_name' => 'Instant-read Thermometer',
                'item_req_unit' => 'piece',
                'item_req_quantity' => 2,
                'item_req_description' => 'Fast-reading instant thermometer for food safety',
                'item_req_status' => 'pending',
                'requested_by' => 5, // Alice Brown
                'approved_by' => null,
                'created_at' => '2024-11-15 15:20:00',
                'updated_at' => '2024-11-15 15:20:00'
            ],
            [
                'item_req_id' => 7,
                'item_req_name' => 'Silicone Baking Mats',
                'item_req_unit' => 'piece',
                'item_req_quantity' => 6,
                'item_req_description' => 'Non-stick silicone baking mats for various sheet pan sizes',
                'item_req_status' => 'approved',
                'requested_by' => 6, // Bob Wilson
                'approved_by' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-14 10:45:00',
                'updated_at' => '2024-11-14 14:30:00'
            ],
            [
                'item_req_id' => 8,
                'item_req_name' => 'Decorator Spatula Set',
                'item_req_unit' => 'set',
                'item_req_quantity' => 2,
                'item_req_description' => 'Offset spatula set for cake frosting',
                'item_req_status' => 'partially_fulfilled',
                'requested_by' => 7, // Carol Davis
                'approved_by' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-10 12:00:00',
                'updated_at' => '2024-11-14 17:20:00'
            ],
        ];

        foreach ($itemRequests as $request) {
            DB::table('item_requests')->insert($request);
        }
    }
}