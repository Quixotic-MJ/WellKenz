<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item-Request Trends – WellKenz ERP</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#333;margin:0;padding:15px}
        h1,h2{margin:0 0 8px 0;font-weight:700}
        h1{font-size:18px}h2{font-size:14px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:6px 4px;text-align:left;border:1px solid #ccc}
        th{background:#f5f5f5;font-weight:700}
        .logo{float:right;width:120px;height:auto}
        .clear{clear:both}
        @media print{body{margin:10mm}@page{size:A4 portrait;margin:10mm}}
    </style>
</head>
<body>
    <img src="{{ public_path('assets/img/logo.png') }}" alt="Logo" class="logo">
    <h1>Item-Request Trends</h1>
    <p>Generated: {{ now()->format('d-M-Y H:i') }} | Supervisor: {{ session('emp_name') }}</p>
    <div class="clear"></div>

    <h2>Period: {{ $from }} to {{ $to }}</h2>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Requested Qty</th>
                <th>Approved</th>
                <th>Rejected</th>
                <th>Approval %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td>{{ $row->item_name }}</td>
                <td>{{ $row->total_requested }}</td>
                <td>{{ $row->approved }}</td>
                <td>{{ $row->rejected }}</td>
                <td>{{ number_format($row->approval_rate,1) }} %</td>
            </tr>
            @empty
            <tr><td colspan="5">No data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>