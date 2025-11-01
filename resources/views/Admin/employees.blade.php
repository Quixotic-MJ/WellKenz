@extends('Admin.layout.app')

@section('title', 'Employee Management - WellKenz ERP')

@section('breadcrumb', 'Employee Management')

@section('content')
<div class="space-y-6">
    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Employee Management</h1>
            <p class="text-text-muted mt-2">Manage employee information and departments</p>
        </div>
        <button onclick="openAddEmployeeModal()" class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
            <i class="fas fa-user-plus mr-2"></i>
            Add Employee
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Employees</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['total_employees'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-green-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['active_employees'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Bakers</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['bakers'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">New This Month</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['new_this_month'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="bg-white border-2 border-border-soft">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-xl font-bold text-text-dark">All Employees</h3>
                <div class="relative">
                    <input type="text" placeholder="Search..." 
                        class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    @php
                        $employees = $employees ?? collect();
                    @endphp
                    
                    @forelse($employees as $employee)
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">{{ $employee->emp_name ?? 'N/A' }}</p>
                            <p class="text-xs text-text-muted">{{ $employee->emp_email ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $positionColors = [
                                    'Baker' => 'bg-orange-100 text-orange-700',
                                    'Purchasing Officer' => 'bg-green-100 text-green-700',
                                    'Inventory Clerk' => 'bg-blue-100 text-blue-700',
                                    'Supervisor / Owner' => 'bg-purple-100 text-purple-700',
                                ];
                                $color = $positionColors[$employee->emp_position ?? ''] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-block px-2 py-1 {{ $color }} text-xs font-bold">
                                {{ $employee->emp_position ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-text-dark">
                                {{ $employee->department->dept_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">{{ $employee->emp_contact ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($employee->user ?? false)
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">
                                    NO ACCOUNT
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <button onclick="openEditEmployeeModal({{ $employee->emp_id ?? 0 }})" 
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                    Edit
                                </button>
                                @if(!($employee->user ?? false))
                                <form action="{{ route('employees.destroy', $employee->emp_id ?? 0) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Delete this employee?')"
                                            class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-text-muted">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p>No employees found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <p class="text-sm text-text-muted">Showing {{ $employees->count() }} employees</p>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="bg-white border-2 border-border-soft p-6">
        <h3 class="font-display text-xl font-bold text-text-dark mb-6">Department Distribution</h3>
        <div class="space-y-4">
            @php
                $deptStats = [];
                $totalEmployees = $employees->count();
                foreach($departments ?? [] as $dept) {
                    $count = $employees->where('dept_id', $dept->dept_id)->count();
                    $deptStats[] = [
                        'name' => $dept->dept_name,
                        'count' => $count,
                        'percentage' => $totalEmployees > 0 ? round(($count / $totalEmployees) * 100) : 0
                    ];
                }
            @endphp
            
            @foreach($deptStats as $stat)
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-semibold text-text-dark">{{ $stat['name'] }}</span>
                    <span class="text-text-muted">{{ $stat['count'] }} ({{ $stat['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-gray-200 h-2">
                    <div class="bg-caramel h-2" style="width: {{ $stat['percentage'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div id="addEmployeeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Add New Employee</h3>
                <button onclick="closeAddEmployeeModal()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Full Name</label>
                    <input type="text" name="emp_name" required 
                           class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                           placeholder="Enter full name">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                        <select name="emp_position" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="">Select Position</option>
                            @foreach($positions ?? [] as $position)
                                <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                        <select name="dept_id" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $department)
                                <option value="{{ $department->dept_id }}">{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                        <input type="email" name="emp_email" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                               placeholder="email@wellkenz.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Contact</label>
                        <input type="text" name="emp_contact" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddEmployeeModal()" 
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                        Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    
    .font-display { font-family: 'Playfair Display', serif; }
    .cream-bg { background-color: #faf7f3; }
    .text-dark { color: #1a1410; }
    .text-muted { color: #8b7355; }
    .chocolate { background-color: #3d2817; }
    .caramel { background-color: #c48d3f; }
    .caramel-dark { background-color: #a67332; }
    .border-soft { border-color: #e8dfd4; }
</style>

<script>
    function openAddEmployeeModal() {
        document.getElementById('addEmployeeModal').classList.remove('hidden');
    }

    function closeAddEmployeeModal() {
        document.getElementById('addEmployeeModal').classList.add('hidden');
    }

    function openEditEmployeeModal(employeeId) {
        if (employeeId && employeeId > 0) {
            window.location.href = "{{ url('admin/employees') }}/" + employeeId + "/edit";
        }
    }

    document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddEmployeeModal();
    });
</script>
@endsection