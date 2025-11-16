<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $employees = [
            [
                'username' => 'employee1',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Alice Brown',
                'position' => 'Senior Baker',
                'email' => 'alice@wellkenz.com',
                'contact' => '+63 917 567 8901',
            ],
            [
                'username' => 'employee2',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Bob Wilson',
                'position' => 'Pastry Chef',
                'email' => 'bob@wellkenz.com',
                'contact' => '+63 917 678 9012',
            ],
            [
                'username' => 'employee3',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Carol Davis',
                'position' => 'Cake Decorator',
                'email' => 'carol@wellkenz.com',
                'contact' => '+63 917 789 0123',
            ],
            [
                'username' => 'employee4',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'David Lee',
                'position' => 'Assistant Baker',
                'email' => 'david@wellkenz.com',
                'contact' => '+63 917 890 1234',
            ],
            [
                'username' => 'employee5',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Emma Taylor',
                'position' => 'Store Manager',
                'email' => 'emma@wellkenz.com',
                'contact' => '+63 917 901 2345',
            ],
        ];

        $startId = 5; // Continue from AdminSeeder user IDs
        foreach ($employees as $index => $employee) {
            DB::table('users')->insert([
                'user_id' => $startId + $index,
                'username' => $employee['username'],
                'password' => $employee['password'],
                'role' => $employee['role'],
                'name' => $employee['name'],
                'position' => $employee['position'],
                'email' => $employee['email'],
                'contact' => $employee['contact'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create some inactive employees for testing
        DB::table('users')->insert([
            'user_id' => $startId + count($employees),
            'username' => 'inactive_employee',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'name' => 'Frank Miller',
            'position' => 'Former Assistant',
            'email' => 'frank@wellkenz.com',
            'contact' => '+63 917 012 3456',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}