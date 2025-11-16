<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create Admin role user
        DB::table('users')->insert([
            'user_id' => 1,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'name' => 'John Doe',
            'position' => 'System Administrator',
            'email' => 'admin@wellkenz.com',
            'contact' => '+63 917 123 4567',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create other admin staff
        DB::table('users')->insert([
            'user_id' => 2,
            'username' => 'supervisor',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'name' => 'Jane Smith',
            'position' => 'Operations Supervisor',
            'email' => 'supervisor@wellkenz.com',
            'contact' => '+63 917 234 5678',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'user_id' => 3,
            'username' => 'purchasing_mgr',
            'password' => Hash::make('password'),
            'role' => 'purchasing',
            'name' => 'Robert Johnson',
            'position' => 'Purchasing Manager',
            'email' => 'purchasing@wellkenz.com',
            'contact' => '+63 917 345 6789',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'user_id' => 4,
            'username' => 'inventory_mgr',
            'password' => Hash::make('password'),
            'role' => 'inventory',
            'name' => 'Maria Garcia',
            'position' => 'Inventory Manager',
            'email' => 'inventory@wellkenz.com',
            'contact' => '+63 917 456 7890',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}