<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 20px;
        }
        
        .company-info {
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 12px;
            color: #666;
        }
        
        .po-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
        }
        
        .po-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        
        .detail-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            color: #8B4513;
        }
        
        .detail-item {
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #8B4513;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .totals-section {
            margin-left: auto;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
        }
        
        .total-row.final {
            border-top: 2px solid #8B4513;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .notes-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            color: #8B4513;
        }
        
        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin: 30px 0 10px 0;
        }
        
        .signature-label {
            font-size: 12px;
            font-weight: bold;
        }
        
        .signature-title {
            font-size: 11px;
            color: #666;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #8B4513;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background-color: #A0522D;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-confirmed { background-color: #fef3c7; color: #92400e; }
        .status-partial { background-color: #fed7aa; color: #c2410c; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print PO
    </button>

    <div class="header">
        <div class="company-info">
            <div class="company-name">WellKenz Bakery</div>
            <div class="company-details">
                Purchase Order<br>
                Generated on: {{ now()->format('M d, Y H:i:s') }}
            </div>
        </div>
        
        <div class="po-title">
            PURCHASE ORDER
            <span class="status-badge status-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span>
        </div>
    </div>

    <div class="po-details">
        <div class="detail-section">
            <h3>PURCHASE ORDER DETAILS</h3>
            <div class="detail-item">
                <span class="detail-label">PO Number:</span>
                <strong>{{ $purchaseOrder->po_number }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Order Date:</span>
                {{ $purchaseOrder->order_date->format('M d, Y') }}
            </div>
            <div class="detail-item">
                <span class="detail-label">Expected Delivery:</span>
                {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('M d, Y') : 'Not specified' }}
            </div>
            <div class="detail-item">
                <span class="detail-label">Payment Terms:</span>
                {{ $purchaseOrder->payment_terms ?? 30 }} days
            </div>
            @if($purchaseOrder->sourcePurchaseRequests->count() > 0)
                <div class="detail-item">
                    <span class="detail-label">Source PR:</span>
                    @foreach($purchaseOrder->sourcePurchaseRequests as $sourcePR)
                        {{ $sourcePR->pr_number }}@if(!$loop->last), @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="detail-section">
            <h3>SUPPLIER INFORMATION</h3>
            <div class="detail-item">
                <strong>{{ $purchaseOrder->supplier->name }}</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Code:</span>
                {{ $purchaseOrder->supplier->supplier_code }}
            </div>
            @if($purchaseOrder->supplier->contact_person)
                <div class="detail-item">
                    <span class="detail-label">Contact:</span>
                    {{ $purchaseOrder->supplier->contact_person }}
                </div>
            @endif
            @if($purchaseOrder->supplier->phone)
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    {{ $purchaseOrder->supplier->phone }}
                </div>
            @endif
            @if($purchaseOrder->supplier->email)
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    {{ $purchaseOrder->supplier->email }}
                </div>
            @endif
            @if($purchaseOrder->supplier->address)
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    {{ $purchaseOrder->supplier->address }}
                </div>
            @endif
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40%;">Item Description</th>
                <th style="width: 15%;">Item Code</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->purchaseOrderItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item->name }}</strong>
                        @if($item->item->category)
                            <br><small>{{ $item->item->category->name }}</small>
                        @endif
                        @if($item->notes)
                            <br><small>Notes: {{ $item->notes }}</small>
                        @endif
                    </td>
                    <td>{{ $item->item->item_code }}</td>
                    <td class="text-center">
                        {{ number_format($item->quantity_ordered, 3) }} 
                        <br><small>{{ $item->item->unit->symbol ?? 'pcs' }}</small>
                    </td>
                    <td class="text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right"><strong>₱{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
        </div>
        
        @if($purchaseOrder->tax_amount > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
            </div>
        @endif
        
        @if($purchaseOrder->discount_amount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span>-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
            </div>
        @endif
        
        <div class="total-row final">
            <span>TOTAL AMOUNT:</span>
            <span>₱{{ number_format($purchaseOrder->grand_total, 2) }}</span>
        </div>
    </div>

    @if($purchaseOrder->notes)
        <div class="notes-section">
            <h3>NOTES & SPECIAL INSTRUCTIONS</h3>
            <p style="margin: 0; font-size: 12px;">{{ $purchaseOrder->notes }}</p>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Prepared By</div>
            <div class="signature-title">{{ $purchaseOrder->createdBy->name ?? 'N/A' }}</div>
            <div class="signature-title">Date: {{ $purchaseOrder->created_at->format('M d, Y') }}</div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Approved By</div>
            <div class="signature-title">
                @if($purchaseOrder->approvedBy)
                    {{ $purchaseOrder->approvedBy->name }}
                    <br>Date: {{ $purchaseOrder->approved_at ? $purchaseOrder->approved_at->format('M d, Y') : 'Pending' }}
                @else
                    Pending Approval
                @endif
            </div>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog when page loads (optional)
        // window.onload = function() { window.print(); }
        
        // Clean up after printing
        window.onafterprint = function() {
            // You can add cleanup code here if needed
        };
    </script>
</body>
</html>