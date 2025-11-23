<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level Report - WellKenz Bakery</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .report-date {
            font-size: 12px;
            color: #666;
        }
        
        .metrics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .metric-card {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }
        
        .metric-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .metric-good { color: #22c55e; }
        .metric-low { color: #f59e0b; }
        .metric-critical { color: #ef4444; }
        .metric-total { color: #374151; }
        
        .table-container {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        th {
            background-color: #f3f4f6;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }
        
        .item-info {
            font-weight: bold;
        }
        
        .item-code {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        
        .status-good {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-low {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-critical {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .category-badge {
            background-color: #e5e7eb;
            color: #374151;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .stock-bar {
            width: 60px;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .stock-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .stock-good { background-color: #22c55e; }
        .stock-low { background-color: #f59e0b; }
        .stock-critical { background-color: #ef4444; }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #8B4513;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #654321;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print Report</button>
    
    <div class="header">
        <div class="company-name">WellKenz Bakery</div>
        <div class="report-title">Live Stock Level Report</div>
        <div class="report-date">Generated on: {{ now()->format('F d, Y \a\t h:i A') }}</div>
    </div>
    
    <div class="metrics">
        <div class="metric-card">
            <div class="metric-label">Total Items</div>
            <div class="metric-value metric-total">{{ number_format($metrics['total_items']) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Healthy Stock</div>
            <div class="metric-value metric-good">{{ number_format($metrics['healthy_stock']) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Low Stock</div>
            <div class="metric-value metric-low">{{ number_format($metrics['low_stock']) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Critical / Out</div>
            <div class="metric-value metric-critical">{{ number_format($metrics['critical_stock']) }}</div>
        </div>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Item Information</th>
                    <th style="width: 12%;">Category</th>
                    <th style="width: 15%;">Current Stock</th>
                    <th style="width: 12%;">Stock Level</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%;">Stock Thresholds</th>
                    <th style="width: 16%;">Last Movement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockData as $data)
                    <tr>
                        <td>
                            <div class="item-info">{{ $data['item']->name }}</div>
                            <div class="item-code">SKU: {{ $data['item']->item_code }}</div>
                        </td>
                        <td>
                            <span class="category-badge">{{ $data['item']->category->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold; {{ $data['status_class'] }}">{{ number_format($data['current_stock'], 1) }} {{ $data['item']->unit->symbol ?? '' }}</div>
                            <div class="stock-bar">
                                <div class="stock-bar-fill @if($data['status'] == 'Critical') stock-critical @elseif($data['status'] == 'Low') stock-low @else stock-good @endif" 
                                     style="width: {{ min(100, $data['percentage']) }}%"></div>
                            </div>
                            <div style="font-size: 8px; color: #666; margin-top: 2px;">{{ $data['percentage'] }}%</div>
                        </td>
                        <td>
                            <div style="font-size: 9px;">
                                <div>Min: {{ number_format($data['min_stock_level'], 1) }}</div>
                                <div>Reorder: {{ number_format($data['reorder_point'], 1) }}</div>
                                <div>Max: {{ number_format($data['max_stock_level'], 1) }}</div>
                            </div>
                        </td>
                        <td>
                            @if($data['status'] == 'Critical')
                                <span class="status-critical">CRITICAL</span>
                            @elseif($data['status'] == 'Low')
                                <span class="status-low">LOW</span>
                            @else
                                <span class="status-good">GOOD</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 9px;">
                                @if($data['reorder_point'] > 0)
                                    <div>Reorder Point: {{ number_format($data['reorder_point'], 1) }} {{ $data['item']->unit->symbol ?? '' }}</div>
                                @endif
                                @if($data['min_stock_level'] > 0)
                                    <div>Min Level: {{ number_format($data['min_stock_level'], 1) }} {{ $data['item']->unit->symbol ?? '' }}</div>
                                @endif
                                @if($data['max_stock_level'] > 0)
                                    <div>Max Level: {{ number_format($data['max_stock_level'], 1) }} {{ $data['item']->unit->symbol ?? '' }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 9px; color: #666;">{{ $data['last_movement'] }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p><strong>WellKenz Bakery ERP System</strong></p>
        <p>Report generated by: {{ auth()->user()->name ?? 'System' }} | Role: {{ auth()->user()->role ?? 'Unknown' }}</p>
        <p>This report contains confidential information. Please handle with care.</p>
    </div>
    
    <script>
        // Auto-print when page loads (optional - uncomment if needed)
        // window.onload = function() { window.print(); }
        
        // Print settings
        window.onbeforeprint = function() {
            console.log('Preparing to print stock report...');
        };
        
        window.onafterprint = function() {
            console.log('Print completed or cancelled.');
        };
    </script>
</body>
</html>