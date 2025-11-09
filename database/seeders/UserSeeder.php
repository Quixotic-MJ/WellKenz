<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Don't delete users if they have associated notifications or other data
        // DB::table('users')->delete();

        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'name' => 'Maria Santos',
                'position' => 'System Administrator',
                'email' => 'maria.santos@wellkenz.com',
                'contact' => '09123456789',
                'status' => 'active',
            ],
            [
                'username' => 'baker1',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Juan Dela Cruz',
                'position' => 'Head Baker',
                'email' => 'juan.delacruz@wellkenz.com',
                'contact' => '09123456780',
                'status' => 'active',
            ],
            [
                'username' => 'baker2',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Ana Reyes',
                'position' => 'Assistant Baker',
                'email' => 'ana.reyes@wellkenz.com',
                'contact' => '09123456781',
                'status' => 'active',
            ],
            [
                'username' => 'inventory',
                'password' => Hash::make('password'),
                'role' => 'inventory',
                'name' => 'Carlos Garcia',
                'position' => 'Inventory Manager',
                'email' => 'carlos.garcia@wellkenz.com',
                'contact' => '09123456782',
                'status' => 'active',
            ],
            [
                'username' => 'purchasing',
                'password' => Hash::make('password'),
                'role' => 'purchasing',
                'name' => 'Elena Torres',
                'position' => 'Purchasing Officer',
                'email' => 'elena.torres@wellkenz.com',
                'contact' => '09123456783',
                'status' => 'active',
            ],
            [
                'username' => 'supervisor',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'name' => 'Roberto Lim',
                'position' => 'Production Supervisor',
                'email' => 'roberto.lim@wellkenz.com',
                'contact' => '09123456784',
                'status' => 'active',
            ],
            // Additional users for better testing
            [
                'username' => 'baker3',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'name' => 'Liza Mendoza',
                'position' => 'Pastry Chef',
                'email' => 'liza.mendoza@wellkenz.com',
                'contact' => '09123456785',
                'status' => 'active',
            ],
            [
                'username' => 'inventory2',
                'password' => Hash::make('password'),
                'role' => 'inventory',
                'name' => 'Miguel Ramos',
                'position' => 'Inventory Assistant',
                'email' => 'miguel.ramos@wellkenz.com',
                'contact' => '09123456786',
                'status' => 'active',
            ],
            [
                'username' => 'purchasing2',
                'password' => Hash::make('password'),
                'role' => 'purchasing',
                'name' => 'Sofia Chen',
                'position' => 'Purchasing Assistant',
                'email' => 'sofia.chen@wellkenz.com',
                'contact' => '09123456787',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            // Only insert if user doesn't already exist
            $existingUser = DB::table('users')
                ->where('username', $user['username'])
                ->first();

            if (!$existingUser) {
                DB::table('users')->insert(array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                // Update existing user with new columns if needed
                DB::table('users')
                    ->where('username', $user['username'])
                    ->update([
                        'name' => $user['name'],
                        'position' => $user['position'],
                        'email' => $user['email'],
                        'contact' => $user['contact'],
                        'status' => $user['status'],
                        'updated_at' => now(),
                    ]);
            }
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('=== Test Accounts ===');
        $this->command->info('Admin: admin / password (Maria Santos)');
        $this->command->info('Head Baker: baker1 / password (Juan Dela Cruz)');
        $this->command->info('Assistant Baker: baker2 / password (Ana Reyes)');
        $this->command->info('Pastry Chef: baker3 / password (Liza Mendoza)');
        $this->command->info('Inventory Manager: inventory / password (Carlos Garcia)');
        $this->command->info('Inventory Assistant: inventory2 / password (Miguel Ramos)');
        $this->command->info('Purchasing Officer: purchasing / password (Elena Torres)');
        $this->command->info('Purchasing Assistant: purchasing2 / password (Sofia Chen)');
        $this->command->info('Supervisor: supervisor / password (Roberto Lim)');
        $this->command->info('=====================');
    }
}