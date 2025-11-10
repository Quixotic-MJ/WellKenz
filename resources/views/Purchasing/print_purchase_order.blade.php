<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print PO - {{ $po->po_ref }}</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:40px;color:#333}
        table{width:100%;border-collapse:collapse;margin-top:15px}
        th,td{padding:8px 10px;border:1px solid #ccc;text-align:left}
        th{background:#f5f5f5}
        .right{text-align:right}
        .top-info{display:flex;justify-content:space-between;margin-bottom:30px}
        .totals{margin-top:20px;width:320px;margin-left:auto}
        .totals td{border:none;padding:4px 10px}
        .totals tr:last-child{font-weight:bold;font-size:1.1em;border-top:2px solid #000}
        @media print{body{margin:10px} .no-print{display:none}}
    </style>
</head>
<body>

<h2>PURCHASE ORDER</h2>

<div class="top-info">
    <div>
        <strong>PO Ref:</strong> {{ $po->po_ref }} <br>
        <strong>Order date:</strong> {{ $po->order_date->format('d-M-Y') }} <br>
        <strong>Delivery address:</strong> <br>
        {{ nl2br(e($po->delivery_address)) }}
        @if($po->expected_delivery_date)
            <br><strong>Expected delivery:</strong> {{ $po->expected_delivery_date->format('d-M-Y') }}
        @endif
    </div>
    <div>
        <strong>Supplier</strong><br>
        {{ $po->supplier->sup_name }}<br>
        {{ $po->supplier->sup_address }}<br>
        Contact: {{ $po->supplier->contact_person }} /
        {{ $po->supplier->contact_number }}<br>
        Email: {{ $po->supplier->sup_email }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Item code</th>
            <th>Description</th>
            <th class="right">Quantity</th>
            <th class="right">Unit price</th>
            <th class="right">Sub-total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($po->items as $idx => $line)
        <tr>
            <td>{{ $idx+1 }}</td>
            <td>{{ $line->item->item_code }}</td>
            <td>{{ $line->item->item_name }}</td>
            <td class="right">{{ number_format($line->pi_quantity) }} {{ $line->item->item_unit }}</td>
            <td class="right">₱{{ number_format($line->pi_unit_price,2) }}</td>
            <td class="right">₱{{ number_format($line->pi_subtotal,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td colspan="5" class="right">Total amount:</td>
        <td class="right">₱{{ number_format($po->total_amount,2) }}</td>
    </tr>
</table>

<div style="margin-top:50px">
    <p>Prepared by: ____________________</p>
    <p>Approved by: ____________________</p>
</div>

<button class="no-print" onclick="window.print()">Print</button>

</body>
</html>

