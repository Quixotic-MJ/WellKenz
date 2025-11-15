<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requisition - {{ $req->req_ref }} – WellKenz ERP</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#333;margin:0;padding:15px}
        h1,h2{margin:0 0 8px 0;font-weight:700}
        h1{font-size:18px}h2{font-size:14px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:6px 4px;text-align:left;border:1px solid #ccc}
        th{background:#f5f5f5;font-weight:700}
        .info{display:flex;gap:20px;margin-bottom:15px}
        .info div{background:#f9f9f9;border:1px solid #ddd;padding:8px 12px;border-radius:4px;font-size:11px}
        .logo{float:right;width:120px;height:auto}
        .clear{clear:both}
        @media print{body{margin:10mm}@page{size:A4 portrait;margin:10mm}}
    </style>
</head>
<body>
    <h1>Requisition Details</h1>
    <p>Reference: RQ-{{ $req->req_ref }} | Generated: {{ now()->format('d-M-Y H:i') }}</p>
    <div class="clear"></div>

    <div class="info">
        <div><strong>Status:</strong> {{ ucfirst($req->req_status) }}</div>
        <div><strong>Priority:</strong> {{ ucfirst($req->req_priority) }}</div>
        <div><strong>Requested:</strong> {{ $req->created_at->format('d-M-Y') }}</div>
    </div>

    <div style="margin-bottom:15px;">
        <strong>Purpose:</strong><br>
        {{ $req->req_purpose }}
    </div>

    <h2>Requested Items</h2>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->req_item_quantity }}</td>
                <td>{{ $item->item_unit }}</td>
            </tr>
            @empty
            <tr><td colspan="3">No items</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($req->req_reject_reason)
    <div style="margin-top:15px;">
        <strong>Rejection Reason:</strong><br>
        {{ $req->req_reject_reason }}
    </div>
    @endif
</body>
</html>
