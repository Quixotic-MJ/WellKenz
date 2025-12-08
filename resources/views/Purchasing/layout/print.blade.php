<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WellKenz Bakery - Document')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Variables for Brand Colors -->
    <style>
        :root {
            --chocolate: #3d2817;
            --chocolate-dark: #2a1a0f;
            --caramel: #c48d3f;
            --caramel-dark: #a67332;
            --text-dark: #1a1410;
            --text-muted: #8b7355;
            --border-soft: #e8dfd4;
            --cream-bg: #faf7f3;
            --white: #ffffff;
        }

        /* Print-optimized styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Page setup */
            @page {
                margin: 15mm;
                size: A4;
                @bottom-center {
                    content: "WellKenz Bakery - " counter(page) " of " counter(pages);
                    font-size: 9pt;
                    color: #666;
                }
                @top-center {
                    content: "@yield('document_title', 'Document') - WellKenz Bakery";
                    font-size: 10pt;
                    color: #333;
                }
            }

            /* Reset margins and padding */
            body {
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                font-size: 11pt !important;
                line-height: 1.4 !important;
                color: #1a1410 !important;
                background: white !important;
            }

            /* Brand colors - preserved for print */
            .text-chocolate { color: #3d2817 !important; }
            .text-caramel { color: #c48d3f !important; }
            .border-chocolate { border-color: #3d2817 !important; }
            .bg-chocolate { background-color: #3d2817 !important; }
            
            /* Typography */
            .font-display { 
                font-family: 'Playfair Display', serif !important;
                font-weight: 700 !important;
            }
            .font-bold { font-weight: 700 !important; }
            .font-medium { font-weight: 500 !important; }
            .uppercase { text-transform: uppercase !important; }
            .tracking-wide { letter-spacing: 0.025em !important; }
            .tracking-wider { letter-spacing: 0.05em !important; }

            /* Hide UI elements */
            .screen-only {
                display: none !important;
            }

            /* Table optimizations */
            table {
                border-collapse: collapse !important;
            }

            th, td {
                border: 1pt solid #333 !important;
                padding: 8pt !important;
            }

            th {
                background-color: #f9f9f9 !important;
                font-weight: bold !important;
                color: #3d2817 !important;
            }

            /* Prevent page breaks in critical sections */
            .no-print-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Screen styles for print preview */
        @media screen {
            .print-preview-container {
                max-width: 210mm;
                margin: 20px auto;
                background: white;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                border-radius: 8px;
                overflow: hidden;
            }

            .print-screen-header {
                background: #f8f9fa;
                padding: 15px 20px;
                border-bottom: 1px solid #e9ecef;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .print-button {
                padding: 8px 16px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }

            .print-button:hover {
                background: #0056b3;
            }

            .back-button {
                background: #6c757d;
            }

            .back-button:hover {
                background: #545b62;
            }
        }

        /* Hide print elements on screen */
        .print-only {
            display: none;
        }

        @media print {
            .print-only {
                display: block !important;
            }
        }

        /* Common Document Styles */
        .document-header {
            border-bottom: 2px solid var(--chocolate);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            background: var(--chocolate);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .brand-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--chocolate);
            margin: 0;
            line-height: 1;
        }

        .brand-text p {
            color: var(--caramel);
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 2px;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }

        .metadata-box {
            background: var(--cream-bg);
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            padding: 15px;
            text-align: right;
            min-width: 200px;
        }

        .metadata-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--chocolate);
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metadata-box .doc-id {
            font-family: monospace;
            font-weight: 600;
            color: var(--caramel);
            margin-bottom: 8px;
        }

        .metadata-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .metadata-label {
            font-weight: 600;
            color: var(--text-muted);
        }

        .metadata-value {
            font-weight: 500;
            color: var(--text-dark);
        }

        /* Standardized Table Styles */
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .document-table th {
            background-color: var(--chocolate);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.5px;
            padding: 12px 8px;
            text-align: left;
        }

        .document-table td {
            border-bottom: 1px solid var(--border-soft);
            padding: 10px 8px;
            font-size: 10pt;
            vertical-align: top;
        }

        .document-table tr:nth-child(even) {
            background-color: rgba(250, 247, 243, 0.3);
        }

        /* Document Footer */
        .document-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-soft);
            text-align: center;
            font-size: 9pt;
            color: var(--text-muted);
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .print-timestamp {
            font-weight: 600;
            color: var(--text-dark);
        }
    </style>

    @stack('styles')
</head>

<body class="antialiased bg-gray-100">
    
    <!-- Screen-only header for print preview -->
    <div class="screen-only">
        <div class="print-preview-container">
            <div class="print-screen-header">
                <a href="{{ url()->previous() }}" class="print-button back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div style="display: flex; gap: 10px;">
                    <button onclick="window.print()" class="print-button">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="downloadPDF()" class="print-button">
                        <i class="fas fa-download"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="print-content">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
        function downloadPDF() {
            const currentUrl = window.location.href;
            const pdfUrl = currentUrl.replace('/print', '/pdf');
            window.open(pdfUrl, '_blank');
        }

        if (window.location.search.includes('print=true')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }

        window.addEventListener('beforeprint', function() {
            const url = new URL(window.location);
            url.searchParams.delete('print');
            window.history.replaceState({}, '', url);
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>