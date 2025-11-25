<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTV Slip - {{ $rtv->rtv_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-section {
            width: 48%;
        }
        
        .info-section h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        
        .info-value {
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table .center {
            text-align: center;
        }
        
        .items-table .right {
            text-align: right;
        }
        
        .totals-section {
            width: 300px;
            margin-left: auto;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
            padding-top: 5px;
        }
        
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                font-size: 11px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">WELLKENZ BAKERY</div>
        <div class="document-title">RETURN TO VENDOR (RTV) SLIP</div>
        <div>RTV Number: <strong>{{ $rtv->rtv_number }}</strong></div>
        <div>Date: {{ $rtv->return_date->format('F d, Y') }}</div>
    </div>

    <!-- Document Information -->
    <div class="document-info">
        <div class="info-section">
            <h4>Supplier Information</h4>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $rtv->supplier->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Code:</div>
                <div class="info-value">{{ $rtv->supplier->supplier_code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact:</div>
                <div class="info-value">{{ $rtv->supplier->contact_person ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $rtv->supplier->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">{{ $rtv->supplier->address ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="info-section">
            <h4>RTV Information</h4>
            <div class="info-row">
                <div class="info-label">RTV Number:</div>
                <div class="info-value">{{ $rtv->rtv_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Return Date:</div>
                <div class="info-value">{{ $rtv->return_date->format('F d, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Purchase Order:</div>
                <div class="info-value">{{ $rtv->purchaseOrder?->po_number ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ $rtv->status_badge['label'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Processed By:</div>
                <div class="info-value">{{ $rtv->createdBy?->name ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 35%;">Item Description</th>
                <th style="width: 10%;">SKU</th>
                <th style="width: 12%;">Quantity</th>
                <th style="width: 12%;">Unit Cost</th>
                <th style="width: 12%;">Total Cost</th>
                <th style="width: 14%;">Reason for Return</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rtv->rtvItems as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: bold;">{{ $item->item->name }}</div>
                    @if($item->item->description)
                    <div style="font-size: 10px; color: #666;">{{ $item->item->description }}</div>
                    @endif
                </td>
                <td>{{ $item->item->item_code }}</td>
                <td class="center">
                    {{ number_format($item->quantity_returned, 3) }}
                    <div style="font-size: 10px; color: #666;">{{ $item->item->unit->symbol ?? 'pcs' }}</div>
                </td>
                <td class="right">₱{{ number_format($item->unit_cost, 2) }}</td>
                <td class="right">₱{{ number_format($item->total_cost, 2) }}</td>
                <td>{{ $item->reason }}</td>
            </tr>
            @endforeach
            
            <!-- Empty rows for printing -->
            @for($i = count($rtv->rtvItems); $i < 10; $i++)
            <tr style="height: 30px;">
                <td class="center">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="center">&nbsp;</td>
                <td class="right">&nbsp;</td>
                <td class="right">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₱{{ number_format($rtv->total_value, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Tax (if applicable):</span>
            <span>₱0.00</span>
        </div>
        <div class="total-row final">
            <span>Total Value:</span>
            <span>₱{{ number_format($rtv->total_value, 2) }}</span>
        </div>
    </div>

    <!-- Notes Section -->
    @if($rtv->notes)
    <div style="margin-top: 20px;">
        <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px;">Notes</h4>
        <div style="min-height: 60px; border: 1px solid #000; padding: 10px; background-color: #f9f9f9;">
            {{ $rtv->notes }}
        </div>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div style="font-weight: bold;">Prepared By</div>
            <div style="font-size: 10px;">Name & Signature</div>
            <div style="font-size: 10px;">Date: _______________</div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div style="font-weight: bold;">Approved By</div>
            <div style="font-size: 10px;">Name & Signature</div>
            <div style="font-size: 10px;">Date: _______________</div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div style="font-weight: bold;">Received By</div>
            <div style="font-size: 10px;">Supplier Representative</div>
            <div style="font-size: 10px;">Date: _______________</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>WellKenz Bakery ERP System</div>
        <div>Generated on: {{ now()->format('F d, Y h:i A') }}</div>
        <div style="margin-top: 10px;">
            <button onclick="window.print()" class="no-print" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print RTV Slip
            </button>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
        
        // Clean up printing
        function printSlip() {
            window.print();
        }
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            // Uncomment the next line if you want to auto-close after printing
            // window.close();
        };
    </script>
</body>
</html>