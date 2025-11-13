<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? str_replace('-',' ',ucfirst($report)) }}</title>
    <style>
        body{font-family:'DejaVu Sans', sans-serif; margin:0; padding:12mm; font-size:12px; line-height:1.3; color:#222}
        h1,h2,h3{margin:0}
        .text-right{text-align:right}
        .text-center{text-align:center}
        .muted{color:#666}
        table{width:100%; border-collapse:collapse}
        th,td{padding:6px 8px}
        .bordered th,.bordered td{border:1px solid #000}
        .bb th,.bb td{border-bottom:1px solid #000}
        .mt-4{margin-top:12px}
        .mt-8{margin-top:20px}
        .mt-12{margin-top:28px}
        @page{size:A4 landscape; margin:0}
    </style>
</head>
<body>
    <table class="bb" style="width:100%">
        <tr>
            <td class="text-center" style="font-size:18px;font-weight:bold">WELLKENZ CAKES AND PASTRIES</td>
        </tr>
        <tr>
            <td class="text-center" style="font-size:16px;font-weight:bold">{{ strtoupper($title ?? str_replace('-',' ',ucfirst($report))) }}</td>
        </tr>
        <tr>
            <td class="text-center muted" style="font-size:12px">Period: {{ $start ?? '' }} — {{ $end ?? '' }} | Generated: {{ now()->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    @php
        $isComposite = is_array($data ?? null);
    @endphp

    @if(!$isComposite)
        @php $rows = collect($data ?? []); @endphp
        <table class="bordered mt-12">
            <thead>
                <tr>
                    @foreach(($rows->isNotEmpty()? array_keys((array)$rows->first()) : []) as $th)
                        <th>{{ strtoupper(str_replace('_',' ',$th)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach((array)$row as $cell)
                            <td>{{ is_scalar($cell) ? $cell : json_encode($cell) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="text-center muted" colspan="10">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        @php
            $low = collect($data['low_stock'] ?? []);
            $exp = collect($data['expiry'] ?? []);
        @endphp

        <h3 class="mt-12">Low-Stock Items</h3>
        <table class="bordered mt-4">
            <thead>
                <tr>
                    @php $hdr = $low->isNotEmpty()? array_keys((array)$low->first()) : []; @endphp
                    @foreach($hdr as $th)
                        <th>{{ strtoupper(str_replace('_',' ',$th)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($low as $row)
                    <tr>
                        @foreach((array)$row as $cell)
                            <td>{{ is_scalar($cell) ? $cell : json_encode($cell) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="text-center muted" colspan="10">No data</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3 class="mt-12">Expiry Alerts</h3>
        <table class="bordered mt-4">
            <thead>
                <tr>
                    @php $hdr2 = $exp->isNotEmpty()? array_keys((array)$exp->first()) : []; @endphp
                    @foreach($hdr2 as $th)
                        <th>{{ strtoupper(str_replace('_',' ',$th)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($exp as $row)
                    <tr>
                        @foreach((array)$row as $cell)
                            <td>{{ is_scalar($cell) ? $cell : json_encode($cell) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="text-center muted" colspan="10">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

</body>
</html>