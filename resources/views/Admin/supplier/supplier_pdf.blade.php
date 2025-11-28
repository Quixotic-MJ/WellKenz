<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier List - Export Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #8B4513;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }
        .stats {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #8B4513;
        }
        .stats h3 {
            margin: 0 0 10px 0;
            color: #8B4513;
            font-size: 16px;
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            width: 33.33%;
        }
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #8B4513;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>WellKenz Bakery - Supplier List</h1>
        <p>Generated on: {{ date('F j, Y g:i A') }}</p>
        <p>System: WellKenz Bakery ERP</p>
    </div>

    <div class="stats">
        <h3>Export Summary</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Suppliers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $stats['active'] }}</div>
                <div class="stat-label">Active Partners</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $stats['inactive'] }}</div>
                <div class="stat-label">Inactive Partners</div>
            </div>
        </div>
    </div>

    @if($suppliers->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Supplier Code</th>
                    <th style="width: 18%;">Company Name</th>
                    <th style="width: 12%;">Contact Person</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Phone</th>
                    <th style="width: 15%;">Address</th>
                    <th style="width: 8%;">City/Province</th>
                    <th style="width: 8%;">Tax ID</th>
                    <th style="width: 6%;">Terms</th>
                    <th style="width: 6%;">Rating</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                <tr>
                    <td><strong>{{ $supplier->supplier_code }}</strong></td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>
                        @if($supplier->address)
                            {{ Str::limit($supplier->address, 25) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($supplier->city || $supplier->province)
                            {{ $supplier->city ?? '' }}{{ $supplier->city && $supplier->province ? ', ' : '' }}{{ $supplier->province ?? '' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $supplier->tax_id ?? '-' }}</td>
                    <td>
                        @if($supplier->payment_terms)
                            @if($supplier->payment_terms == 0)
                                COD
                            @else
                                Net {{ $supplier->payment_terms }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($supplier->rating)
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $supplier->rating)
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($supplier->is_active)
                            <span class="status-active">ACTIVE</span>
                        @else
                            <span class="status-inactive">INACTIVE</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No suppliers found</h3>
            <p>There are no suppliers to display in this export.</p>
        </div>
    @endif

    <div class="footer">
        <p><strong>WellKenz Bakery ERP System</strong></p>
        <p>© {{ date('Y') }} WellKenz Bakery. All rights reserved.</p>
        <p>Report generated by: {{ Auth::user()->name ?? 'System' }}</p>
    </div>
</body>
</html>