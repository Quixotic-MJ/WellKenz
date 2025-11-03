<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            Log::info('EmployeeController: Starting index method');
            
            // Check if tables exist
            $employeesTableExists = Schema::hasTable('employees');
            $departmentsTableExists = Schema::hasTable('departments');
            
            Log::info("Employees table exists: " . ($employeesTableExists ? 'Yes' : 'No'));
            Log::info("Departments table exists: " . ($departmentsTableExists ? 'Yes' : 'No'));

            if (!$employeesTableExists || !$departmentsTableExists) {
                Log::warning('Required tables do not exist, returning empty data');
                return $this->getEmptyData();
            }

            // Use Eloquent for listing (we didn't create a stored procedure for listing all)
            $employees = Employee::with('department', 'user')->get();
            $departments = Department::all();
            
            Log::info("Found " . $departments->count() . " departments in database");

            // If no departments exist, create some default ones using stored procedure
            if ($departments->isEmpty()) {
                Log::info('No departments found, creating default departments');
                $this->createDefaultDepartments();
                $departments = Department::all();
                Log::info("Now have " . $departments->count() . " departments after creation");
            }

            $stats = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::count(),
                'bakers' => Employee::where('emp_position', 'like', '%baker%')->orWhere('emp_position', 'like', '%Baker%')->count(),
                'new_this_month' => Employee::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            $positions = $this->getPositions();
            
            Log::info("Sending to view - Positions: " . count($positions) . ", Departments: " . $departments->count());

            return view('Admin.employees', [
                'employees' => $employees,
                'departments' => $departments,
                'stats' => $stats,
                'positions' => $positions
            ]);

        } catch (\Exception $e) {
            Log::error('EmployeeController error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->getEmptyData();
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
                Department::createDepartment($deptName);
            }
            
            Log::info('Default departments created successfully using stored procedures');
        } catch (\Exception $e) {
            Log::error('Failed to create default departments: ' . $e->getMessage());
        }
    }

    private function getEmptyData()
    {
        Log::info('Using empty data fallback');
        
        $positions = $this->getPositions();
        
        $defaultDepartments = collect([
            (object)['dept_id' => 1, 'dept_name' => 'Bakery'],
            (object)['dept_id' => 2, 'dept_name' => 'Pastry'],
            (object)['dept_id' => 3, 'dept_name' => 'Administration'],
            (object)['dept_id' => 4, 'dept_name' => 'Purchasing'],
            (object)['dept_id' => 5, 'dept_name' => 'Inventory'],
            (object)['dept_id' => 6, 'dept_name' => 'Sales'],
        ]);

        return view('Admin.employees', [
            'employees' => collect(),
            'departments' => $defaultDepartments,
            'stats' => [
                'total_employees' => 0,
                'active_employees' => 0,
                'bakers' => 0,
                'new_this_month' => 0,
            ],
            'positions' => $positions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'emp_name' => 'required|string|max:255',
            'emp_position' => 'required|string',
            'emp_email' => 'required|email|unique:employees',
            'emp_contact' => 'required|string',
            'dept_id' => 'required|exists:departments,dept_id'
        ]);

        try {
            // Use stored procedure
            $result = Employee::createEmployee($request->all());
            $resultData = json_decode($result, true);

            if ($resultData['success']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $resultData['message']
                    ]);
                }
                return redirect()->route('Admin_employee')->with('success', $resultData['message']);
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultData['message']
                    ], 400);
                }
                return redirect()->route('Admin_employee')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating employee: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('Admin_employee')->with('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Use stored procedure
            $result = Employee::getEmployee($id);
            $resultData = json_decode($result, true);

            if (!$resultData['success']) {
                if (request()->ajax()) {
                    return response()->json(['error' => $resultData['message']], 404);
                }
                return redirect()->route('Admin_employee')->with('error', $resultData['message']);
            }

            $employee = (object) $resultData['data'];
            $departments = Department::all();

            if (request()->ajax()) {
                return response()->json([
                    'employee' => $employee,
                    'departments' => $departments
                ]);
            }

            return view('Admin.employees-show', compact('employee', 'departments'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Error retrieving employee: ' . $e->getMessage()], 500);
            }
            return redirect()->route('Admin_employee')->with('error', 'Error retrieving employee: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            // Use stored procedure
            $result = Employee::getEmployee($id);
            $resultData = json_decode($result, true);

            if (!$resultData['success']) {
                if (request()->ajax()) {
                    return response()->json(['error' => $resultData['message']], 404);
                }
                return redirect()->route('Admin_employee')->with('error', $resultData['message']);
            }

            $employee = (object) $resultData['data'];
            $departments = Department::all();
            $positions = $this->getPositions();

            // If it's an AJAX request (from modal), return JSON
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'employee' => $employee,
                    'departments' => $departments,
                    'positions' => $positions
                ]);
            }

            return view('Admin.employees-edit', compact('employee', 'departments', 'positions'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error retrieving employee: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('Admin_employee')->with('error', 'Error retrieving employee: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'emp_name' => 'required|string|max:255',
            'emp_position' => 'required|string',
            'emp_email' => 'required|email|unique:employees,emp_email,' . $id . ',emp_id',
            'emp_contact' => 'required|string',
            'dept_id' => 'required|exists:departments,dept_id'
        ]);

        try {
            // Use stored procedure
            $result = Employee::updateEmployee($id, $request->all());
            $resultData = json_decode($result, true);

            if ($resultData['success']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $resultData['message']
                    ]);
                }
                return redirect()->route('Admin_employee')->with('success', $resultData['message']);
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultData['message']
                    ], 400);
                }
                return redirect()->route('Admin_employee')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating employee: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating employee: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('Admin_employee')->with('error', 'Error updating employee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Use stored procedure
            $result = Employee::deleteEmployee($id);
            $resultData = json_decode($result, true);

            if ($resultData['success']) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $resultData['message']
                    ]);
                }
                return redirect()->route('Admin_employee')->with('success', $resultData['message']);
            } else {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultData['message']
                    ], 400);
                }
                return redirect()->route('Admin_employee')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting employee: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('Admin_employee')->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }
}