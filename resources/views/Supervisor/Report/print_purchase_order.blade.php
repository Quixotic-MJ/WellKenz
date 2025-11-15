<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase-Order Summary – WellKenz ERP</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#333;margin:0;padding:15px}
        h1,h2{margin:0 0 8px 0;font-weight:700}
        h1{font-size:18px}h2{font-size:14px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:6px 4px;text-align:left;border:1px solid #ccc}
        th{background:#f5f5f5;font-weight:700}
        .summary-bar{display:flex;gap:20px;margin-bottom:15px}
        .summary-bar div{background:#f9f9f9;border:1px solid #ddd;padding:8px 12px;border-radius:4px;font-size:11px}
        .logo{float:right;width:120px;height:auto}
        .clear{clear:both}
        @media print{body{margin:10mm}@page{size:A4 portrait;margin:10mm}}
    </style>
</head>
<body>
    <img src="{{ public_path('assets/img/logo.png') }}" alt="Logo" class="logo">
    <h1>Purchase-Order Summary</h1>
    <p>Generated: {{ now()->format('d-M-Y H:i') }} | Supervisor: {{ session('emp_name') }}</p>
    <div class="clear"></div>

    <div class="summary-bar">
        <div><strong>Total POs:</strong> {{ $total }}</div>
        <div><strong>Total Value:</strong> ₱ {{ number_format($totalValue,2) }}</div>
        <div><strong>Draft:</strong> {{ $draft }}</div>
        <div><strong>Ordered:</strong> {{ $ordered }}</div>
        <div><strong>Delivered:</strong> {{ $delivered }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>PO Ref</th>
                <th>Supplier</th>
                <th>Linked Req</th>
                <th>Status</th>
                <th>Total (₱)</th>
                <th>Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $po)
            <tr>
                <td>PO-{{ $po->po_ref }}</td>
                <td>{{ $po->supplier->supplier_name ?? '-' }}</td>
                <td>{{ $po->requisition ? 'RQ-'.$po->requisition->req_ref : '-' }}</td>
                <td>{{ ucfirst($po->po_status) }}</td>
                <td>{{ number_format($po->po_total,2) }}</td>
                <td>{{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('d-M-Y') : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6">No records</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>