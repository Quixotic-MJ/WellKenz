<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Health – WellKenz ERP</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#333;margin:0;padding:15px}
        h1,h2{margin:0 0 8px 0;font-weight:700}
        h1{font-size:18px}h2{font-size:14px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:6px 4px;text-align:left;border:1px solid #ccc}
        th{background:#f5f5f5;font-weight:700}
        .alert-box{background:#fff3cd;border:1px solid #ffeaa7;padding:8px;margin-bottom:10px;border-radius:4px;font-size:11px}
        .logo{float:right;width:120px;height:auto}
        .clear{clear:both}
        @media print{body{margin:10mm}@page{size:A4 portrait;margin:10mm}}
    </style>
</head>
<body>
    <img src="{{ public_path('assets/img/logo.png') }}" alt="Logo" class="logo">
    <h1>Inventory Health Report</h1>
    <p>Generated: {{ now()->format('d-M-Y H:i') }} | Supervisor: {{ session('emp_name') }}</p>
    <div class="clear"></div>

    @if(count($lowStock) > 0)
    <div class="alert-box">
        <strong>Low-Stock Alert:</strong> {{ count($lowStock) }} items at or below reorder level.
    </div>
    @endif

    @if(count($expiry) > 0)
    <div class="alert-box">
        <strong>Expiry Alert:</strong> {{ count($expiry) }} items expiring within 30 days.
    </div>
    @endif

    <h2>Low-Stock Items</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Current Stock</th>
                <th>Reorder Level</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStock as $l)
            <tr>
                <td>{{ $l->item_code ?? '-' }}</td>
                <td>{{ $l->item_name }}</td>
                <td>{{ $l->item_stock }}</td>
                <td>{{ $l->reorder_level }}</td>
                <td>{{ $l->item_unit }}</td>
            </tr>
            @empty
            <tr><td colspan="5">No low-stock items</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Near-Expiry Items (≤ 30 days)</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Expiry Date</th>
                <th>Stock</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expiry as $e)
            <tr>
                <td>{{ $e->item_code ?? '-' }}</td>
                <td>{{ $e->item_name }}</td>
                <td>{{ \Carbon\Carbon::parse($e->item_expire_date)->format('d-M-Y') }}</td>
                <td>{{ $e->item_stock }}</td>
                <td>{{ $e->item_unit }}</td>
            </tr>
            @empty
            <tr><td colspan="5">No expiring items</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>