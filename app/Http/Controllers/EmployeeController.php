<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::all();
            $positions = $this->getPositions();

            return view('Admin.Management.employee_management', [
                'employees' => $employees,
                'positions' => $positions
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController error: ' . $e->getMessage());
            return $this->getEmptyData();
        }
    }

    private function getPositions()
    {
        return [
            'Baker',
            'Purchasing Officer',
            'Inventory Clerk',
            'Supervisor / Owner',
            'Admin / IT staff',
        ];
    }

    private function getEmptyData()
    {
        $positions = $this->getPositions();

        return view('Admin.Management.employee_management', [
            'employees' => collect(),
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
        ]);

        try {
            $employee = Employee::create([
                'emp_name' => $request->emp_name,
                'emp_position' => $request->emp_position,
                'emp_email' => $request->emp_email,
                'emp_contact' => $request->emp_contact,
                'emp_status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            return response()->json([
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving employee: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            $positions = $this->getPositions();

            return response()->json([
                'success' => true,
                'employee' => $employee,
                'positions' => $positions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'emp_name' => 'required|string|max:255',
            'emp_position' => 'required|string',
            'emp_email' => 'required|email|unique:employees,emp_email,' . $id . ',emp_id',
            'emp_contact' => 'required|string',
            'emp_status' => 'required|in:active,inactive'
        ]);

        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $employee->update([
                'emp_name' => $request->emp_name,
                'emp_position' => $request->emp_position,
                'emp_email' => $request->emp_email,
                'emp_contact' => $request->emp_contact,
                'emp_status' => $request->emp_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee: ' . $e->getMessage()
            ], 500);
        }
    }
}