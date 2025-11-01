<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        // Use stored procedure for departments with employee counts
        $departments = Department::all();
        $departmentsWithCounts = [];
        
        foreach ($departments as $dept) {
            $employeesCount = Employee::where('dept_id', $dept->dept_id)->count();
            $departmentsWithCounts[] = [
                'department' => $dept,
                'employees_count' => $employeesCount
            ];
        }
        
        return view('Admin.departments.index', compact('departmentsWithCounts'));
    }

    public function create()
    {
        return view('Admin.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'dept_name' => 'required|string|max:255|unique:departments'
        ]);

        // Use stored procedure
        $result = Department::createDepartment($request->dept_name);
        $resultData = json_decode($result, true);

        if ($resultData['success']) {
            return redirect()->route('departments.index')
                ->with('success', $resultData['message']);
        } else {
            return redirect()->route('departments.index')
                ->with('error', $resultData['message']);
        }
    }

    public function show($id)
    {
        // Use stored procedure
        $result = Department::getDepartment($id);
        $resultData = json_decode($result, true);

        if (!$resultData['success']) {
            return redirect()->route('departments.index')
                ->with('error', $resultData['message']);
        }

        $department = (object) $resultData['data'];
        $employees = Employee::where('dept_id', $id)->with('department')->get();

        return view('Admin.departments.show', compact('department', 'employees'));
    }

    public function edit($id)
    {
        // Use stored procedure
        $result = Department::getDepartment($id);
        $resultData = json_decode($result, true);

        if (!$resultData['success']) {
            return redirect()->route('departments.index')
                ->with('error', $resultData['message']);
        }

        $department = (object) $resultData['data'];
        return view('Admin.departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'dept_name' => 'required|string|max:255|unique:departments,dept_name,' . $id . ',dept_id'
        ]);

        // Use stored procedure
        $result = Department::updateDepartment($id, $request->dept_name);
        $resultData = json_decode($result, true);

        if ($resultData['success']) {
            return redirect()->route('departments.index')
                ->with('success', $resultData['message']);
        } else {
            return redirect()->route('departments.index')
                ->with('error', $resultData['message']);
        }
    }

    public function destroy($id)
    {
        // Use stored procedure
        $result = Department::deleteDepartment($id);
        $resultData = json_decode($result, true);

        if ($resultData['success']) {
            return redirect()->route('departments.index')
                ->with('success', $resultData['message']);
        } else {
            return redirect()->route('departments.index')
                ->with('error', $resultData['message']);
        }
    }
}