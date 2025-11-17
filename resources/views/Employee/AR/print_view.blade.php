<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acknowledgement Receipt - {{ $ar->ar_ref }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #666;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        .info-item strong {
            display: block;
            color: #555;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-item span {
            font-size: 14px;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-issued {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-received {
            background: #dcfce7;
            color: #166534;
        }
        .remarks {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
        }
        .remarks strong {
            display: block;
            color: #555;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
        }
        .footer {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .signature-box {
            border-top: 1px solid #333;
            padding-top: 5px;
            text-align: center;
        }
        .signature-box p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .print-date {
            text-align: right;
            font-size: 10px;
            color: #999;
            margin-top: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Print Receipt
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>WellKenz Bakery</h1>
        <h2>Acknowledgement Receipt</h2>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <strong>Reference Number</strong>
            <span>{{ $ar->ar_ref }}</span>
        </div>
        <div class="info-item">
            <strong>Status</strong>
            <span class="status-badge status-{{ $ar->ar_status }}">{{ ucfirst($ar->ar_status) }}</span>
        </div>
        <div class="info-item">
            <strong>Issued Date</strong>
            <span>{{ $ar->issued_date->format('F d, Y') }}</span>
        </div>
        <div class="info-item">
            <strong>Requisition Reference</strong>
            {{-- ***** FIX 1: Access the relationship ***** --}}
            <span>{{ $ar->requisition->req_ref ?? '—' }}</span>
        </div>
        <div class="info-item">
            <strong>Issued By</strong>
            {{-- ***** FIX 2: Access the 'issuer' relationship ***** --}}
            <span>{{ $ar->issuer->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <strong>Issued To</strong>
            {{-- ***** FIX 3: Access the 'receiver' relationship ***** --}}
            <span>{{ $ar->receiver->name ?? '—' }}</span>
        </div>
    </div>

    <div class="remarks">
        <strong>Remarks</strong>
        {{ $ar->ar_remarks ?? 'No remarks' }}
    </div>

    @if($items->count() > 0)
    <h3 style="margin-bottom: 10px; font-size: 14px;">Items</h3>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Transaction Type</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->trans_quantity }}</td>
                <td>{{ $item->item_unit }}</td>
                <td>{{ ucfirst($item->trans_type) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->trans_date)->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #666; font-style: italic;">No items associated with this receipt.</p>
    @endif

    <div class="footer">
        <div>
            <div class="signature-box">
                <p>Issued By Signature</p>
            </div>
        </div>
        <div>
            <div class="signature-box">
                <p>Received By Signature</p>
            </div>
        </div>
    </div>

    <div class="print-date">
        Printed on: {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>