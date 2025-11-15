@extends('Inventory.layout.app')

@section('title','Acknowledge Receipts - WellKenz ERP')
@section('breadcrumb','Acknowledge Receipts')

@section('content')
<div class="space-y-6">

    <!-- header -->
    <div class="bg-white border rounded p-6">
        <h1 class="text-2xl font-semibold text-gray-900">Acknowledge Receipts (AR)</h1>
        <p class="text-sm text-gray-500 mt-1">List of all issued acknowledge receipts</p>
    </div>

    <!-- table -->
    <div class="bg-white border rounded">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">AR Ref</th>
                        <th class="px-6 py-3 text-left">Req Ref</th>
                        <th class="px-6 py-3 text-left">Issued To</th>
                        <th class="px-6 py-3 text-left">Issued By</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($ars as $ar)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold">AR-{{ $ar->ar_ref }}</td>
                            <td class="px-6 py-4">RQ-{{ $ar->requisition->req_ref ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $ar->issuedTo->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $ar->issuedBy->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $ar->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                    {{ ucfirst($ar->ar_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-500">No acknowledge receipts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t bg-gray-50">
            {{ $ars->links() }}
        </div>
    </div>

</div>
@endsection