<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Use First List - WellKenz Bakery</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS Variables for Brand Colors */
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
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-200: #fecaca;
            --red-600: #dc2626;
            --red-800: #991b1b;
            --yellow-50: #fffbeb;
            --yellow-100: #fef3c7;
            --yellow-200: #fde68a;
            --yellow-600: #d97706;
            --yellow-800: #92400e;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --orange-50: #fff7ed;
            --orange-600: #ea580c;
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: var(--text-dark);
            background: white;
            margin: 0;
            padding: 15mm;
        }

        /* Page Setup for PDF */
        @page {
            margin: 15mm;
            size: A4;
            @bottom-center {
                content: "WellKenz Bakery - " counter(page) " of " counter(pages);
                font-size: 9pt;
                color: #666;
            }
            @top-center {
                content: "Use First List - WellKenz Bakery";
                font-size: 10pt;
                color: #333;
            }
        }

        /* Typography */
        .font-display { 
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .font-mono { font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; }
        .uppercase { text-transform: uppercase; }
        .tracking-wide { letter-spacing: 0.025em; }
        .tracking-wider { letter-spacing: 0.05em; }

        /* Layout */
        .min-h-screen {
            min-height: 100vh;
        }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .w-full { width: 100%; }
        .h-4 { height: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .p-4 { padding: 1rem; }
        .p-8 { padding: 2rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .gap-15px { gap: 15px; }
        .grid { display: grid; }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .top-1\/2 { top: 50%; }
        .left-1\/2 { left: 50%; }
        .bottom-0 { bottom: 0; }
        .left-0 { left: 0; }
        .right-0 { right: 0; }
        .transform { transform: translate(-50%, -50%); }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-2xl { font-size: 1.5rem; }

        /* Brand Colors */
        .text-chocolate { color: var(--chocolate); }
        .text-caramel { color: var(--caramel); }
        .text-red-600 { color: var(--red-600); }
        .text-red-800 { color: var(--red-800); }
        .text-yellow-600 { color: var(--yellow-600); }
        .text-yellow-800 { color: var(--yellow-800); }
        .text-gray-500 { color: var(--gray-500); }
        .text-gray-700 { color: var(--gray-700); }
        .text-green-600 { color: #16a34a; }
        .text-green-800 { color: #166534; }
        .bg-chocolate { background-color: var(--chocolate); }
        .bg-red-50 { background-color: var(--red-50); }
        .bg-red-100 { background-color: var(--red-100); }
        .bg-yellow-50 { background-color: var(--yellow-50); }
        .bg-yellow-100 { background-color: var(--yellow-100); }
        .bg-gray-50 { background-color: var(--gray-50); }
        .bg-orange-50 { background-color: var(--orange-50); }
        .bg-white { background-color: white; }

        /* Borders */
        .border { border-width: 1px; }
        .border-2 { border-width: 2px; }
        .border-red-200 { border-color: var(--red-200); }
        .border-yellow-200 { border-color: var(--yellow-200); }
        .border-gray-200 { border-color: var(--gray-200); }
        .border-gray-300 { border-color: var(--gray-300); }
        .border-dashed { border-style: dashed; }
        .border-l-4 { border-left-width: 4px; }
        .border-caramel { border-color: var(--caramel); }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded { border-radius: 0.25rem; }

        /* Shadows */
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }

        /* Document Specific Styles */
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

        /* Table Styles */
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

        /* Status Styles */
        .inline-block {
            display: inline-block;
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

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            pointer-events: none;
            z-index: 1;
        }

        .watermark i {
            font-size: 400px;
            color: var(--chocolate);
        }

        /* Utility Classes */
        .space-y-1 > * + * {
            margin-top: 0.25rem;
        }

        /* Footer Strip */
        .footer-strip {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--chocolate);
        }

        /* Page Break Control */
        .no-print-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen bg-white flex flex-col items-center">
        
        {{-- A4 PAPER CONTAINER --}}
        <div class="bg-white w-full min-h-[297mm] relative">
            
            {{-- WATERMARK (Background Decor) --}}
            <div class="watermark">
                <i class="fas fa-birthday-cake"></i>
            </div>

            <div class="relative z-10 p-[15mm]">
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
                            <div class="doc-id">Generated: {{ $generatedAt ?? now()->format('M d, Y h:i A') }}</div>
                            <div class="metadata-item">
                                <span class="metadata-label">User:</span>
                                <span class="metadata-value">{{ $generatedBy ?? auth()->user()->name ?? 'System' }}</span>
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
                        $criticalCount = $criticalCount ?? 0;
                        $warningCount = $warningCount ?? 0;
                        $totalCount = $totalCount ?? ($batches->count() ?? 0);
                    @endphp
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $criticalCount }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Critical Items</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $warningCount }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Warning Items</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-chocolate">{{ $totalCount }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Total Items</div>
                    </div>
                </div>

                {{-- 3. INSTRUCTIONS BOX --}}
                <div class="bg-orange-50 border-l-4 border-caramel p-4 mb-6">
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
                @if(($batches->count() ?? 0) > 0)
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
                        <div class="print-timestamp">Generated on: {{ $generatedAt ?? now()->format('F d, Y g:i A') }}</div>
                        <div class="font-bold uppercase tracking-wide">Internal Use Only</div>
                    </div>
                </div>
            </div>
            
            {{-- Footer Strip --}}
            <div class="footer-strip"></div>
        </div>
        
    </div>
</body>
</html>