<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        DB::table('employees')->delete();

        $employees = [
            [
                'emp_name' => 'John Smith',
                'emp_position' => 'Admin / IT staff',
                'emp_email' => 'john.smith@wellkenz.com',
                'emp_contact' => '+1 (555) 123-4567',
                'emp_status' => 'active',
            ],
            [
                'emp_name' => 'Jane Doe',
                'emp_position' => 'Baker',
                'emp_email' => 'jane.doe@wellkenz.com',
                'emp_contact' => '+1 (555) 234-5678',
                'emp_status' => 'active',
            ],
            [
                'emp_name' => 'Mike Brown',
                'emp_position' => 'Baker',
                'emp_email' => 'mike.brown@wellkenz.com',
                'emp_contact' => '+1 (555) 345-6789',
                'emp_status' => 'active',
            ],
            [
                'emp_name' => 'Lisa Green',
                'emp_position' => 'Inventory Clerk',
                'emp_email' => 'lisa.green@wellkenz.com',
                'emp_contact' => '+1 (555) 456-7890',
                'emp_status' => 'active',
            ],
            [
                'emp_name' => 'David White',
                'emp_position' => 'Purchasing Officer',
                'emp_email' => 'david.white@wellkenz.com',
                'emp_contact' => '+1 (555) 567-8901',
                'emp_status' => 'active',
            ],
            [
                'emp_name' => 'Sarah Black',
                'emp_position' => 'Supervisor / Owner',
                'emp_email' => 'sarah.black@wellkenz.com',
                'emp_contact' => '+1 (555) 678-9012',
                'emp_status' => 'active',
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert([
                'emp_name' => $employee['emp_name'],
                'emp_position' => $employee['emp_position'],
                'emp_email' => $employee['emp_email'],
                'emp_contact' => $employee['emp_contact'],
                'emp_status' => $employee['emp_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Employees seeded successfully!');
    }
}