@extends('Admin.layout.app')

@section('title', 'Edit Employee - WellKenz ERP')

@section('breadcrumb', 'Edit Employee')

@section('content')
<div class="space-y-6">
    <div class="bg-white border-2 border-border-soft rounded-lg p-6 max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-display text-2xl font-bold text-text-dark">Edit Employee: {{ $employee->emp_name }}</h2>
            <a href="{{ route('Admin_employees') }}" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>
        
        <form action="{{ route('employees.update', $employee->emp_id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Full Name</label>
                    <input type="text" name="emp_name" value="{{ $employee->emp_name }}" required 
                           class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate rounded">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                        <select name="emp_position" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate rounded">
                            @foreach($positions as $position)
                                <option value="{{ $position }}" {{ $employee->emp_position == $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                        <select name="dept_id" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate rounded">
                            @foreach($departments as $department)
                                <option value="{{ $department->dept_id }}" {{ $employee->dept_id == $department->dept_id ? 'selected' : '' }}>
                                    {{ $department->dept_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Email Address</label>
                        <input type="email" name="emp_email" value="{{ $employee->emp_email }}" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Contact Number</label>
                        <input type="text" name="emp_contact" value="{{ $employee->emp_contact }}" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate rounded">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('Admin_employees') }}" 
                       class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition rounded">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition rounded">
                        Update Employee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection