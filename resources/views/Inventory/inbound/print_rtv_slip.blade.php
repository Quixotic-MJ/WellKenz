@extends('Purchasing.layout.print')

@section('title', 'RTV Slip #' . $rtv->rtv_number . ' - WellKenz Bakery')
@section('document_title', 'RTV Slip')

@section('content')

<div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

    {{-- ACTION BAR (Visible on Screen Only) --}}
    <div class="w-full max-w-[210mm] mb-6 flex justify-between items-center print:hidden">
        <a href="{{ route('inventory.rtv.index') }}" class="flex items-center text-sm font-bold text-chocolate hover:text-caramel transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to RTV Log
        </a>
        <div class="flex gap-3">
            <button onclick="window.print()" class="flex items-center px-5 py-2.5 bg-chocolate text-white font-bold rounded-lg shadow-md hover:bg-chocolate-dark transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-print mr-2"></i> Print Slip
            </button>
            <button class="flex items-center px-5 py-2.5 bg-white border border-border-soft text-chocolate font-bold rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <i class="fas fa-download mr-2"></i> PDF
            </button>
        </div>
    </div>

    {{-- A4 PAPER CONTAINER --}}
    <div class="bg-white w-full max-w-[210mm] min-h-[297mm] p-[15mm] shadow-2xl rounded-sm print:shadow-none print:w-full print:max-w-none print:p-0 print:m-0 relative">
        
        {{-- WATERMARK (Background Decor) --}}
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-[0.03] pointer-events-none screen-only">
            <i class="fas fa-birthday-cake text-[400px] text-chocolate"></i>
        </div>

        <div class="relative z-10">
            {{-- 1. DOCUMENT HEADER --}}
            <header class="document-header">
                <div class="flex justify-between items-start">
                    <div class="brand-block">
                        <div class="brand-logo">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div class="brand-text">
                            <h1>WellKenz Bakery</h1>
                            <p>Return to Vendor</p>
                        </div>
                    </div>
                    <div class="metadata-box">
                        <h2>RTV Slip</h2>
                        <div class="doc-id">#{{ $rtv->rtv_number }}</div>
                        <div class="metadata-item">
                            <span class="metadata-label">Date Issued:</span>
                            <span class="metadata-value">{{ $rtv->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Prepared By:</span>
                            <span class="metadata-value">{{ $rtv->createdBy?->name ?? 'System Admin' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Status:</span>
                            <span class="metadata-value font-bold">{{ $rtv->status }}</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- 2. INFO GRID --}}
            <div class="grid grid-cols-2 gap-8 mb-8">
                
                {{-- Supplier Details --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Supplier Details</h3>
                    <div class="space-y-1.5 text-sm">
                        <p class="font-bold text-lg text-gray-900 print:text-black">{{ $rtv->supplier->name }}</p>
                        <div class="grid grid-cols-1 gap-1">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black">Code:</span>
                                <span class="text-gray-700 print:text-black">{{ $rtv->supplier->supplier_code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black">Contact:</span>
                                <span class="text-gray-700 print:text-black">{{ $rtv->supplier->contact_person ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black">Phone:</span>
                                <span class="text-gray-700 print:text-black">{{ $rtv->supplier->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                        @if($rtv->supplier->address)
                            <div class="mt-2 pt-2 border-t border-gray-100 print:border-gray-300">
                                <span class="font-medium text-gray-500 print:text-black text-xs block mb-1">Address:</span>
                                <span class="text-gray-700 print:text-black text-sm leading-tight">{{ $rtv->supplier->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Transaction Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium print:text-black">Return Date:</span>
                            <span class="font-bold text-gray-900 print:text-black">{{ $rtv->return_date->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium print:text-black">PO Reference:</span>
                            <span class="font-mono font-bold text-chocolate print:text-black">{{ $rtv->purchaseOrder?->po_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium print:text-black">Status:</span>
                            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase border bg-gray-50 text-gray-800 border-gray-200 print:border-black">
                                {{ $rtv->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. ITEMS TABLE --}}
            <div class="mb-8">
                <table class="document-table">
                    <thead>
                        <tr>
                            <th class="w-12">No.</th>
                            <th>Item Description</th>
                            <th class="w-24">SKU</th>
                            <th class="w-24 text-center">Qty</th>
                            <th class="w-32 text-right">Unit Cost</th>
                            <th class="w-32 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rtv->rtvItems as $index => $item)
                        <tr class="no-print-break">
                            <td class="text-gray-500 print:text-black text-center">{{ $index + 1 }}</td>
                            <td>
                                <p class="font-bold text-gray-900 print:text-black">{{ $item->item->name }}</p>
                                @if($item->reason)
                                    <p class="text-xs text-red-600 mt-1 italic print:text-black">Reason: {{ $item->reason }}</p>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-gray-600 print:text-black">{{ $item->item->item_code }}</td>
                            <td class="text-center">
                                <span class="font-bold">{{ number_format($item->quantity_returned, 2) }}</span>
                                <span class="text-xs text-gray-500 block print:text-black">{{ $item->item->unit->symbol ?? 'pcs' }}</span>
                            </td>
                            <td class="text-right text-gray-600 print:text-black">₱{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-right font-bold text-gray-900 print:text-black">₱{{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 print:bg-transparent">
                            <td colspan="5" class="py-4 pr-4 text-right text-sm font-bold text-gray-500 uppercase tracking-wide border-b-2 border-gray-800 print:text-black print:border-black">Subtotal</td>
                            <td class="py-4 text-right text-base font-bold text-gray-900 border-b-2 border-gray-800 print:text-black print:border-black">₱{{ number_format($rtv->total_value, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 4. NOTES & SUMMARY --}}
            @if($rtv->notes)
            <div class="mb-8 border border-gray-200 rounded-lg p-4 bg-cream-bg/30 print:border-black print:bg-transparent">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2 print:text-black">Notes / Remarks</p>
                <p class="text-sm text-gray-700 italic print:text-black">{{ $rtv->notes }}</p>
            </div>
            @endif

            {{-- 5. SIGNATURES --}}
            <div class="grid grid-cols-3 gap-8 mt-auto pt-12">
                <div class="text-center">
                    <div class="border-b border-gray-400 mb-2 w-3/4 mx-auto print:border-black"></div>
                    <p class="text-xs font-bold text-chocolate uppercase tracking-widest print:text-black">Prepared By</p>
                    <p class="text-[10px] text-gray-500 mt-1 print:text-black">WellKenz Representative</p>
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-400 mb-2 w-3/4 mx-auto print:border-black"></div>
                    <p class="text-xs font-bold text-chocolate uppercase tracking-widest print:text-black">Approved By</p>
                    <p class="text-[10px] text-gray-500 mt-1 print:text-black">Supervisor / Manager</p>
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-400 mb-2 w-3/4 mx-auto print:border-black"></div>
                    <p class="text-xs font-bold text-chocolate uppercase tracking-widest print:text-black">Received By</p>
                    <p class="text-[10px] text-gray-500 mt-1 print:text-black">Supplier Representative</p>
                </div>
            </div>

        </div>
        
        {{-- Footer Strip --}}
        <div class="absolute bottom-0 left-0 right-0 h-4 bg-chocolate screen-only"></div>
    </div>
    
    <p class="text-center text-xs text-gray-400 mt-4 screen-only">
        &copy; {{ date('Y') }} WellKenz Bakery. All rights reserved.
    </p>

</div>

@endsection