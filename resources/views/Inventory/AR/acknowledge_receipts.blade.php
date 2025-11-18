@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Acknowledgement Receipts</h1>
            <p class="text-gray-600 mt-1">Track and manage issued acknowledgment receipts</p>
        </div>
        <div class="flex space-x-2">
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Issued Receipts</h3>
                <div class="flex space-x-2">
                    <input type="date" id="filter-date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select id="filter-status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="issued">Issued</option>
                        <option value="confirmed">Confirmed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AR Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="receipts-body">
                    @forelse($receipts as $receipt)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $receipt->ar_ref }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $receipt->receiver->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receipt->issued_date->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $receipt->ar_status === 'issued' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($receipt->ar_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ auth()->user()->name ?? 'System' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 space-x-2">
                            <!-- Updated to use new viewReceipt function -->
                            <button onclick="viewReceipt({{ $receipt->ar_id }})" class="hover:text-blue-800 font-medium">View</button>
                            <!-- Original printReceipt function is still used, but can be triggered from modal -->
                            <button onclick="printReceipt({{ $receipt->ar_id }})" class="hover:text-blue-800 font-medium">Print</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No receipts found</td>
                    </tr>
                    @endForelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>
</div>

{{-- REMOVED THE MODAL HTML BLOCK --}}

@push('scripts')
<script>
    {{-- REMOVED MODAL GLOBAL VARIABLES --}}
    {{-- REMOVED DOMCONTENTLOADED LISTENER --}}
    {{-- REMOVED showModal FUNCTION --}}

    /**
     * REVERTED: Opens view details in new window.
     * @param {number} id - The AR ID
     */
    function viewReceipt(id) {
        // Use Laravel route helper for correct URL - navigate in same tab
        const url = `{{ route('inventory.acknowledge-receipts.view', ':id') }}`.replace(':id', id);
        window.location.href = url;
    }

    /**
     * Opens print view in new window. (Original function)
     * @param {number} id - The AR ID
     */
    function printReceipt(id) {
        // Use Laravel route helper for correct URL - navigate in same tab
        const url = `{{ route('inventory.acknowledge-receipts.print', ':id') }}`.replace(':id', id);
        window.location.href = url;
    }

</script>
@endpush
@endsection