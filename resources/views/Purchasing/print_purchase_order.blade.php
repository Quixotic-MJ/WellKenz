<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PO-{{ $po->po_ref }}</title>
    <style>
        /* use DejaVu Sans for full Unicode (₱, ©, ®, etc.) */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15mm;
            font-size: 12px;
            line-height: 1.3;
        }

        .text-right {
            text-align: right
        }

        .text-center {
            text-align: center
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 4px 6px
        }

        .bordered th,
        .bordered td {
            border: 1px solid #000
        }

        .noborder th,
        .noborder td {
            border: none
        }

        .bb th,
        .bb td {
            border-bottom: 1px solid #000
        }

        .w-33 {
            width: 33.33%
        }

        .w-50 {
            width: 50%
        }

        .sig-line {
            width: 160px;
            border-bottom: 1px solid #000;
            margin: 0 auto 4px
        }

        @page {
            size: A4 portrait;
            margin: 0
        }
    </style>
</head>

<body>
    <div class="a4box">

        {{-- HEADER --}}
        <table class="noborder">
            <tr>
                <td class="text-center" style="font-size:20px;font-weight:bold">WELLKENZ CAKES AND PASTRIES</td>
            </tr>
            <tr>
                <td class="text-center" style="font-size:16px;font-weight:bold">PURCHASE ORDER</td>
            </tr>
        </table>

        {{-- TOP TWO-COLUMN BLOCK --}}
        <table class="noborder bb mt-20">
            <tr>
                <td class="w-50" valign="top">
                    <strong>PO Reference:</strong> {{ $po->po_ref }}<br>
                    <strong>Order Date:</strong> {{ $po->order_date->format('d-M-Y') }}<br>
                    <strong>Delivery Address:</strong><br>
                    {{ nl2br(e($po->delivery_address)) }}<br>
                    @if ($po->expected_delivery_date)
                        <strong>Expected Delivery:</strong> {{ $po->expected_delivery_date->format('d-M-Y') }}
                    @endif
                </td>
                <td class="w-50" valign="top">
                    <strong>Supplier:</strong><br>
                    {{ $po->supplier->sup_name }}<br>
                    {{ $po->supplier->sup_address }}<br>
                    Contact: {{ $po->supplier->contact_person }}<br>
                    Phone: {{ $po->supplier->contact_number }}<br>
                    Email: {{ $po->supplier->sup_email }}
                </td>
            </tr>
        </table>

        {{-- ITEMS TABLE --}}
        <table class="bordered mt-20">
            <thead>
                <tr>
                    <th style="width:8%">#</th>
                    <th style="width:15%">Item Code</th>
                    <th>Item Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Sub-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->items as $idx => $line)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $line->item->item_code }}</td>
                        <td>{{ $line->item->item_name }}</td>
                        <td class="text-right">{{ number_format($line->pi_quantity) }} {{ $line->item->item_unit }}
                        </td>
                        <td class="text-right">₱{{ number_format($line->pi_unit_price, 2) }}</td>
                        <td class="text-right">₱{{ number_format($line->pi_subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALS --}}
        <table class="noborder mt-10" style="margin-left:auto;width:260px">
            <tr>
                <td class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right" style="border-bottom:2px solid #000;width:120px">
                    <strong>₱{{ number_format($po->total_amount, 2) }}</strong>
                </td>
            </tr>
        </table>

        {{-- SIGNATURES --}}
        <table class="noborder mt-30" style="width:100%">
            <tr>
                <td class="w-33 text-center">
                    <div class="sig-line"></div>
                    <p style="margin-top:4px;font-size:11px">Requested
                        by<br>{{ $po->requisition->requester->name ?? '' }}</p>
                </td>
                <td class="w-33 text-center">
                    <div class="sig-line"></div>
                    <p style="margin-top:4px;font-size:11px">Approved
                        by<br>{{ $po->requisition->approver->name ?? '_________________' }}</p>
                </td>
                <td class="w-33 text-center">
                    <div class="sig-line"></div>
                    <p style="margin-top:4px;font-size:11px">Supplier Signature / Date</p>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
