<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function employeeManagement()
    {
        try {
            Log::info('AdminController: Starting employeeManagement method');
            
            // Check if tables exist
            $employeesTableExists = Schema::hasTable('employees');
            $departmentsTableExists = Schema::hasTable('departments');
            
            if (!$employeesTableExists || !$departmentsTableExists) {
                Log::warning('Required tables do not exist, returning empty data');
                return $this->getEmptyEmployeeData();
            }

            // Use Eloquent for listing
            $employees = Employee::with('department', 'user')->get();
            $departments = Department::all();
            
            Log::info("Found " . $departments->count() . " departments in database");

            // If no departments exist, create some default ones
            if ($departments->isEmpty()) {
                Log::info('No departments found, creating default departments');
                $this->createDefaultDepartments();
                $departments = Department::all();
            }

            $positions = $this->getPositions();
            
            Log::info("Sending to view - Employees: " . $employees->count() . ", Departments: " . $departments->count());

            return view('Admin.Management.employee_management', [
                'employees' => $employees,
                'departments' => $departments,
                'positions' => $positions
            ]);

        } catch (\Exception $e) {
            Log::error('AdminController employeeManagement error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->getEmptyEmployeeData();
        }
    }

    private function getPositions()
    {
        return [
            'Baker',
            'Pastry Chef',
            'Head Baker',
            'Purchasing Officer',
            'Inventory Clerk',
            'Supervisor / Owner',
            'Admin / IT staff',
            'Sales Staff',
            'Delivery Driver'
        ];
    }

    private function createDefaultDepartments()
    {
        try {
            $defaultDepartments = [
                'Bakery',
                'Pastry',
                'Administration',
                'Purchasing',
                'Inventory',
                'Sales',
            ];

            foreach ($defaultDepartments as $deptName) {
                // Use Eloquent to create departments instead of stored procedure
                Department::create(['dept_name' => $deptName]);
            }
            
            Log::info('Default departments created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create default departments: ' . $e->getMessage());
        }
    }

    private function getEmptyEmployeeData()
    {
        Log::info('Using empty employee data fallback');
        
        $positions = $this->getPositions();
        
        $defaultDepartments = collect([
            (object)['dept_id' => 1, 'dept_name' => 'Bakery'],
            (object)['dept_id' => 2, 'dept_name' => 'Pastry'],
            (object)['dept_id' => 3, 'dept_name' => 'Administration'],
            (object)['dept_id' => 4, 'dept_name' => 'Purchasing'],
            (object)['dept_id' => 5, 'dept_name' => 'Inventory'],
            (object)['dept_id' => 6, 'dept_name' => 'Sales'],
        ]);

        return view('Admin.Management.employee_management', [
            'employees' => collect(),
            'departments' => $defaultDepartments,
            'positions' => $positions
        ]);
    }
}