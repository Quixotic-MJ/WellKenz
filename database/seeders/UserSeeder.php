<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'emp_id' => 1,
            ],
            [
                'username' => 'baker1',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'emp_id' => 2,
            ],
            [
                'username' => 'baker2',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'emp_id' => 3,
            ],
            [
                'username' => 'inventory',
                'password' => Hash::make('password'),
                'role' => 'inventory',
                'emp_id' => 4,
            ],
            [
                'username' => 'purchasing',
                'password' => Hash::make('password'),
                'role' => 'purchasing',
                'emp_id' => 5,
            ],
            [
                'username' => 'supervisor',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'emp_id' => 6,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'username' => $user['username'],
                'password' => $user['password'],
                'role' => $user['role'],
                'emp_id' => $user['emp_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Test Accounts:');
        $this->command->info('Admin: admin / password');
        $this->command->info('Baker 1: baker1 / password');
        $this->command->info('Baker 2: baker2 / password');
        $this->command->info('Inventory: inventory / password');
        $this->command->info('Purchasing: purchasing / password');
        $this->command->info('Supervisor: supervisor / password');
    }
}