@extends('Purchasing.layout.print')

@section('title', 'Stock Level Report - WellKenz Bakery')
@section('document_title', 'Stock Level Report')

@section('content')

<div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

    {{-- ACTION BAR (Visible on Screen Only) --}}
    <div class="w-full max-w-[210mm] mb-6 flex justify-between items-center print:hidden">
        <a href="{{ route('supervisor.inventory.stock-level') }}" class="flex items-center text-sm font-bold text-chocolate hover:text-caramel transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Stock Levels
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
                            <p>Inventory Management</p>
                        </div>
                    </div>
                    <div class="metadata-box">
                        <h2>Stock Level Report</h2>
                        <div class="doc-id">Generated: {{ now()->format('M d, Y h:i A') }}</div>
                        <div class="metadata-item">
                            <span class="metadata-label">User:</span>
                            <span class="metadata-value">{{ auth()->user()->name ?? 'System' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Report Type:</span>
                            <span class="metadata-value">Inventory Analysis</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- 2. KEY METRICS --}}
            <div class="grid grid-cols-4 gap-4 mb-8">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-chocolate">{{ number_format($metrics['total_items']) }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Total Items</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($metrics['healthy_stock']) }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Healthy Stock</div>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format($metrics['low_stock']) }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Low Stock</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center print:border-black">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($metrics['critical_stock']) }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Critical / Out</div>
                </div>
            </div>

            {{-- 3. DATA TABLE --}}
            <div class="mb-8">
                <table class="document-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Item Details</th>
                            <th style="width: 15%;">Category</th>
                            <th style="width: 15%; text-align: right;">Current Stock</th>
                            <th style="width: 15%; text-align: center;">Availability</th>
                            <th style="width: 25%;">Status & Thresholds</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockData as $data)
                            <tr class="no-print-break">
                                <td>
                                    <div class="font-bold text-chocolate text-sm">{{ $data['item']->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">SKU: <span class="font-mono">{{ $data['item']->item_code }}</span></div>
                                </td>
                                <td>
                                    <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded border">
                                        {{ $data['item']->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="font-bold">{{ number_format($data['current_stock'], 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ $data['item']->unit->symbol ?? 'units' }}</div>
                                </td>
                                <td style="padding: 0 15px;">
                                    <div class="bg-gray-200 h-2 rounded-full mb-1">
                                        <div class="h-full rounded-full 
                                            @if($data['status'] == 'Critical') bg-red-500 
                                            @elseif($data['status'] == 'Low') bg-yellow-500 
                                            @else bg-green-500 @endif" 
                                            style="width: {{ min(100, $data['percentage']) }}%">
                                        </div>
                                    </div>
                                    <div class="text-center text-xs text-gray-500">
                                        {{ $data['percentage'] }}% Capacity
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="inline-block px-2 py-1 text-xs font-bold rounded
                                            @if($data['status'] == 'Critical') bg-red-100 text-red-800 border border-red-200
                                            @elseif($data['status'] == 'Low') bg-yellow-100 text-yellow-800 border border-yellow-200
                                            @else bg-green-100 text-green-800 border border-green-200 @endif">
                                            @if($data['status'] == 'Critical')
                                                <i class="fas fa-exclamation-circle mr-1"></i>CRITICAL
                                            @elseif($data['status'] == 'Low')
                                                <i class="fas fa-exclamation-triangle mr-1"></i>LOW
                                            @else
                                                <i class="fas fa-check-circle mr-1"></i>GOOD
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $data['last_movement'] }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Min: <strong>{{ number_format($data['min_stock_level']) }}</strong> | 
                                        Reorder: <strong>{{ number_format($data['reorder_point']) }}</strong>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 4. DOCUMENT FOOTER --}}
            <div class="document-footer">
                <div class="footer-info">
                    <div>© {{ date('Y') }} WellKenz Bakery ERP System</div>
                    <div class="print-timestamp">Generated on: {{ now()->format('F d, Y g:i A') }}</div>
                    <div>Confidential Document</div>
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