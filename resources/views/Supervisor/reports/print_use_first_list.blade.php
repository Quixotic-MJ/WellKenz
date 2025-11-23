<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Use First List - {{ date('M j, Y') }}</title>
    
    {{-- Font Awesome for Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #D2691E; /* Chocolate */
            --primary-dark: #8B4513;
            --danger: #dc2626;
            --warning: #d97706;
            --success: #16a34a;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: var(--gray-900);
            background-color: #f9fafb; /* Light gray background for screen */
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page-container {
            max-width: 8.5in;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 8px;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info h1 {
            margin: 0;
            color: var(--primary);
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-info p {
            margin: 5px 0 0;
            font-size: 18px;
            color: var(--gray-700);
            font-weight: 600;
        }

        .report-meta {
            text-align: right;
            font-size: 14px;
            color: #666;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card.critical { background-color: #fef2f2; border-color: #fee2e2; }
        .card.warning { background-color: #fffbeb; border-color: #fef3c7; }
        .card.total { background-color: #f0fdf4; border-color: #dcfce7; }

        .card-number {
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }

        .card-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .text-danger { color: var(--danger); }
        .text-warning { color: var(--warning); }
        .text-success { color: var(--success); }

        /* Instructions Box */
        .alert-box {
            background-color: #fff7ed; /* Orange tint */
            border-left: 5px solid var(--primary);
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .alert-title {
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .alert-content ul {
            margin: 0;
            padding-left: 25px;
            color: var(--gray-700);
            font-size: 14px;
        }

        .alert-content li {
            margin-bottom: 4px;
        }

        /* Data Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background-color: var(--gray-100);
            color: var(--gray-700);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            text-align: left;
            border-bottom: 2px solid var(--gray-200);
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        tr.row-expired { background-color: #fef2f2; }
        tr.row-critical { background-color: #fff7ed; }
        
        /* Item Info Styling */
        .item-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 14px;
            display: block;
        }
        
        .item-meta {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .badge-expired { background-color: var(--danger); color: white; }
        .badge-critical { background-color: #ef4444; color: white; }
        .badge-warning { background-color: var(--warning); color: white; }
        .badge-normal { background-color: var(--success); color: white; }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            page-break-inside: avoid;
        }

        .sig-block {
            width: 200px;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid black;
            margin-bottom: 8px;
            height: 30px;
        }

        .sig-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* No Print Elements */
        .no-print-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }

        .btn:hover { transform: translateY(-2px); }

        .btn-print { background-color: var(--primary); color: white; }
        .btn-close { background-color: var(--gray-200); color: var(--gray-700); }

        /* Print Media Queries */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            
            .page-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
                max-width: 100%;
            }

            .no-print-bar, .no-print {
                display: none !important;
            }

            /* Ensure background colors print */
            .card.critical, .card.warning, .card.total, 
            .alert-box, .badge, 
            tr.row-expired, tr.row-critical {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="page-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>WellKenz Bakery</h1>
                <p><i class="fas fa-clipboard-list" style="font-size: 0.8em; margin-right: 8px;"></i> Use First List</p>
            </div>
            <div class="report-meta">
                <div><strong>Generated:</strong> {{ date('F j, Y') }}</div>
                <div><strong>Time:</strong> {{ date('g:i A') }}</div>
                <div style="margin-top: 5px;"><span class="badge" style="background:#eee; color:#333; border:1px solid #ddd;">Priority Items Only</span></div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="card critical">
                <div class="card-number text-danger">{{ $criticalCount }}</div>
                <div class="card-label">Critical Items</div>
                <div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Expires  48hrs</div>
                <i class="fas fa-exclamation-circle" style="position: absolute; top: 10px; right: 10px; color: #fee2e2; font-size: 40px;"></i>
            </div>
            <div class="card warning">
                <div class="card-number text-warning">{{ $warningCount }}</div>
                <div class="card-label">Warning Items</div>
                <div style="font-size: 11px; color: #d97706; margin-top: 4px;">Expires  7 days</div>
                <i class="fas fa-clock" style="position: absolute; top: 10px; right: 10px; color: #fef3c7; font-size: 40px;"></i>
            </div>
            <div class="card total">
                <div class="card-number text-success">{{ $totalCount }}</div>
                <div class="card-label">Total Batches</div>
                <div style="font-size: 11px; color: #16a34a; margin-top: 4px;">Value: ₱{{ number_format($totalValue, 2) }}</div>
                <i class="fas fa-boxes" style="position: absolute; top: 10px; right: 10px; color: #dcfce7; font-size: 40px;"></i>
            </div>
        </div>

        <!-- Instructions -->
        <div class="alert-box">
            <div class="alert-title">
                <i class="fas fa-hard-hat"></i> ACTION REQUIRED
            </div>
            <div class="alert-content">
                <ul>
                    <li><strong>CRITICAL (Red):</strong> Prioritize these items immediately. Use within 24-48 hours.</li>
                    <li><strong>WARNING (Orange):</strong> Plan production to use these items within the week.</li>
                    <li><strong>EXPIRED:</strong> Do not use. Segregate and report to Inventory Supervisor for write-off.</li>
                </ul>
            </div>
        </div>

        <!-- Table -->
        @if($batches->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Item Details</th>
                            <th style="width: 15%;">Expiry Date</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th style="width: 15%; text-align: right;">Stock Qty</th>
                            <th style="width: 15%; text-align: right;">Unit Cost</th>
                            <th style="width: 20%;">Supplier</th>
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
                                $badgeClass = 'badge-normal';
                                $statusText = 'NORMAL';
                                
                                if ($isPastExpiry) {
                                    $rowClass = 'row-expired';
                                    $badgeClass = 'badge-expired';
                                    $statusText = 'EXPIRED';
                                } elseif ($daysUntilExpiry <= 1) {
                                    $rowClass = 'row-critical';
                                    $badgeClass = 'badge-critical';
                                    $statusText = $daysUntilExpiry <= 0 ? 'TODAY' : '1 DAY LEFT';
                                } elseif ($daysUntilExpiry <= 7) {
                                    $badgeClass = 'badge-warning';
                                    $statusText = round($daysUntilExpiry) . ' DAYS';
                                }
                            @endphp
                            
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <span class="item-name">{{ $batch->item->name ?? 'Unknown Item' }}</span>
                                    <div class="item-meta">
                                        Code: <strong>{{ $batch->item->item_code ?? 'N/A' }}</strong> | 
                                        Batch: {{ $batch->batch_number }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--gray-900);">
                                        {{ $expiryDate->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 10px; color: #666;">
                                        {{ $expiryDate->format('D') }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 700; font-size: 14px;">{{ number_format($batch->quantity, 2) }}</div>
                                    <div style="font-size: 10px; color: #666;">{{ $batch->item->unit->symbol ?? 'units' }}</div>
                                </td>
                                <td style="text-align: right;">
                                    <div>₱{{ number_format($batch->unit_cost, 2) }}</div>
                                    <div style="font-size: 10px; color: #666;">Total: ₱{{ number_format($batch->quantity * $batch->unit_cost, 2) }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: var(--gray-700);">
                                        {{ Str::limit($batch->supplier->name ?? 'Unknown Supplier', 20) }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px; color: var(--gray-700); background: var(--gray-100); border-radius: 8px;">
                <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 15px;"></i>
                <h3>All Clear!</h3>
                <p>No priority expiry items found. All stock is fresh.</p>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Inventory Supervisor</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Head Baker</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Date Verified</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>WellKenz Bakery ERP System | Generated by {{ auth()->user()->name ?? 'System' }}</p>
        </div>
    </div>

    <!-- Floating Action Bar (Screen Only) -->
    <div class="no-print-bar">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print List
        </button>
        <button onclick="window.close()" class="btn btn-close">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <script>
        window.onload = function() {
            // Optional: auto-print check could go here
        }
    </script>
</body>
</html>