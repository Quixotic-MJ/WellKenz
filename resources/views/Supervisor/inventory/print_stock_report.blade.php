<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level Report - WellKenz Bakery</title>
    
    {{-- Load Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    {{-- Font Awesome for Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --chocolate: #3d2817;
            --caramel: #c48d3f;
            --cream: #faf7f3;
            --border: #e8dfd4;
            --text-main: #374151;
            --text-light: #6b7280;
        }

        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: var(--text-main);
            background-color: white;
            margin: 0;
            padding: 20px;
        }

        /* Typography */
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; color: var(--chocolate); margin: 0; }
        
        /* Utilities */
        .no-print { }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 600; }
        .uppercase { text-transform: uppercase; }
        .tracking-wide { letter-spacing: 0.05em; }

        /* Header */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid var(--chocolate);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .brand-section h1 { font-size: 24pt; line-height: 1; }
        .brand-section p { color: var(--caramel); font-weight: bold; font-size: 9pt; letter-spacing: 2px; margin-top: 5px; text-transform: uppercase; }

        .report-info { text-align: right; }
        .report-info h2 { font-size: 16pt; margin-bottom: 5px; }
        .report-info p { font-size: 9pt; color: var(--text-light); }

        /* Metrics */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .metric-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            background-color: var(--cream);
        }

        .metric-title { font-size: 8pt; font-weight: bold; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-value { font-size: 18pt; font-weight: 700; color: var(--chocolate); margin-top: 5px; font-family: 'Playfair Display', serif; }

        .metric-card.good .metric-value { color: #059669; }
        .metric-card.low .metric-value { color: #d97706; }
        .metric-card.critical .metric-value { color: #dc2626; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: var(--chocolate);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
        }

        td {
            border-bottom: 1px solid var(--border);
            padding: 8px;
            font-size: 9pt;
            vertical-align: middle;
        }

        tr:nth-child(even) { background-color: rgba(250, 247, 243, 0.5); }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-good { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-low { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-critical { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-cat { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

        /* Progress Bar */
        .progress-track {
            background-color: #e5e7eb;
            height: 6px;
            border-radius: 3px;
            width: 100%;
            margin-top: 4px;
            overflow: hidden;
        }
        .progress-fill { height: 100%; border-radius: 3px; }
        .fill-good { background-color: #10b981; }
        .fill-low { background-color: #f59e0b; }
        .fill-critical { background-color: #ef4444; }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            font-size: 8pt;
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
        }

        /* Print Button (Screen Only) */
        .fab-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--chocolate);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(61, 40, 23, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .fab-print:hover { background-color: #2c1d10; transform: translateY(-2px); }

        @media print {
            .no-print, .fab-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .metric-card { border: 1px solid #ccc; background-color: white; }
            th { background-color: #3d2817 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .badge { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <button class="fab-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    <!-- Header -->
    <div class="header-container">
        <div class="brand-section">
            <h1>WellKenz</h1>
            <p>Inventory Management</p>
        </div>
        <div class="report-info">
            <h2>Stock Level Report</h2>
            <p>Generated: {{ now()->format('F d, Y • h:i A') }}</p>
            <p>User: {{ auth()->user()->name ?? 'System' }}</p>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-title">Total Items</div>
            <div class="metric-value">{{ number_format($metrics['total_items']) }}</div>
        </div>
        <div class="metric-card good">
            <div class="metric-title">Healthy Stock</div>
            <div class="metric-value">{{ number_format($metrics['healthy_stock']) }}</div>
        </div>
        <div class="metric-card low">
            <div class="metric-title">Low Stock</div>
            <div class="metric-value">{{ number_format($metrics['low_stock']) }}</div>
        </div>
        <div class="metric-card critical">
            <div class="metric-title">Critical / Out</div>
            <div class="metric-value">{{ number_format($metrics['critical_stock']) }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
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
                <tr>
                    <td>
                        <div style="font-weight: bold; color: var(--chocolate); font-size: 10pt;">{{ $data['item']->name }}</div>
                        <div style="color: var(--text-light); font-size: 8pt; margin-top: 2px;">SKU: <span style="font-family: monospace;">{{ $data['item']->item_code }}</span></div>
                    </td>
                    <td>
                        <span class="badge badge-cat">{{ $data['item']->category->name ?? 'General' }}</span>
                    </td>
                    <td class="text-right">
                        <div style="font-weight: bold; font-size: 11pt;">{{ number_format($data['current_stock'], 2) }}</div>
                        <div style="font-size: 8pt; color: var(--text-light);">{{ $data['item']->unit->symbol ?? 'units' }}</div>
                    </td>
                    <td style="padding: 0 15px;">
                        <div class="progress-track">
                            <div class="progress-fill 
                                @if($data['status'] == 'Critical') fill-critical 
                                @elseif($data['status'] == 'Low') fill-low 
                                @else fill-good @endif" 
                                style="width: {{ min(100, $data['percentage']) }}%">
                            </div>
                        </div>
                        <div class="text-center" style="font-size: 7pt; color: var(--text-light); margin-top: 2px;">
                            {{ $data['percentage'] }}% Capacity
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            @if($data['status'] == 'Critical')
                                <span class="badge badge-critical"><i class="fas fa-exclamation-circle"></i> CRITICAL</span>
                            @elseif($data['status'] == 'Low')
                                <span class="badge badge-low"><i class="fas fa-exclamation-triangle"></i> LOW</span>
                            @else
                                <span class="badge badge-good"><i class="fas fa-check-circle"></i> GOOD</span>
                            @endif
                            <span style="font-size: 8pt; color: var(--text-light);">
                                {{ $data['last_movement'] }}
                            </span>
                        </div>
                        <div style="font-size: 8pt; color: var(--text-light);">
                            Min: <strong>{{ number_format($data['min_stock_level']) }}</strong> | 
                            Reorder: <strong>{{ number_format($data['reorder_point']) }}</strong>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div>© {{ date('Y') }} WellKenz Bakery ERP System</div>
        <div>Confidential Document</div>
        <div>Page <span class="page-number"></span></div>
    </div>

    <script>
        // Auto-print logic (optional)
        // window.onload = function() { setTimeout(window.print, 500); }
    </script>
</body>
</html>