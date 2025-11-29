<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $purchaseOrder->po_number }}</title>
    <style>
        /* PDF-Optimized Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #3d2817;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16px;
            color: #666;
            text-transform: uppercase;
        }

        .po-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-section {
            width: 48%;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            color: #3d2817;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }

        .table-container {
            margin: 30px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }

        .total-row.final {
            border-bottom: 2px solid #333;
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding: 10px 0;
        }

        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }

        @page {
            margin: 2cm;
            size: A4;
        }
    </style>
</head>
<body>
    {{-- Company Header --}}
    <div class="header">
        <div class="company-name">WELLKENZ BAKERY</div>
        <div class="document-title">Official Purchase Order</div>
        <div style="font-size: 10px; margin-top: 10px;">Document Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
    </div>

    {{-- PO Information --}}
    <div class="po-info">
        <div class="info-section">
            <div class="section-title">Purchase Order Details</div>
            <div class="info-row">
                <span class="info-label">PO Number:</span> {{ $purchaseOrder->po_number }}
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span> {{ $purchaseOrder->created_at->format('Y-m-d') }}
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span> {{ $purchaseOrder->status }}
            </div>
            <div class="info-row">
                <span class="info-label">Created By:</span> {{ $purchaseOrder->createdBy->name ?? 'System' }}
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Delivery Information</div>
            <div class="info-row">
                <span class="info-label">Expected:</span> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : 'TBA' }}
            </div>
            <div class="info-row">
                <span class="info-label">Terms:</span> {{ $purchaseOrder->payment_terms ?? 30 }} Days
            </div>
            <div class="info-row">
                <span class="info-label">Currency:</span> PHP (Philippine Peso)
            </div>
        </div>
    </div>

    {{-- Supplier Information --}}
    <div style="margin-bottom: 30px;">
        <div class="section-title">Vendor Information</div>
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 48%;">
                <div class="info-row">
                    <strong>{{ $purchaseOrder->supplier->name }}</strong>
                </div>
                @if($purchaseOrder->supplier->contact_person)
                <div class="info-row">
                    <span class="info-label">Contact:</span> {{ $purchaseOrder->supplier->contact_person }}
                </div>
                @endif
                @if($purchaseOrder->supplier->phone)
                <div class="info-row">
                    <span class="info-label">Phone:</span> {{ $purchaseOrder->supplier->phone }}
                </div>
                @endif
                @if($purchaseOrder->supplier->email)
                <div class="info-row">
                    <span class="info-label">Email:</span> {{ $purchaseOrder->supplier->email }}
                </div>
                @endif
                @if($purchaseOrder->supplier->address)
                <div class="info-row">
                    <span class="info-label">Address:</span> {{ $purchaseOrder->supplier->address }}
                </div>
                @endif
            </div>
            <div style="width: 48%;">
                <div class="section-title" style="border: none; margin-bottom: 10px;">Ship To</div>
                <div class="info-row">
                    <strong>WellKenz Bakery HQ</strong>
                </div>
                <div class="info-row">123 Baker Street</div>
                <div class="info-row">Cebu City, Philippines 6000</div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Item Description</th>
                    <th style="width: 80px;">Code</th>
                    <th style="width: 80px;" class="text-center">Quantity</th>
                    <th style="width: 100px;" class="text-right">Unit Price</th>
                    <th style="width: 100px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->purchaseOrderItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->item->name }}</strong>
                        @if($item->notes)
                            <br><em style="font-size: 10px;">{{ $item->notes }}</em>
                        @endif
                    </td>
                    <td>{{ $item->item->item_code }}</td>
                    <td class="text-center">
                        {{ number_format($item->quantity_ordered, 2) }}
                        <br><span style="font-size: 10px;">{{ $item->item->unit->symbol ?? 'pcs' }}</span>
                    </td>
                    <td class="text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right"><strong>₱{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
        </div>
        
        @if($purchaseOrder->discount_amount > 0)
        <div class="total-row">
            <span>Discount:</span>
            <span>-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
        </div>
        @endif

        @if($purchaseOrder->tax_amount > 0)
        <div class="total-row">
            <span>Tax:</span>
            <span>₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
        </div>
        @endif

        <div class="total-row final">
            <span>TOTAL AMOUNT:</span>
            <span>₱{{ number_format($purchaseOrder->grand_total, 2) }}</span>
        </div>
    </div>

    {{-- Notes --}}
    @if($purchaseOrder->notes)
    <div style="margin-bottom: 30px;">
        <div class="section-title">Special Instructions</div>
        <div style="border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">
            {{ $purchaseOrder->notes }}
        </div>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Prepared By</div>
            <div style="font-size: 10px; color: #666;">{{ $purchaseOrder->createdBy->name ?? 'System Generated' }}</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Approved By</div>
            <div style="font-size: 10px; color: #666;">{{ $purchaseOrder->approvedBy->name ?? 'Manager' }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div><strong>WellKenz Bakery Enterprise Resource Planning System</strong></div>
        <div>Document ID: PO-{{ $purchaseOrder->id }} | Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
    </div>
</body>
</html>