{{-- This view is standalone and does NOT extend the app layout. --}}
{{-- It assumes your controller passes: $receipt, $receiver, $issuer, and $items --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Acknowledgement Receipt - {{ $receipt->ar_ref }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
        }
        th, td {
            padding: 6px 8px;
        }
        th {
            background-color: #f9fafb;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .border-table, .border-table th, .border-table td {
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-white text-gray-900">

    <div class="max-w-4xl mx-auto p-4">
        <!-- Print/Close Buttons -->
        <div class="mb-6 flex justify-end space-x-2 no-print">
            <button onclick="window.print();" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                Print
            </button>
            <button onclick="window.close();" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                Close
            </button>
        </div>

        <!-- Header -->
        <header class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    {{-- You can replace this with your company logo --}}
                    <h1 class="text-3xl font-bold text-gray-800">Your Company</h1>
                    <p class="text-gray-500">123 Company Address, City, State 12345</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-semibold uppercase text-gray-700">Acknowledgement Receipt</h2>
                    <p class="text-lg font-medium">{{ $receipt->ar_ref }}</p>
                </div>
            </div>
        </header>

        <!-- Receipt Info -->
        <div class="mb-6 p-4 border rounded-lg grid grid-cols-2 gap-4">
            <div>
                <strong class="text-gray-600 block mb-1">Issued To:</strong>
                <p class="font-medium">{{ $receiver->name ?? 'N/A' }}</p>
                <p>{{ $receiver->department_name ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p class="mb-1"><strong class="text-gray-600">Issue Date:</strong> {{ $receipt->issued_date->format('M d, Y H:i A') }}</p>
                <p><strong class="text-gray-600">Status:</strong> {{ ucfirst($receipt->ar_status) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <table class="border-table">
                <thead>
                    <tr>
                        <th class="w-1/4">Stock Code</th>
                        <th class="w-1/2">Description</th>
                        <th class="w-1/8 text-center">Quantity</th>
                        <th class="w-1/8">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($receipt->requisition->items ?? [] as $item)
                    <tr>
                        <td>{{ $item->item->item_name ?? 'N/A' }}</td>
                        <td>{{ $item->item->item_description ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->req_item_quantity ?? 0 }}</td>
                        <td>{{ $item->item_unit ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No items found for this requisition.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer / Signatures -->
        <footer class="pt-8 mt-12 border-t">
            <div class="grid grid-cols-2 gap-12">
                <div>
                    <p class="mb-2 text-gray-600">Issued By:</p>
                    <div class="mt-12 border-b border-gray-400"></div>
                    <p class="mt-2 font-medium">{{ $issuer->name ?? 'System' }}</p>
                    <p class="text-sm text-gray-500">Warehouse Staff</p>
                </div>
                <div>
                    <p class="mb-2 text-gray-600">Received By:</p>
                    <div class="mt-12 border-b border-gray-400"></div>
                    <p class="mt-2 font-medium">{{ $receiver->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">Receiver's Signature</p>
                </div>
            </div>
        </footer>

    </div>

    <script>
        // Automatically trigger print dialog on load
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>