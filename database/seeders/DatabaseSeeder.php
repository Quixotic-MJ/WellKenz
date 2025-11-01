<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('users')->delete();
        DB::table('employees')->delete();
        DB::table('departments')->delete();

        // Insert Departments
        $departments = [
            ['dept_name' => 'Administration'],
            ['dept_name' => 'Bakery'],
            ['dept_name' => 'Purchasing'],
            ['dept_name' => 'Inventory'],
            ['dept_name' => 'Supervision'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'dept_name' => $department['dept_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert Employees
        $employees = [
            [
                'emp_name' => 'John Smith',
                'emp_position' => 'Admin / IT staff',
                'emp_email' => 'john.smith@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4567',
                'dept_id' => 1,
            ],
            [
                'emp_name' => 'Maria Garcia',
                'emp_position' => 'Baker',
                'emp_email' => 'maria.garcia@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4568',
                'dept_id' => 2,
            ],
            [
                'emp_name' => 'Robert Johnson',
                'emp_position' => 'Baker',
                'emp_email' => 'robert.johnson@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4569',
                'dept_id' => 2,
            ],
            [
                'emp_name' => 'Sarah Chen',
                'emp_position' => 'Inventory Clerk',
                'emp_email' => 'sarah.chen@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4570',
                'dept_id' => 4,
            ],
            [
                'emp_name' => 'Michael Brown',
                'emp_position' => 'Purchasing Officer',
                'emp_email' => 'michael.brown@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4571',
                'dept_id' => 3,
            ],
            [
                'emp_name' => 'Emily Wilson',
                'emp_position' => 'Supervisor / Owner',
                'emp_email' => 'emily.wilson@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4572',
                'dept_id' => 5,
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert([
                'emp_name' => $employee['emp_name'],
                'emp_position' => $employee['emp_position'],
                'emp_email' => $employee['emp_email'],
                'emp_contact' => $employee['emp_contact'],
                'dept_id' => $employee['dept_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert Users
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

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Test Accounts:');
        $this->command->info('Admin: admin / password');
        $this->command->info('Baker 1: baker1 / password');
        $this->command->info('Baker 2: baker2 / password');
        $this->command->info('Inventory: inventory / password');
        $this->command->info('Purchasing: purchasing / password');
        $this->command->info('Supervisor: supervisor / password');
    }
}