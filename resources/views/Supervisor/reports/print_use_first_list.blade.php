@extends('Purchasing.layout.print')

@section('title', 'Use First List - WellKenz Bakery')
@section('document_title', 'Use First List')

@section('content')

<div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

    {{-- ACTION BAR (Visible on Screen Only) --}}
    <div class="w-full max-w-[210mm] mb-6 flex justify-between items-center print:hidden">
        <a href="{{ route('supervisor.reports.expiry') }}" class="flex items-center text-sm font-bold text-chocolate hover:text-caramel transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Reports
        </a>
        <div class="flex gap-3">
            <button onclick="window.print()" class="flex items-center px-5 py-2.5 bg-chocolate text-white font-bold rounded-lg shadow-md hover:bg-chocolate-dark transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-print mr-2"></i> Print Report
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
                            <p>Bakery Inventory</p>
                        </div>
                    </div>
                    <div class="metadata-box">
                        <h2>Use First List</h2>
                        <div class="doc-id">Generated: {{ now()->format('M d, Y h:i A') }}</div>
                        <div class="metadata-item">
                            <span class="metadata-label">User:</span>
                            <span class="metadata-value">{{ auth()->user()->name ?? 'System' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Priority:</span>
                            <span class="metadata-value text-red-600 font-bold">ACTION REQUIRED</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- 2. SUMMARY GRID --}}
            <div class="grid grid-cols-3 gap-4 mb-8">
                @php
                    $criticalCount = $batches->filter(function($batch) {
                        $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                        return $expiryDate->isPast() || \Carbon\Carbon::now()->diffInDays($expiryDate, false) <= 1;
                    })->count();
                    
                    $warningCount = $batches->filter(function($batch) {
                        $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                        $daysUntilExpiry = \Carbon\Carbon::now()->diffInDays($expiryDate, false);
                        return $daysUntilExpiry > 1 && $daysUntilExpiry <= 7;
                    })->count();
                @endphp
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-red-600">{{ $criticalCount }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Critical Items</div>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-yellow-600">{{ $warningCount }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Warning Items</div>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-chocolate">{{ $batches->count() }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Total Items</div>
                </div>
            </div>

            {{-- 3. INSTRUCTIONS BOX --}}
            <div class="bg-orange-50 border-l-4 border-caramel p-4 mb-6 print:border-black print:bg-transparent">
                <div class="flex items-center mb-2">
                    <i class="fas fa-clipboard-check text-chocolate mr-2"></i>
                    <h3 class="font-bold text-chocolate text-sm uppercase tracking-wide">Baker Instructions</h3>
                </div>
                <div class="text-xs space-y-1">
                    <p><strong class="text-red-600">RED HIGHLIGHTS:</strong> Must be used in today's production or transferred to staff meals immediately.</p>
                    <p><strong class="text-yellow-600">ORANGE HIGHLIGHTS:</strong> Schedule these items for production within the current week.</p>
                    <p><strong>FIFO POLICY:</strong> Always verify batch numbers matches the physical item before use.</p>
                </div>
            </div>

            {{-- 4. DATA TABLE --}}
            @if($batches->count() > 0)
                <div class="mb-8">
                    <table class="document-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Item Details</th>
                                <th style="width: 15%;">Category</th>
                                <th style="width: 15%;">Expiry Date</th>
                                <th style="width: 10%; text-align: center;">Status</th>
                                <th style="width: 15%; text-align: right;">Qty</th>
                                <th style="width: 15%;">Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                                    $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                                    $isPastExpiry = $expiryDate->isPast();
                                    
                                    $rowClass = '';
                                    $statusText = 'NORMAL';
                                    $statusStyle = 'bg-green-100 text-green-800 border-green-200'; // Good (Green)

                                    if ($isPastExpiry) {
                                        $rowClass = 'bg-red-50';
                                        $statusText = 'EXPIRED';
                                        $statusStyle = 'bg-red-100 text-red-800 border-red-200'; // Critical (Red)
                                    } elseif ($daysUntilExpiry <= 1) {
                                        $rowClass = 'bg-red-50';
                                        $statusText = 'CRITICAL';
                                        $statusStyle = 'bg-red-100 text-red-800 border-red-200'; // Critical (Red)
                                    } elseif ($daysUntilExpiry <= 7) {
                                        $statusText = 'WARNING';
                                        $statusStyle = 'bg-yellow-100 text-yellow-800 border-yellow-200'; // Warning (Orange)
                                    }
                                @endphp
                                <tr class="no-print-break {{ $rowClass }}">
                                    <td>
                                        <div class="font-bold text-chocolate">{{ $batch->item->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            SKU: <span class="font-mono">{{ $batch->item->item_code }}</span> | 
                                            Batch: <span class="font-mono">{{ $batch->batch_number }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded border">
                                            {{ $batch->item->category->name ?? 'General' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $expiryDate->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $expiryDate->format('l') }}</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="inline-block px-2 py-1 text-xs font-bold rounded border {{ $statusStyle }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="font-bold">{{ number_format($batch->quantity, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $batch->item->unit->symbol ?? 'units' }}</div>
                                    </td>
                                    <td class="text-xs">
                                        {{ \Illuminate\Support\Str::limit($batch->supplier->name ?? 'Unknown', 20) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                    <p class="font-medium text-gray-700">No priority items found.</p>
                    <p class="text-sm text-gray-500 mt-1">All inventory batches are fresh.</p>
                </div>
            @endif

            {{-- 5. DOCUMENT FOOTER --}}
            <div class="document-footer">
                <div class="footer-info">
                    <div>© {{ date('Y') }} WellKenz Bakery ERP System</div>
                    <div class="print-timestamp">Generated on: {{ now()->format('F d, Y g:i A') }}</div>
                    <div class="font-bold uppercase tracking-wide">Internal Use Only</div>
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