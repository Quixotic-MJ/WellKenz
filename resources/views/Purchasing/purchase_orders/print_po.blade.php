<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order #{{ $purchaseOrder->po_number ?? 'N/A' }} - WellKenz Bakery</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Variables for Brand Colors -->
    <style>
        :root {
            --chocolate: #3d2817;
            --chocolate-dark: #2a1a0f;
            --caramel: #c48d3f;
            --caramel-dark: #a67332;
            --text-dark: #1a1410;
            --text-muted: #8b7355;
            --border-soft: #e8dfd4;
            --cream-bg: #faf7f3;
            --white: #ffffff;
        }

        /* Print-optimized styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Page setup */
            @page {
                margin: 12mm;
                size: A4;
                @bottom-center {
                    content: "Purchase Order #{{ $purchaseOrder->po_number ?? 'N/A' }} - Page " counter(page);
                    font-size: 8pt;
                    color: #666;
                }
                @top-center {
                    content: "WellKenz Bakery - Official Purchase Order";
                    font-size: 9pt;
                    color: #333;
                }
            }

            /* Reset margins and padding */
            body {
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                font-size: 9pt !important;
                line-height: 1.3 !important;
                color: #1a1410 !important;
                background: white !important;
            }

            /* Hide screen-only elements */
            .screen-only {
                display: none !important;
            }

            /* Container adjustments for paper */
            .print-preview-container {
                max-width: none !important;
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: white !important;
            }

            .min-h-screen {
                min-height: auto !important;
                padding: 0 !important;
            }

            .bg-white {
                width: 100% !important;
                max-width: 190mm !important;
                min-height: auto !important;
                padding: 8mm !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                overflow: hidden !important;
            }

            /* Prevent content overflow */
            .relative.z-10 {
                overflow: hidden !important;
            }

            /* Grid adjustments */
            .grid-cols-2 {
                gap: 8pt !important;
            }

            .gap-8 {
                gap: 8pt !important;
            }

            /* Brand colors - preserved for print */
            .text-chocolate { color: #3d2817 !important; }
            .text-caramel { color: #c48d3f !important; }
            .border-chocolate { border-color: #3d2817 !important; }
            .bg-chocolate { background-color: #3d2817 !important; }
            
            /* Typography optimizations for print */
            .font-display { 
                font-family: 'Playfair Display', serif !important;
                font-weight: 700 !important;
            }
            .font-bold { font-weight: 600 !important; }
            .font-medium { font-weight: 500 !important; }
            .uppercase { text-transform: uppercase !important; }
            .tracking-wide { letter-spacing: 0.025em !important; }
            .tracking-wider { letter-spacing: 0.05em !important; }

            /* Header sizing for print */
            .document-header {
                margin-bottom: 15pt !important;
                padding-bottom: 10pt !important;
            }

            .brand-text h1 {
                font-size: 16pt !important;
                margin-bottom: 2pt !important;
            }

            .brand-text p {
                font-size: 8pt !important;
            }

            .brand-logo {
                width: 30pt !important;
                height: 30pt !important;
                font-size: 12pt !important;
            }

            .metadata-box {
                padding: 6pt !important;
                min-width: 120pt !important;
                max-width: 140pt !important;
            }

            .metadata-box h2 {
                font-size: 10pt !important;
                margin-bottom: 3pt !important;
            }

            .metadata-item {
                font-size: 7pt !important;
                margin-bottom: 1pt !important;
            }

            /* Table optimizations */
            table {
                border-collapse: collapse !important;
                margin-bottom: 12pt !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            th, td {
                border: 0.5pt solid #333 !important;
                padding: 3pt 4pt !important;
                font-size: 7pt !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            th {
                background-color: #f5f5f5 !important;
                font-weight: 600 !important;
                color: #3d2817 !important;
                font-size: 6pt !important;
                padding: 4pt !important;
            }

            /* Table column widths */
            .document-table th:nth-child(1),
            .document-table td:nth-child(1) { width: 8% !important; } /* No. */
            .document-table th:nth-child(2),
            .document-table td:nth-child(2) { width: 35% !important; } /* Description */
            .document-table th:nth-child(3),
            .document-table td:nth-child(3) { width: 15% !important; } /* Item Code */
            .document-table th:nth-child(4),
            .document-table td:nth-child(4) { width: 12% !important; } /* Quantity */
            .document-table th:nth-child(5),
            .document-table td:nth-child(5) { width: 15% !important; } /* Unit Price */
            .document-table th:nth-child(6),
            .document-table td:nth-child(6) { width: 15% !important; } /* Line Total */

            /* Info grid adjustments */
            .grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
                gap: 8pt !important;
                max-width: 100% !important;
            }

            .border {
                border: 0.5pt solid #333 !important;
                padding: 6pt !important;
                margin-bottom: 8pt !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .rounded-lg {
                border-radius: 0 !important;
            }

            /* Flex container adjustments */
            .flex {
                flex-wrap: wrap !important;
            }

            .justify-between {
                justify-content: space-between !important;
            }

            /* Typography adjustments */
            h1 { 
                font-size: 16pt !important; 
                color: #3d2817 !important;
                font-family: 'Playfair Display', serif !important;
                line-height: 1.2 !important;
            }
            h2 { 
                font-size: 12pt !important; 
                color: #3d2817 !important;
                margin-bottom: 4pt !important;
            }
            h3 { 
                font-size: 10pt !important; 
                color: #3d2817 !important;
                margin-bottom: 6pt !important;
            }

            /* Text sizing adjustments */
            .text-sm { font-size: 8pt !important; }
            .text-xs { font-size: 7pt !important; }
            .text-lg { font-size: 11pt !important; }

            /* Signature section */
            .grid-cols-3 {
                grid-template-columns: 1fr 1fr 1fr !important;
                gap: 15pt !important;
                margin-top: 15pt !important;
            }

            .h-16 {
                height: 25pt !important;
            }

            /* Footer adjustments */
            .mt-12 {
                margin-top: 20pt !important;
            }

            .pt-6 {
                padding-top: 10pt !important;
            }

            .border-t {
                border-top: 0.5pt solid #333 !important;
            }

            /* Special instructions */
            .space-y-2 > * + * {
                margin-top: 4pt !important;
            }

            /* Background colors for print */
            .bg-gray-50 { background-color: #f9f9f9 !important; }
            .bg-gray-100 { background-color: #f0f0f0 !important; }
            
            /* Signature lines */
            .border-b {
                border-bottom: 0.5pt solid #333 !important;
            }

            /* Prevent page breaks in critical sections */
            .no-print-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Hide footer strip on print */
            .absolute.bottom-0 {
                display: none !important;
            }
        }

        /* Screen styles for print preview */
        @media screen {
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                min-height: 100vh;
            }

            .print-preview-container {
                max-width: 210mm;
                margin: 20px auto;
                background: white;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                border-radius: 8px;
                overflow: hidden;
            }
        }

        /* Hide print elements on screen */
        .print-only {
            display: none;
        }

        @media print {
            .print-only {
                display: block !important;
            }
        }

        /* Common Document Styles */
        .document-header {
            border-bottom: 2px solid var(--chocolate);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            background: var(--chocolate);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .brand-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--chocolate);
            margin: 0;
            line-height: 1;
        }

        .brand-text p {
            color: var(--caramel);
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 2px;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }

        .metadata-box {
            background: var(--cream-bg);
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            padding: 15px;
            text-align: right;
            min-width: 200px;
        }

        .metadata-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--chocolate);
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metadata-box .doc-id {
            font-family: monospace;
            font-weight: 600;
            color: var(--caramel);
            margin-bottom: 8px;
        }

        .metadata-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .metadata-label {
            font-weight: 600;
            color: var(--text-muted);
        }

        .metadata-value {
            font-weight: 500;
            color: var(--text-dark);
        }

        /* Standardized Table Styles */
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .document-table th {
            background-color: var(--chocolate);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.5px;
            padding: 12px 8px;
            text-align: left;
        }

        .document-table td {
            border-bottom: 1px solid var(--border-soft);
            padding: 10px 8px;
            font-size: 10pt;
            vertical-align: top;
        }

        .document-table tr:nth-child(even) {
            background-color: rgba(250, 247, 243, 0.3);
        }

        /* Document Footer */
        .document-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-soft);
            text-align: center;
            font-size: 9pt;
            color: var(--text-muted);
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .print-timestamp {
            font-weight: 600;
            color: var(--text-dark);
        }
    </style>
</head>

<body class="antialiased">
    
    <!-- Main Content Area -->
    <main class="print-preview-container">
        <div class="min-h-screen bg-cream-bg p-8 flex flex-col items-center print:p-0 print:bg-white print:block">

            {{-- A4 PAPER CONTAINER --}}
            <div class="bg-white w-full max-w-[210mm] min-h-[297mm] p-[15mm] shadow-2xl rounded-sm print:shadow-none print:w-full print:max-w-none print:p-0 print:m-0 relative print:rounded-none">
                
                {{-- WATERMARK (Background Decor) --}}
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-[0.03] pointer-events-none screen-only">
                    <i class="fas fa-birthday-cake text-[400px] text-chocolate"></i>
                </div>

                <div class="relative z-10">
                    {{-- 1. DOCUMENT HEADER --}}
                    <header class="document-header no-print-break">
                        <div class="flex justify-between items-start">
                            <div class="brand-block">
                                <div class="brand-logo">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div class="brand-text">
                                    <h1>WellKenz Bakery</h1>
                                    <p>Official Purchase Order Document</p>
                                </div>
                            </div>
                            <div class="metadata-box">
                                <h2>PO #{{ $purchaseOrder->po_number ?? 'N/A' }}</h2>
                                <div class="doc-id">ID: {{ $purchaseOrder->id ?? 'N/A' }}</div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Date Issued:</span>
                                    <span class="metadata-value">{{ $purchaseOrder->created_at ? $purchaseOrder->created_at->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Created By:</span>
                                    <span class="metadata-value">{{ $purchaseOrder->createdBy->name ?? 'System' }}</span>
                                </div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Status:</span>
                                    <span class="metadata-value font-bold">{{ $purchaseOrder->status ?? 'Pending' }}</span>
                                </div>
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
                                    <p class="font-bold text-lg text-gray-900 print:text-black leading-tight">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
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
                                    @if($purchaseOrder->supplier->tax_id ?? false)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 print:text-black w-20">Tax ID:</span>
                                            <span class="text-gray-700 print:text-black font-mono">{{ $purchaseOrder->supplier->tax_id }}</span>
                                        </div>
                                    @endif
                                    @if($purchaseOrder->supplier->payment_terms ?? false)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-500 print:text-black w-20">Terms:</span>
                                            <span class="text-gray-700 print:text-black">{{ $purchaseOrder->supplier->payment_terms }} days</span>
                                        </div>
                                    @endif
                                </div>
                                @if($purchaseOrder->supplier->address ?? false)
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
                        <table class="document-table">
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
                                    @forelse($purchaseOrder->purchaseOrderItems as $index => $item)
                                    <tr class="border-b border-gray-200 print:border-gray-300 hover:bg-gray-50 print:hover:bg-transparent">
                                        <td class="py-4 px-4 text-gray-600 print:text-black print:py-2 print:px-2 text-center font-medium">{{ $index + 1 }}</td>
                                        <td class="py-4 px-4 print:py-2 print:px-2">
                                            <div class="font-bold text-gray-900 print:text-black mb-1">{{ $item->item->name ?? 'N/A' }}</div>
                                            @if($item->item->description ?? false)
                                                <div class="text-xs text-gray-500 print:text-black mb-1">{{ $item->item->description }}</div>
                                            @endif
                                            @if($item->notes ?? false)
                                                <div class="text-xs text-gray-400 print:text-black italic bg-yellow-50 print:bg-transparent px-2 py-1 print:p-0 rounded print:rounded-none">
                                                    <i class="fas fa-sticky-note mr-1"></i>{{ $item->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 font-mono text-xs text-gray-600 print:text-black print:py-2 print:px-2">
                                            <div class="bg-gray-100 print:bg-transparent px-2 py-1 print:p-0 rounded print:rounded-none inline-block">
                                                {{ $item->item->item_code ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center print:py-2 print:px-2">
                                            <div class="font-bold text-gray-900 print:text-black">{{ number_format($item->quantity_ordered ?? 0, 2) }}</div>
                                            <div class="text-xs text-gray-500 print:text-black">{{ $item->item->unit->symbol ?? ($item->unit ?? 'pcs') }}</div>
                                            @if(($item->quantity_received ?? 0) > 0)
                                                <div class="text-xs text-green-600 print:text-black mt-1">
                                                    <i class="fas fa-check-circle mr-1"></i>{{ number_format($item->quantity_received, 2) }} received
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right text-gray-700 print:text-black print:py-2 print:px-2">
                                            <div class="font-medium">₱{{ number_format($item->unit_price ?? 0, 2) }}</div>
                                            @if(($item->discount_percentage ?? 0) > 0)
                                                <div class="text-xs text-red-600 print:text-black">
                                                    <i class="fas fa-tag mr-1"></i>{{ number_format($item->discount_percentage, 1) }}% off
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-gray-900 print:text-black print:py-2 print:px-2">
                                            ₱{{ number_format($item->total_price ?? ($item->quantity_ordered * $item->unit_price), 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-500 print:text-black">
                                            <i class="fas fa-inbox text-2xl mb-2"></i>
                                            <p>No items found for this purchase order.</p>
                                        </td>
                                    </tr>
                                    @endforelse

                                </tbody>
                            </table>
                            
                            {{-- Table Summary --}}
                            <div class="bg-gray-50 print:bg-transparent px-4 py-3 border border-gray-200 border-t-0 print:border-black print:rounded-b-none rounded-b-lg">
                                <div class="flex justify-between text-sm text-gray-600 print:text-black">
                                    <span class="font-medium">Total Items: 
                                        @php
                                            $totalItems = 0;
                                            $totalQuantity = 0;
                                            
                                            if ($purchaseOrder->purchaseOrderItems && count($purchaseOrder->purchaseOrderItems) > 0) {
                                                $totalItems = count($purchaseOrder->purchaseOrderItems);
                                                $totalQuantity = $purchaseOrder->purchaseOrderItems->sum('quantity_ordered');
                                            }
                                        @endphp
                                        {{ $totalItems }}
                                    </span>
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
                                        <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</span>
                                    </div>
                                    
                                    @if(($purchaseOrder->discount_amount ?? 0) > 0)
                                    <div class="flex justify-between items-center py-1 print:py-0">
                                        <span class="text-gray-600 print:text-black">Total Discount:</span>
                                        <span class="font-medium text-red-600 print:text-black">-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
                                    </div>
                                    @endif

                                    @if(($purchaseOrder->tax_amount ?? 0) > 0)
                                    <div class="flex justify-between items-center py-1 print:py-0">
                                        <span class="text-gray-600 print:text-black">Tax Amount:</span>
                                        <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                                    </div>
                                    @endif

                                    @if(($purchaseOrder->shipping_cost ?? 0) > 0)
                                    <div class="flex justify-between items-center py-1 print:py-0">
                                        <span class="text-gray-600 print:text-black">Shipping:</span>
                                        <span class="font-medium text-gray-900 print:text-black">₱{{ number_format($purchaseOrder->shipping_cost, 2) }}</span>
                                    </div>
                                    @endif

                                    <div class="border-t border-gray-200 print:border-black pt-2 mt-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-chocolate print:text-black">TOTAL AMOUNT:</span>
                                            <span class="text-lg font-bold text-chocolate print:text-black">₱{{ number_format($purchaseOrder->grand_total ?? ($purchaseOrder->total_amount + ($purchaseOrder->tax_amount ?? 0) + ($purchaseOrder->shipping_cost ?? 0) - ($purchaseOrder->discount_amount ?? 0)), 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. NOTES SECTION --}}
                    @if(($purchaseOrder->notes ?? false) || ($purchaseOrder->special_instructions ?? false) || ($purchaseOrder->delivery_instructions ?? false))
                    <div class="mb-8 no-print-break">
                        <div class="border border-gray-200 rounded-lg p-4 print:border-black print:rounded-none">
                            <h3 class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide mb-3 flex items-center">
                                <i class="fas fa-sticky-note mr-2 text-chocolate print:text-black"></i>
                                Special Instructions
                            </h3>
                            <div class="space-y-2 text-sm">
                                @if($purchaseOrder->notes ?? false)
                                    <div class="bg-yellow-50 print:bg-transparent border-l-4 border-yellow-400 print:border-black pl-3 print:pl-0">
                                        <p class="text-gray-700 print:text-black">{{ $purchaseOrder->notes }}</p>
                                    </div>
                                @endif
                                @if($purchaseOrder->special_instructions ?? false)
                                    <div class="bg-blue-50 print:bg-transparent border-l-4 border-blue-400 print:border-black pl-3 print:pl-0">
                                        <p class="text-gray-700 print:text-black">{{ $purchaseOrder->special_instructions }}</p>
                                    </div>
                                @endif
                                @if($purchaseOrder->delivery_instructions ?? false)
                                    <div class="bg-green-50 print:bg-transparent border-l-4 border-green-400 print:border-black pl-3 print:pl-0">
                                        <p class="text-gray-700 print:text-black">{{ $purchaseOrder->delivery_instructions }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 6. SIGNATURES & APPROVALS --}}
                    <div class="mt-auto pt-8 no-print-break">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 print:gap-6">
                            {{-- Prepared By --}}
                            <div class="text-center">
                                <div class="border-b border-gray-400 mb-3 h-16 print:border-black print:h-12"></div>
                                <p class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide">Prepared By</p>
                                <p class="text-xs text-gray-600 print:text-black mt-1">{{ $purchaseOrder->createdBy->name ?? 'System Generated' }}</p>
                                <p class="text-xs text-gray-500 print:text-black">{{ $purchaseOrder->created_at ? $purchaseOrder->created_at->format('M d, Y') : 'N/A' }}</p>
                            </div>

                            {{-- Approved By --}}
                            <div class="text-center">
                                <div class="border-b border-gray-400 mb-3 h-16 print:border-black print:h-12"></div>
                                <p class="text-sm font-bold text-gray-900 print:text-black uppercase tracking-wide">Approved By</p>
                                <p class="text-xs text-gray-600 print:text-black mt-1">{{ $purchaseOrder->approvedBy->name ?? 'Manager' }}</p>
                                <p class="text-xs text-gray-500 print:text-black">
                                    @if($purchaseOrder->approved_at ?? false)
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
                                <p>Document ID: PO-{{ $purchaseOrder->id ?? 'N/A' }} | Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
                                @if($purchaseOrder->po_number ?? false)
                                    <p>PO Number: {{ $purchaseOrder->po_number }}</p>
                                @endif
                                <p class="screen-only">This is a computer-generated document. No signature required for digital copies.</p>
                                <p class="print-only">Printed on: {{ now()->format('Y-m-d H:i:s') }}</p>
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
    </main>

    <!-- Scripts -->
    <script>
        // Auto-print functionality
        window.onload = function() { 
            window.print(); 
        };
    </script>

</body>

</html>