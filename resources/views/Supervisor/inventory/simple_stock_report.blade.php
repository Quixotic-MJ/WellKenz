<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level Report - WellKenz Bakery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18pt;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .metrics {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .metric-row {
            display: table-row;
        }

        .metric-cell {
            display: table-cell;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .metric-value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }

        .metric-label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 9pt;
        }

        th {
            background-color: #000;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .status-good { background-color: #d1fae5; color: #065f46; }
        .status-low { background-color: #fef3c7; color: #92400e; }
        .status-critical { background-color: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>WellKenz Bakery</h1>
        <p>Stock Level Report</p>
        <p>Generated: {{ now()->format('F d, Y • h:i A') }}</p>
    </div>

    <div class="metrics">
        <div class="metric-row">
            <div class="metric-cell">
                <div class="metric-value">{{ number_format($metrics['total_items']) }}</div>
                <div class="metric-label">Total Items</div>
            </div>
            <div class="metric-cell">
                <div class="metric-value">{{ number_format($metrics['healthy_stock']) }}</div>
                <div class="metric-label">Healthy Stock</div>
            </div>
            <div class="metric-cell">
                <div class="metric-value">{{ number_format($metrics['low_stock']) }}</div>
                <div class="metric-label">Low Stock</div>
            </div>
            <div class="metric-cell">
                <div class="metric-value">{{ number_format($metrics['critical_stock']) }}</div>
                <div class="metric-label">Critical / Out</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Item Name</th>
                <th style="width: 15%;">Code</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 12%; text-align: right;">Stock</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 23%;">Last Movement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockData as $data)
                <tr>
                    <td>{{ $data['item']->name }}</td>
                    <td>{{ $data['item']->item_code }}</td>
                    <td>{{ $data['item']->category->name ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ number_format($data['current_stock'], 2) }}</td>
                    <td style="text-align: center;">
                        <span class="status-{{ strtolower(str_replace(' ', '-', $data['status'])) }}">
                            {{ $data['status'] }}
                        </span>
                    </td>
                    <td>{{ $data['last_movement'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} WellKenz Bakery ERP System • Confidential Document</p>
        <p>Total Items: {{ $stockData->count() }}</p>
    </div>
</body>
</html>