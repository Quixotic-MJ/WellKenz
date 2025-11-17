<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order PO-{{ $purchaseOrder->po_ref }}</title>
    <style>
        /* Basic reset and print-friendly styles */
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 0; }
        .container { width: 90%; margin: 20px auto; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18pt; margin: 0; }
        .header p { font-size: 10pt; margin: 2px 0; }
        .details-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .details-grid div { width: 48%; }
        .details-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
        .details-box h3 { font-size: 12pt; margin-top: 0; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .label { font-weight: bold; display: block; margin-top: 5px; color: #555; font-size: 9pt; }
        .value { margin-bottom: 10px; font-size: 10pt; }

        /* Table Styles */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; font-size: 9pt; text-transform: uppercase; }
        .items-table td { font-size: 10pt; }
        .text-right { text-align: right !important; }
        .total-row td { font-weight: bold; background-color: #e0f7fa; font-size: 11pt; }

        /* Footer/Signatures */
        .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
        .signatures div { width: 30%; text-align: center; border-top: 1px solid #333; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PURCHASE ORDER</h1>
            <p>WellKenz ERP | [Your Company Address/Contact]</p>
            <h2>PO-{{ $purchaseOrder->po_ref }}</h2>
        </div>

        <div class="details-grid">
            <div class="details-box">
                <h3>Supplier Details (Ship To)</h3>
                <span class="label">Supplier:</span>
                <span class="value">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</span>
                <span class="label">Address:</span>
                <span class="value">{{ $purchaseOrder->supplier->address ?? 'N/A' }}</span>
                <span class="label">Contact:</span>
                <span class="value">{{ $purchaseOrder->supplier->contact_person ?? 'N/A' }}</span>
            </div>
            <div class="details-box">
                <h3>Order Information</h3>
                <span class="label">Date Created:</span>
                <span class="value">{{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('M d, Y') }}</span>
                <span class="label">Expected Delivery Date:</span>
                <span class="value">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : '-' }}</span>
                <span class="label">Payment Terms:</span>
                <span class="value">{{ $purchaseOrder->payment_terms ?? 'N/A' }}</span>
            </div>
        </div>

        @if(!empty($requestorNames))
        <div class="details-box">
            <h3>Requesters</h3>
            <p class="value">{{ $requestorNames }}</p>
        </div>
        @elseif(!empty($purchaseOrder->notes) && \Illuminate\Support\Str::startsWith($purchaseOrder->notes, 'Requesters: '))
        <div class="details-box">
            <h3>Requesters</h3>
            <p class="value">{{ \Illuminate\Support\Str::after($purchaseOrder->notes, 'Requesters: ') }}</p>
        </div>
        @endif

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item Description</th>
                    <th class="text-right" style="width: 10%;">Unit</th>
                    <th class="text-right" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 15%;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach ($purchaseOrder->items as $item)
                    @php $grandTotal += $item->lineTotal; @endphp
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-right">{{ $item->unit }}</td>
                        <td class="text-right">{{ number_format($item->quantity) }}</td>
                        <td class="text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">₱{{ number_format($item->lineTotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right">GRAND TOTAL</td>
                    <td class="text-right">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="details-box" style="margin-top: 20px;">
            <h3>Notes & Terms</h3>
            <p style="font-size: 9pt;">{{ $purchaseOrder->notes ?? 'Please deliver goods on or before the expected delivery date.' }}</p>
        </div>

        <div class="signatures">
            <div>
                <br>
                <span>Prepared By: ({{ $purchaseOrder->creator->name ?? 'N/A' }})</span>
            </div>
            <div>
                <br>
                <span>Approved By: ({{ $purchaseOrder->approver->name ?? 'N/A' }})</span>
            </div>
            <div>
                <br>
                <span>Received/Accepted By: (Supplier)</span>
            </div>
        </div>
    </div>
</body>
</html>