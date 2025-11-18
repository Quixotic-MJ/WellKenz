<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Procurement Report' }} - WellKenz ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-white p-8">
    <!-- Print Header -->
    <div class="mb-8 pb-6 border-b-2 border-gray-300">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">WellKenz ERP</h1>
            <p class="text-lg text-gray-600 mt-2">Procurement Report</p>
            <p class="text-sm text-gray-500 mt-1">Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>

    <!-- Report Title -->
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 text-center">{{ $title ?? 'Report' }}</h2>
    </div>

    <!-- Report Content -->
    <div class="mb-8">
        {!! $html ?? '<p class="text-gray-500 text-center">No data available.</p>' !!}
    </div>

    <!-- Print Footer -->
    <div class="mt-12 pt-6 border-t border-gray-300">
        <p class="text-xs text-gray-500 text-center">This is a system-generated report from WellKenz ERP</p>
        <p class="text-xs text-gray-400 text-center mt-1">Printed by: {{ auth()->user()->name ?? 'System' }}</p>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="no-print fixed bottom-8 right-8">
        <button onclick="window.print()" class="px-6 py-3 bg-gray-900 text-white hover:bg-gray-800 transition rounded-lg shadow-lg">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Report
        </button>
    </div>

    <script>
        // Auto-print on load (optional - uncomment if needed)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
