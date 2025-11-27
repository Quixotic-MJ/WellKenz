
<div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

    {{-- ACTION BAR (Visible on Screen Only) --}}
    <div class="w-full max-w-[210mm] mb-6 flex justify-between items-center print:hidden">
        <a href="{{ route('inventory.rtv.index') }}" class="flex items-center text-sm font-bold text-chocolate hover:text-caramel transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Log
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
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-[0.03] pointer-events-none">
            <i class="fas fa-birthday-cake text-[400px] text-chocolate"></i>
        </div>

        <div class="relative z-10">
            {{-- 1. DOCUMENT HEADER --}}
            <header class="border-b-2 border-chocolate pb-6 mb-8">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-chocolate text-white rounded-full flex items-center justify-center text-2xl print:border-2 print:border-black print:text-black print:bg-transparent">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div>
                            <h1 class="font-display text-3xl font-bold text-chocolate leading-none tracking-wide print:text-black">WellKenz Bakery</h1>
                            <p class="text-xs text-gray-500 uppercase tracking-[0.2em] mt-1 print:text-black">Artisan Quality Since 2010</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="font-display text-2xl font-bold text-gray-900 uppercase tracking-wide print:text-black">RTV Slip</h2>
                        <p class="text-sm font-mono text-caramel font-bold mt-1 print:text-black">#{{ $rtv->rtv_number }}</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between text-sm text-gray-600 print:text-black">
                    <p>
                        <span class="font-bold text-gray-900 print:text-black">Date Issued:</span> 
                        {{ $rtv->created_at->format('F d, Y h:i A') }}
                    </p>
                    <p>
                        <span class="font-bold text-gray-900 print:text-black">Prepared By:</span> 
                        {{ $rtv->createdBy?->name ?? 'System Admin' }}
                    </p>
                </div>
            </header>

            {{-- 2. INFO GRID --}}
            <div class="grid grid-cols-2 gap-12 mb-8">
                
                {{-- Supplier Details --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Supplier Details</h3>
                    <div class="space-y-1.5 text-sm">
                        <p class="font-bold text-lg text-gray-900 print:text-black">{{ $rtv->supplier->name }}</p>
                        <p class="text-gray-600 print:text-black"><span class="w-20 inline-block font-medium text-gray-400 print:text-black">Code:</span> {{ $rtv->supplier->supplier_code }}</p>
                        <p class="text-gray-600 print:text-black"><span class="w-20 inline-block font-medium text-gray-400 print:text-black">Contact:</span> {{ $rtv->supplier->contact_person ?? 'N/A' }}</p>
                        <p class="text-gray-600 print:text-black"><span class="w-20 inline-block font-medium text-gray-400 print:text-black">Phone:</span> {{ $rtv->supplier->phone ?? 'N/A' }}</p>
                        <p class="text-gray-600 print:text-black"><span class="w-20 inline-block font-medium text-gray-400 print:text-black">Address:</span> {{ $rtv->supplier->address ?? 'N/A' }}</p>
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div class="border border-gray-200 rounded-lg p-5 print:border-black">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 print:text-black print:border-black">Reference Info</h3>
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
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-800 print:border-black">
                            <th class="py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-12 print:text-black">No.</th>
                            <th class="py-3 text-xs font-bold text-gray-500 uppercase tracking-wider print:text-black">Item Description</th>
                            <th class="py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-24 print:text-black">SKU</th>
                            <th class="py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24 print:text-black">Qty</th>
                            <th class="py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32 print:text-black">Unit Cost</th>
                            <th class="py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32 print:text-black">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($rtv->rtvItems as $index => $item)
                        <tr class="border-b border-gray-200 print:border-gray-400">
                            <td class="py-4 text-gray-500 print:text-black">{{ $index + 1 }}</td>
                            <td class="py-4">
                                <p class="font-bold text-gray-900 print:text-black">{{ $item->item->name }}</p>
                                @if($item->reason)
                                    <p class="text-xs text-red-600 mt-1 italic print:text-black">Reason: {{ $item->reason }}</p>
                                @endif
                            </td>
                            <td class="py-4 font-mono text-xs text-gray-600 print:text-black">{{ $item->item->item_code }}</td>
                            <td class="py-4 text-center">
                                <span class="font-bold">{{ number_format($item->quantity_returned, 2) }}</span>
                                <span class="text-xs text-gray-500 block print:text-black">{{ $item->item->unit->symbol ?? 'pcs' }}</span>
                            </td>
                            <td class="py-4 text-right text-gray-600 print:text-black">₱{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="py-4 text-right font-bold text-gray-900 print:text-black">₱{{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @endforeach
                        
                        {{-- Fill empty rows for aesthetics if needed (optional) --}}
                        @for($i = count($rtv->rtvItems); $i < 5; $i++)
                        <tr class="border-b border-gray-100 print:hidden">
                            <td class="py-4 text-transparent">.</td>
                            <td class="py-4"></td>
                            <td class="py-4"></td>
                            <td class="py-4"></td>
                            <td class="py-4"></td>
                            <td class="py-4"></td>
                        </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 print:bg-transparent">
                            <td colspan="5" class="py-4 pr-4 text-right text-sm font-bold text-gray-500 uppercase tracking-wide border-b border-gray-800 print:text-black print:border-black">Subtotal</td>
                            <td class="py-4 text-right text-base font-bold text-gray-900 border-b border-gray-800 print:text-black print:border-black">₱{{ number_format($rtv->total_value, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 4. NOTES & SUMMARY --}}
            @if($rtv->notes)
            <div class="mb-12 border border-gray-200 rounded-lg p-4 bg-cream-bg/30 print:border-black print:bg-transparent">
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
        <div class="absolute bottom-0 left-0 right-0 h-4 bg-chocolate print:hidden"></div>
    </div>
    
    <p class="text-center text-xs text-gray-400 mt-4 print:hidden">
        &copy; {{ date('Y') }} WellKenz Bakery. All rights reserved.
    </p>

</div>

<style>
    @media print {
        /* Hide Admin UI Shell */
        nav, header, aside, .print\:hidden {
            display: none !important;
        }

        /* Reset Body for Print */
        body {
            background: white !important;
            color: black !important;
            margin: 0;
            padding: 0;
        }

        /* Content Reset */
        .min-h-screen {
            min-h-0 !important;
        }
        
        /* Typography adjustments */
        .text-chocolate, .text-caramel {
            color: black !important;
        }
        
        /* Ensure background colors are printed if necessary (usually browsers strip them) */
        .bg-gray-50 {
            background-color: #f9fafb !important;
            -webkit-print-color-adjust: exact;
        }

        /* Page margins */
        @page {
            margin: 15mm;
            size: A4;
        }
    }
</style>