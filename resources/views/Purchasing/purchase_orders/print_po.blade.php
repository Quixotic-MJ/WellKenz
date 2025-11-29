@extends('Purchasing.layout.print')

@section('title', 'Purchase Order #' . $purchaseOrder->po_number . ' - WellKenz Bakery')

@section('content')

<div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

    {{-- A4 PAPER CONTAINER --}}
    <div class="bg-white w-full max-w-[210mm] min-h-[297mm] p-[15mm] shadow-2xl rounded-sm print:shadow-none print:w-full print:max-w-none print:p-0 print:m-0 relative print:rounded-none">
        
        {{-- WATERMARK (Background Decor) --}}
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-[0.03] pointer-events-none screen-only">
            <i class="fas fa-birthday-cake text-[400px] text-chocolate"></i>
        </div>

        <div class="relative z-10">
            {{-- 1. DOCUMENT HEADER --}}
            <header class="border-b-2 border-chocolate pb-6 mb-8 no-print-break">
                <div class="flex justify-between items-start print:items-start">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-chocolate text-white rounded-full flex items-center justify-center text-2xl print:border-2 print:border-black print:text-black print:bg-transparent">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div>
                            <h1 class="font-display text-3xl font-bold text-chocolate leading-none tracking-wide print:text-black print:text-2xl">WellKenz Bakery</h1>
                            <p class="text-xs text-gray-500 uppercase tracking-[0.2em] mt-1 print:text-black">Official Purchase Order Document</p>
                            <p class="text-xs text-gray-400 print:text-black print:text-xs mt-1">Generated on: {{ now()->format('F d, Y g:i A') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="font-display text-2xl font-bold text-gray-900 uppercase tracking-wide print:text-black print:text-xl">PO #{{ $purchaseOrder->po_number }}</h2>
                        <div class="inline-block px-3 py-1 rounded border border-gray-300 bg-gray-50 text-xs font-bold uppercase tracking-widest mt-2 print:border-black print:bg-transparent">
                            {{ $purchaseOrder->status }}
                        </div>
                        <div class="mt-2 text-xs text-gray-500 print:text-black">
                            <strong>PO ID:</strong> {{ $purchaseOrder->id }}
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between text-sm text-gray-600 print:text-black">
                    <div class="flex-1">
                        <p class="mb-1">
                            <span class="font-bold text-gray-900 print:text-black">Date Issued:</span> 
                            {{ $purchaseOrder->created_at->format('F d, Y') }}
                        </p>
                        <p>
                            <span class="font-bold text-gray-900 print:text-black">Created By:</span> 
                            {{ $purchaseOrder->createdBy->name ?? 'System' }}
                        </p>
                    </div>
                    <div class="flex-1 text-right">
                        <p class="mb-1">
                            <span class="font-bold text-gray-900 print:text-black">Expected Delivery:</span> 
                            {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'TBA' }}
                        </p>
                        <p>
                            <span class="font-bold text-gray-900 print:text-black">Payment Terms:</span> 
                            {{ $purchaseOrder->payment_terms ?? 30 }} Days
                        </p>
                    </div>
                </div>
            </header>

            {{-- 2. INFO GRID --}}
            <div class="grid grid-cols-2 gap-8 mb-8 no-print-break">
                
                {{-- Supplier Details --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black print:rounded-none print:p-3">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Vendor Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="border-b border-gray-100 print:border-gray-300 pb-2 print:pb-1">
                            <p class="font-bold text-lg text-gray-900 print:text-black leading-tight">{{ $purchaseOrder->supplier->name }}</p>
                            @if($purchaseOrder->supplier->supplier_code)
                                <p class="text-xs text-gray-500 print:text-black font-mono">Code: {{ $purchaseOrder->supplier->supplier_code }}</p>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 gap-1 print:gap-0">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Contact:</span>
                                <span class="text-gray-700 print:text-black">{{ $purchaseOrder->supplier->contact_person ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Phone:</span>
                                <span class="text-gray-700 print:text-black">{{ $purchaseOrder->supplier->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Email:</span>
                                <span class="text-gray-700 print:text-black">{{ $purchaseOrder->supplier->email ?? 'N/A' }}</span>
                            </div>
                            @if($purchaseOrder->supplier->tax_id)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-500 print:text-black w-20">Tax ID:</span>
                                    <span class="text-gray-700 print:text-black font-mono">{{ $purchaseOrder->supplier->tax_id }}</span>
                                </div>
                            @endif
                        </div>
                        @if($purchaseOrder->supplier->address)
                            <div class="mt-2 pt-2 border-t border-gray-100 print:border-gray-300">
                                <span class="font-medium text-gray-500 print:text-black text-xs block mb-1">Address:</span>
                                <span class="text-gray-700 print:text-black text-sm leading-tight">{{ $purchaseOrder->supplier->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Shipping/Billing --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black print:rounded-none print:p-3">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Delivery Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="border-b border-gray-100 print:border-gray-300 pb-2">
                            <p class="font-bold text-lg text-gray-900 print:text-black">WellKenz Bakery</p>
                            <p class="text-xs text-gray-500 print:text-black">Main Headquarters</p>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Address:</span>
                                <span class="text-gray-700 print:text-black">123 Baker Street, Cebu City</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">City:</span>
                                <span class="text-gray-700 print:text-black">Cebu City, Philippines 6000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Terms:</span>
                                <span class="text-gray-700 print:text-black">{{ $purchaseOrder->payment_terms ?? 30 }} Days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500 print:text-black w-20">Currency:</span>
                                <span class="text-gray-700 print:text-black">Philippine Peso (PHP)</span>
                            </div>
                        </div>
                        @if($purchaseOrder->delivery_instructions)
                            <div class="mt-3 pt-2 border-t border-gray-100 print:border-gray-300">
                                <span class="font-medium text-gray-500 print:text-black text-xs block mb-1">Delivery Instructions:</span>
                                <span class="text-gray-700 print:text-black text-sm leading-tight">{{ $purchaseOrder->delivery_instructions }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3. ITEMS TABLE --}}
            <div class="mb-8 no-print-break">
                <div class="bg-gray-50 print:bg-transparent border border-gray-200 print:border-black rounded-t-lg print:rounded-none overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 print:bg-gray-50">
                                <th class="py-4 px-4 text-xs font-bold text-gray-700 uppercase tracking-wider w-12 print:text-black print:py-2 print:px-2">No.</th>
                                <th class="py-4 px-4 text-xs font-bold text-gray-700 uppercase tracking-wider print:text-black print:py-2 print:px-2">Item Description</th>
                                <th class="py-4 px-4 text-xs font-bold text-gray-700 uppercase tracking-wider w-24 print:text-black print:py-2 print:px-2">Item Code</th>
                                <th class="py-4 px-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-20 print:text-black print:py-2 print:px-2">Quantity</th>
                                <th class="py-4 px-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-24 print:text-black print:py-2 print:px-2">Unit Price</th>
                                <th class="py-4 px-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-24 print:text-black print:py-2 print:px-2">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($purchaseOrder->purchaseOrderItems as $index => $item)
                            <tr class="border-b border-gray-200 print:border-gray-300 hover:bg-gray-50 print:hover:bg-transparent">
                                <td class="py-4 px-4 text-gray-600 print:text-black print:py-2 print:px-2 text-center font-medium">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 print:py-2 print:px-2">
                                    <div class="font-bold text-gray-900 print:text-black mb-1">{{ $item->item->name }}</div>
                                    @if($item->item->description)
                                        <div class="text-xs text-gray-500 print:text-black mb-1">{{ $item->item->description }}</div>
                                    @endif
                                    @if($item->notes)
                                        <div class="text-xs text-gray-400 print:text-black italic bg-yellow-50 print:bg-transparent px-2 py-1 print:p-0 rounded print:rounded-none">
                                            <i class="fas fa-sticky-note mr-1"></i>{{ $item->notes }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-mono text-xs text-gray-600 print:text-black print:py-2 print:px-2">
                                    <div class="bg-gray-100 print:bg-transparent px-2 py-1 print:p-0 rounded print:rounded-none inline-block">
                                        {{ $item->item->item_code }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center print:py-2 print:px-2">
                                    <div class="font-bold text-gray-900 print:text-black">{{ number_format($item->quantity_ordered, 2) }}</div>
                                    <div class="text-xs text-gray-500 print:text-black">{{ $item->item->unit->symbol ?? 'pcs' }}</div>
                                    @if($item->quantity_received > 0)
                                        <div class="text-xs text-green-600 print:text-black mt-1">
                                            <i class="fas fa-check-circle mr-1"></i>{{ number_format($item->quantity_received, 2) }} received
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right text-gray-700 print:text-black print:py-2 print:px-2">
                                    <div class="font-medium">₱{{ number_format($item->unit_price, 2) }}</div>
                                    @if($item->discount_percentage > 0)
                                        <div class="text-xs text-red-600 print:text-black">
                                            <i class="fas fa-tag mr-1"></i>{{ number_format($item->discount_percentage, 1) }}% off
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right font-bold text-gray-900 print:text-black print:py-2 print:px-2">
                                    ₱{{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                            
                            {{-- Empty rows for better table appearance on screen --}}
                            @for($i = count($purchaseOrder->purchaseOrderItems); $i < min(8, 12); $i++)
                            <tr class="border-b border-gray-100 print:hidden">
                                <td class="py-4 px-4 text-center text-gray-300">{{ $i + 1 }}</td>
                                <td class="py-4 px-4"></td>
                                <td class="py-4 px-4"></td>
                                <td class="py-4 px-4 text-center">-</td>
                                <td class="py-4 px-4 text-right">-</td>
                                <td class="py-4 px-4 text-right">-</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                
                {{-- Table Summary --}}
                <div class="bg-gray-50 print:bg-transparent px-4 py-3 border border-gray-200 border-t-0 print:border-black print:rounded-b-none rounded-b-lg">
                    <div class="flex justify-between text-sm text-gray-600 print:text-black">
                        <span class="font-medium">Total Items: {{ count($purchaseOrder->purchaseOrderItems) }}</span>
                        <span class="font-medium">Total Quantity: {{ number_format($purchaseOrder->purchaseOrderItems->sum('quantity_ordered'), 0) }} units</span>
                    </div>
                </div>
            </div>

            {{-- 4. TOTALS & SUMMARY --}}
            <div class="flex justify-end mb-8 no-print-break">
                <div class="w-80 print:w-72">
                    {{-- Cost Breakdown --}}
                    <div class="bg-white print:bg-transparent border border-gray-200 print:border-black rounded-lg print:rounded-none overflow-hidden">
                        <div class="bg-gray-50 print:bg-gray-50 px-4 py-3 border-b border-gray-200 print:border-black">
                            <h3 class="font-bold text-gray-900 print:text-black text-sm uppercase tracking-wide">Cost Summary</h3>
                        </div>
                        <div class="p-4 print:p-3 space-y-3">
                            <div class="flex justify-between items-center py-1 print:py-0">
                                <span class="text-gray-600 print:text-black">Subtotal:</span>
                                <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                            </div>
                            
                            @if($purchaseOrder->discount_amount > 0)
                            <div class="flex justify-between items-center py-1 print:py-0">
                                <span class="text-gray-600 print:text-black">Total Discount:</span>
                                <span class="font-medium text-red-600 print:text-black">-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
                            </div>
                            @endif

                            @if($purchaseOrder->tax_amount > 0)
                            <div class="flex justify-between items-center py-1 print:py-0">
                                <span class="text-gray-600 print:text-black">Tax Amount:</span>
                                <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                            </div>
                            @endif

                            @if($purchaseOrder->shipping_cost > 0)
                            <div class="flex justify-between items-center py-1 print:py-0">
                                <span class="text-gray-600 print:text-black">Shipping:</span>
                                <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->shipping_cost, 2) }}</span>
                            </div>
                            @endif

                            <div class="border-t border-gray-200 print:border-black pt-2 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-chocolate print:text-black">TOTAL AMOUNT:</span>
                                    <span class="text-lg font-bold text-chocolate print:text-black">₱{{ number_format($purchaseOrder->grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. NOTES & TERMS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 no-print-break">
                {{-- Notes Section --}}
                @if($purchaseOrder->notes || $purchaseOrder->special_instructions)
                <div class="border border-gray-200 rounded-lg p-4 print:border-black print:rounded-none">
                    <h3 class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide mb-3 flex items-center">
                        <i class="fas fa-sticky-note mr-2 text-chocolate print:text-black"></i>
                        Special Instructions
                    </h3>
                    <div class="space-y-2 text-sm">
                        @if($purchaseOrder->notes)
                            <div class="bg-yellow-50 print:bg-transparent border-l-4 border-yellow-400 print:border-black pl-3 print:pl-0">
                                <p class="text-gray-700 print:text-black">{{ $purchaseOrder->notes }}</p>
                            </div>
                        @endif
                        @if($purchaseOrder->special_instructions)
                            <div class="bg-blue-50 print:bg-transparent border-l-4 border-blue-400 print:border-black pl-3 print:pl-0">
                                <p class="text-gray-700 print:text-black">{{ $purchaseOrder->special_instructions }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Terms & Conditions --}}
                <div class="border border-gray-200 rounded-lg p-4 print:border-black print:rounded-none">
                    <h3 class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide mb-3 flex items-center">
                        <i class="fas fa-gavel mr-2 text-chocolate print:text-black"></i>
                        Terms & Conditions
                    </h3>
                    <div class="text-xs text-gray-600 print:text-black space-y-1">
                        <p>• All deliveries must comply with agreed specifications</p>
                        <p>• Invoice must reference this purchase order number</p>
                        <p>• Payment terms: {{ $purchaseOrder->payment_terms ?? 30 }} days from invoice date</p>
                        <p>• Any discrepancies must be reported within 48 hours</p>
                        @if($purchaseOrder->expected_delivery_date)
                            <p>• Expected delivery: {{ $purchaseOrder->expected_delivery_date->format('F d, Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 6. SIGNATURES & APPROVALS --}}
            <div class="mt-auto pt-8 no-print-break">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 print:gap-6">
                    {{-- Prepared By --}}
                    <div class="text-center">
                        <div class="border-b border-gray-400 mb-3 h-16 print:border-black print:h-12"></div>
                        <p class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide">Prepared By</p>
                        <p class="text-xs text-gray-600 print:text-black mt-1">{{ $purchaseOrder->createdBy->name ?? 'System Generated' }}</p>
                        <p class="text-xs text-gray-500 print:text-black">{{ $purchaseOrder->created_at->format('M d, Y') }}</p>
                    </div>

                    {{-- Approved By --}}
                    <div class="text-center">
                        <div class="border-b border-gray-400 mb-3 h-16 print:border-black print:h-12"></div>
                        <p class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide">Approved By</p>
                        <p class="text-xs text-gray-600 print:text-black mt-1">{{ $purchaseOrder->approvedBy->name ?? 'Manager' }}</p>
                        <p class="text-xs text-gray-500 print:text-black">
                            @if($purchaseOrder->approved_at)
                                {{ $purchaseOrder->approved_at->format('M d, Y') }}
                            @else
                                Pending Approval
                            @endif
                        </p>
                    </div>

                    {{-- Received By --}}
                    <div class="text-center">
                        <div class="border-b border-gray-400 mb-3 h-16 print:border-black print:h-12"></div>
                        <p class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide">Received By</p>
                        <p class="text-xs text-gray-600 print:text-black mt-1">____________________</p>
                        <p class="text-xs text-gray-500 print:text-black">Date: ________________</p>
                    </div>
                </div>

                {{-- Document Footer --}}
                <div class="mt-12 pt-6 border-t border-gray-200 print:border-black text-center">
                    <div class="text-xs text-gray-500 print:text-black space-y-1">
                        <p class="font-bold">WellKenz Bakery Enterprise Resource Planning System</p>
                        <p>Document ID: PO-{{ $purchaseOrder->id }} | Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
                        <p class="screen-only">This is a computer-generated document. No signature required for digital copies.</p>
                    </div>
                </div>
            </div>

        </div>
        
        {{-- Footer Strip --}}
        <div class="absolute bottom-0 left-0 right-0 h-4 bg-chocolate screen-only"></div>
    </div>
    
    <p class="text-center text-xs text-gray-400 mt-4 screen-only">
        &copy; {{ date('Y') }} WellKenz Bakery. All rights reserved. | Print optimized for A4 paper.
    </p>

</div>

<style>
    /* Print-specific enhancements - PRESERVE DESIGN */
    @media print {
        /* Page setup with document info */
        @page {
            @bottom-center {
                content: "Purchase Order #{{ $purchaseOrder->po_number }} - Page " counter(page);
                font-size: 9pt;
                color: #666;
            }
            @top-center {
                content: "WellKenz Bakery - Official Purchase Order";
                font-size: 10pt;
                color: #333;
            }
        }

        /* Typography adjustments - keep design */
        h1 { 
            font-size: 18pt !important; 
            color: #3d2817 !important;
            font-family: 'Playfair Display', serif !important;
        }
        h2 { 
            font-size: 14pt !important; 
            color: #3d2817 !important;
        }
        h3 { 
            font-size: 12pt !important; 
            color: #3d2817 !important;
        }

        /* Table optimizations */
        table {
            border-collapse: collapse !important;
        }

        th, td {
            border: 1pt solid #333 !important;
            padding: 8pt !important;
        }

        th {
            background-color: #f9f9f9 !important;
            font-weight: bold !important;
            color: #3d2817 !important;
        }

        /* Prevent page breaks in critical sections */
        .no-print-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Preserve brand colors */
        .text-chocolate { color: #3d2817 !important; }
        .bg-chocolate { background-color: #3d2817 !important; }
        .border-chocolate { border-color: #3d2817 !important; }
        
        /* Background colors for print */
        .bg-gray-50 { background-color: #f9f9f9 !important; }
        .bg-gray-100 { background-color: #f0f0f0 !important; }
        
        /* Signature lines */
        .border-b {
            border-bottom: 1pt solid #333 !important;
        }
    }
</style>

@endsection