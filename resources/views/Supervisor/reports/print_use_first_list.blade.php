<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Use First List - {{ date('M j, Y') }}</title>
    
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
            
            /* Alert Colors */
            --critical-bg: #fee2e2;
            --critical-text: #991b1b;
            --warning-bg: #fef3c7;
            --warning-text: #92400e;
            --good-bg: #d1fae5;
            --good-text: #065f46;
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
            background-color: white; /* Better for print preview than cream */
            margin: 0;
            padding: 20px;
        }

        /* Typography */
        h1, h2, h3 { font-family: 'Playfair Display', serif; color: var(--chocolate); margin: 0; }
        
        /* Utilities */
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
            border-bottom: 3px solid var(--chocolate);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-section h1 { font-size: 28pt; line-height: 1; font-weight: 700; }
        .brand-section p { color: var(--caramel); font-weight: 700; font-size: 9pt; letter-spacing: 3px; margin-top: 5px; text-transform: uppercase; }

        .report-info { text-align: right; }
        .report-info h2 { font-size: 18pt; margin-bottom: 5px; font-weight: 600; }
        .report-info p { font-size: 9pt; color: var(--text-light); }
        .report-badge { display: inline-block; background: var(--cream); border: 1px solid var(--border); padding: 4px 8px; font-size: 8pt; font-weight: 600; border-radius: 4px; color: var(--chocolate); margin-top: 5px; }

        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background-color: white;
            position: relative;
            overflow: hidden;
        }

        .card.critical { background-color: var(--critical-bg); border-color: #fecaca; }
        .card.warning { background-color: var(--warning-bg); border-color: #fde68a; }
        .card.total { background-color: var(--cream); border-color: var(--border); }

        .card-number {
            font-size: 28pt;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
            font-family: 'Playfair Display', serif;
        }

        .card.critical .card-number { color: var(--critical-text); }
        .card.warning .card-number { color: var(--warning-text); }
        .card.total .card-number { color: var(--chocolate); }

        .card-label {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-sub { font-size: 8pt; opacity: 0.8; margin-top: 5px; font-weight: 500; }
        .card-icon { position: absolute; top: 10px; right: 10px; font-size: 24pt; opacity: 0.1; }

        /* Instructions Box */
        .alert-box {
            background-color: #fff7ed;
            border-left: 4px solid var(--caramel);
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
            font-size: 9pt;
        }

        .alert-title {
            font-weight: 700;
            color: var(--chocolate);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .alert-content ul { margin: 0; padding-left: 20px; color: var(--text-main); }
        .alert-content li { margin-bottom: 2px; }

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
            letter-spacing: 1px;
            padding: 12px 10px;
            text-align: left;
        }

        td {
            border-bottom: 1px solid var(--border);
            padding: 10px;
            font-size: 9pt;
            vertical-align: middle;
        }

        tr.row-expired td { background-color: #fff1f2; color: #9f1239; }
        tr.row-critical td { background-color: #fff7ed; color: #9a3412; }

        /* Data Styling */
        .item-name { font-weight: 700; color: var(--chocolate); font-size: 10pt; display: block; }
        .item-meta { font-size: 8pt; color: var(--text-light); margin-top: 2px; font-family: monospace; }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-cat { background: var(--gray-100); color: var(--gray-700); border: 1px solid var(--gray-200); }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 8pt;
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Floating Print Button (Screen Only) */
        .fab-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--chocolate);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(61, 40, 23, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            z-index: 100;
            transition: transform 0.2s, background-color 0.2s;
        }
        .fab-print:hover { background-color: #2c1d10; transform: translateY(-2px); }

        @media print {
            .no-print, .fab-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .page-container { box-shadow: none; padding: 0; max-width: 100%; }
            th { background-color: #3d2817 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .card.critical, .card.warning { -webkit-print-color-adjust: exact; }
            tr.row-expired, tr.row-critical { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <button class="fab-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    <div class="page-container">
        <!-- Header -->
        <div class="header-container">
            <div class="brand-section">
                <h1>WellKenz</h1>
                <p>Bakery Inventory</p>
            </div>
            <div class="report-info">
                <h2>Use First List</h2>
                <p>Generated: {{ now()->format('F d, Y • h:i A') }}</p>
                <span class="report-badge">PRIORITY ACTION REQUIRED</span>
            </div>
        </div>

     

        <!-- Instructions -->
        <div class="alert-box">
            <div class="alert-title">
                <i class="fas fa-clipboard-check"></i> Baker Instructions
            </div>
            <div class="alert-content">
                <ul>
                    <li><strong>RED HIGHLIGHTS:</strong> Must be used in today's production or transferred to staff meals immediately.</li>
                    <li><strong>ORANGE HIGHLIGHTS:</strong> Schedule these items for production within the current week.</li>
                    <li><strong>FIFO POLICY:</strong> Always verify batch numbers matches the physical item before use.</li>
                </ul>
            </div>
        </div>

        <!-- Table -->
        @if($batches->count() > 0)
            <table>
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
                            $statusStyle = 'background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;'; // Good (Green)

                            if ($isPastExpiry) {
                                $rowClass = 'row-expired';
                                $statusText = 'EXPIRED';
                                $statusStyle = 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;'; // Critical (Red)
                            } elseif ($daysUntilExpiry <= 1) {
                                $rowClass = 'row-critical';
                                $statusText = 'CRITICAL';
                                $statusStyle = 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;'; // Critical (Red)
                            } elseif ($daysUntilExpiry <= 7) {
                                $statusText = 'WARNING';
                                $statusStyle = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;'; // Warning (Orange)
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                <span class="item-name">{{ $batch->item->name }}</span>
                                <div class="item-meta">SKU: {{ $batch->item->item_code }} | Batch: {{ $batch->batch_number }}</div>
                            </td>
                            <td>
                                <span class="badge badge-cat">{{ $batch->item->category->name ?? 'General' }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $expiryDate->format('M d, Y') }}</div>
                                <div style="font-size: 8pt; color: var(--text-light);">{{ $expiryDate->format('l') }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="{{ $statusStyle }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-weight: 700; font-size: 11pt;">{{ number_format($batch->quantity, 2) }}</div>
                                <div style="font-size: 8pt; color: var(--text-light);">{{ $batch->item->unit->symbol ?? 'units' }}</div>
                            </td>
                            <td style="font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($batch->supplier->name ?? 'Unknown', 20) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; border: 2px dashed var(--border); border-radius: 8px; color: var(--text-light);">
                <i class="fas fa-check-circle" style="font-size: 32px; color: var(--success); margin-bottom: 10px;"></i>
                <p style="margin: 0; font-weight: 600;">No priority items found.</p>
                <p style="margin: 5px 0 0; font-size: 9pt;">All inventory batches are fresh.</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>© {{ date('Y') }} WellKenz Bakery ERP System</div>
            <div style="text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Internal Use Only</div>
            <div>User: {{ auth()->user()->name ?? 'System' }}</div>
        </div>
    </div>

    <script>
        // Auto-print prompt when loaded
        // window.onload = function() { setTimeout(window.print, 500); }
    </script>
</body>
</html>