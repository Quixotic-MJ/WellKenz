<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requisition Summary – WellKenz ERP</title>
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
    <h1>Requisition Summary</h1>
    <p>Generated: {{ now()->format('d-M-Y H:i') }} | Supervisor: {{ session('emp_name') }}</p>
    <div class="clear"></div>

    <div class="summary-bar">
        <div><strong>Total:</strong> {{ $total }}</div>
        <div><strong>Pending:</strong> {{ $pending }}</div>
        <div><strong>Approved:</strong> {{ $approved }}</div>
        <div><strong>Rejected:</strong> {{ $rejected }}</div>
    </div>

    <h2>Break-down</h2>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Requested By</th>
                <th>Purpose</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Requested</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            <tr>
                <td>RQ-{{ $r->req_ref }}</td>
                <td>{{ $r->requester->name ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($r->req_purpose,40) }}</td>
                <td>{{ ucfirst($r->req_priority) }}</td>
                <td>{{ ucfirst($r->req_status) }}</td>
                <td>{{ $r->created_at->format('d-M-Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6">No records</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>