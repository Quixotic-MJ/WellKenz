<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WellKenz - Cakes & Pastries')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles matching the sidebar theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cream-bg': '#faf7f3',
                        'white': '#ffffff',
                        'chocolate': '#3d2817',
                        'chocolate-dark': '#2a1a0f',
                        'caramel': '#c48d3f',
                        'caramel-dark': '#a67332',
                        'text-dark': '#1a1410',
                        'text-muted': '#8b7355',
                        'border-soft': '#e8dfd4',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap');

        /* Print-optimized styles - PRESERVE DESIGN */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Keep original fonts and colors - don't override */
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                font-size: 11pt !important;
                line-height: 1.4 !important;
                color: #1a1410 !important;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Preserve design colors */
            .text-chocolate { color: #3d2817 !important; }
            .text-caramel { color: #c48d3f !important; }
            .border-chocolate { border-color: #3d2817 !important; }
            .bg-chocolate { background-color: #3d2817 !important; }
            
            /* Page setup */
            @page {
                margin: 15mm;
                size: A4;
            }
        /* Preserve Tailwind design classes in print */
        @media print {
            /* Brand colors preservation */
            .text-chocolate { color: #3d2817 !important; }
            .text-caramel { color: #c48d3f !important; }
            .text-gray-500 { color: #6b7280 !important; }
            .text-gray-600 { color: #4b5563 !important; }
            .text-gray-700 { color: #374151 !important; }
            .text-gray-900 { color: #111827 !important; }
            
            .bg-chocolate { background-color: #3d2817 !important; }
            .bg-gray-50 { background-color: #f9fafb !important; }
            .bg-gray-100 { background-color: #f3f4f6 !important; }
            
            .border-chocolate { border-color: #3d2817 !important; }
            .border-gray-200 { border-color: #e5e7eb !important; }
            .border-gray-300 { border-color: #d1d5db !important; }
            
            /* Font families */
            .font-display { 
                font-family: 'Playfair Display', serif !important;
                font-weight: 700 !important;
            }
            .font-bold { font-weight: 700 !important; }
            .font-medium { font-weight: 500 !important; }
            
            /* Ensure text remains readable */
            .uppercase { text-transform: uppercase !important; }
            .tracking-wide { letter-spacing: 0.025em !important; }
            .tracking-wider { letter-spacing: 0.05em !important; }
            .tracking-widest { letter-spacing: 0.1em !important; }
        }

            /* Only remove UI elements, preserve content styling */
            .screen-only {
                display: none !important;
            }
        }

        /* Screen-only styles for print preview */
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
            
            .screen-only {
                display: none !important;
            }
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
            // Get current URL and modify it to PDF route
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