
<div class="print-screen-view">
    {{-- Header Section for Screen View Only --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">Batch Labels Print Preview</h1>
                <p class="text-sm text-gray-500 mt-1">Professional warehouse labels for {{ $batches->count() }} batch(es)</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.history.back()" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <button onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                    <i class="fas fa-print mr-2"></i> Print Now
                </button>
            </div>
        </div>
    </div>

    {{-- Labels Grid Container --}}
    <div class="labels-grid-container">
        @foreach($batches as $batch)
        <div class="batch-label" data-batch-id="{{ $batch->id }}">
            {{-- Label Header --}}
            <div class="label-header">
                <div class="company-logo">
                    <span class="company-name">WellKenz Bakery</span>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="label-main-content">
                {{-- Item Name (Large, Bold) --}}
                <div class="item-name">
                    {{ Str::limit($batch->item->name, 30, '...') }}
                </div>

                {{-- Batch Number (Monospace) --}}
                <div class="batch-number">
                    BATCH: {{ $batch->batch_number }}
                </div>

                {{-- Metadata Grid --}}
                <div class="metadata-grid">
                    <div class="metadata-item">
                        <span class="metadata-label">SKU:</span>
                        <span class="metadata-value">{{ $batch->item->item_code }}</span>
                    </div>
                    
                    <div class="metadata-item">
                        <span class="metadata-label">QTY:</span>
                        <span class="metadata-value">{{ number_format($batch->quantity) }} {{ $batch->item->unit->symbol ?? 'pcs' }}</span>
                    </div>
                    
                    <div class="metadata-item expiry-highlight">
                        <span class="metadata-label">EXPIRY:</span>
                        <span class="metadata-value">
                            @if($batch->expiry_date)
                                {{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') }}
                            @else
                                NO EXPIRY
                            @endif
                        </span>
                    </div>
                    
                    <div class="metadata-item">
                        <span class="metadata-label">RECEIVED:</span>
                        <span class="metadata-value">{{ $batch->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="metadata-item supplier-info">
                        <span class="metadata-label">SUPPLIER:</span>
                        <span class="metadata-value">{{ Str::limit($batch->supplier->name ?? 'N/A', 20) }}</span>
                    </div>
                    

                </div>
            </div>


        </div>
        @endforeach
    </div>

    {{-- Print Instructions (Screen Only) --}}
    <div class="print-instructions screen-only">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <h3 class="text-sm font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Print Instructions
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Use a 4" x 3" label printer or standard printer with label sheets</li>
                <li>• Ensure high contrast printing for thermal printers</li>
                <li>• Labels are optimized for warehouse scanning from 5 feet distance</li>
                <li>• Click "Print Now" or use Ctrl+P / Cmd+P</li>
            </ul>
        </div>
    </div>
</div>

{{-- Auto-print functionality --}}
@if($autoPrint ?? false)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-trigger print after a short delay to allow page to load
    setTimeout(function() {
        window.print();
    }, 1000);
});
</script>
@endif



@push('styles')
<style>
/* ============================================
   SCREEN VIEW STYLES
   ============================================ */
.print-screen-view {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.screen-only {
    display: block;
}

/* ============================================
   PRINT-SPECIFIC CSS
   ============================================ */
@media print {
    /* Hide everything except print content */
    body * {
        visibility: hidden;
    }
    
    .batch-label,
    .batch-label * {
        visibility: visible;
    }
    
    /* Remove all margins and padding for printing */
    @page {
        margin: 0;
        size: auto;
    }
    
    /* Hide screen-only elements when printing */
    .print-screen-view > *:not(.labels-grid-container),
    .screen-only {
        display: none !important;
    }
    
    /* Print container */
    .labels-grid-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    
    /* Label dimensions - 3.5" x 2.5" warehouse label size */
    .batch-label {
        width: 3.5in;
        height: 2.5in;
        border: 2px solid #000;
        padding: 0.12in;
        margin: 0.08in;
        display: inline-block;
        vertical-align: top;
        page-break-inside: avoid;
        break-inside: avoid;
        background: white;
        color: black;
        font-family: Arial, sans-serif;
    }
    
    /* Grid layout for multiple labels - fits 2 per row on A4 */
    .labels-grid-container {
        display: grid;
        grid-template-columns: repeat(2, 3.5in);
        gap: 0.15in;
        justify-content: start;
        align-content: start;
        max-width: 7.5in; /* Prevent overflow */
    }
}

/* ============================================
   LABEL STYLES (Shared for screen and print)
   ============================================ */
.batch-label {
    background: white;
    color: black;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    position: relative;
    font-family: Arial, sans-serif;
}

/* Screen view label sizing */
@media screen {
    .batch-label {
        width: 280px; /* 3.5in converted to pixels for screen */
        height: 200px; /* 2.5in converted to pixels for screen */
        margin: 10px;
    }
    
    .labels-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        justify-content: center;
    }
}

/* ============================================
   LABEL CONTENT STYLES
   ============================================ */

/* Header Section */
.label-header {
    border-bottom: 2px solid #000;
    padding-bottom: 4px;
    margin-bottom: 6px;
    text-align: center;
}

.company-logo {
    font-weight: bold;
    font-size: 10px;
    color: #000;
}

.company-name {
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0.5px;
}

/* Main Content */
.label-main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Item Name - Large and Bold (24px+) */
.item-name {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    line-height: 1.2;
    margin-bottom: 8px;
    color: #000;
    word-wrap: break-word;
}

/* Batch Number - Monospace */
.batch-number {
    font-family: 'Courier New', monospace;
    font-size: 10px;
    font-weight: bold;
    text-align: center;
    background: #f0f0f0;
    padding: 3px;
    border: 1px solid #000;
    margin-bottom: 8px;
    color: #000;
    letter-spacing: 0.5px;
}

/* Metadata Grid */
.metadata-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    margin-bottom: 8px;
}

.metadata-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 8px;
    line-height: 1.1;
}

.metadata-label {
    font-weight: bold;
    color: #000;
}

.metadata-value {
    color: #000;
    font-weight: normal;
    text-align: right;
}

/* Expiry Highlight */
.expiry-highlight {
    background: #ffeb3b;
    padding: 2px;
    border: 1px solid #000;
}

.expiry-highlight .metadata-value {
    font-weight: bold;
    font-size: 9px;
}

/* Supplier Info */
.supplier-info .metadata-value {
    font-size: 7px;
}



/* ============================================
   RESPONSIVE ADJUSTMENTS
   ============================================ */

/* For smaller screens, reduce label size */
@media screen and (max-width: 768px) {
    .batch-label {
        width: 240px;
        height: 172px; /* 3.5:2.5 ratio */
    }
    
    .labels-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
    
    .item-name {
        font-size: 14px;
    }
    
    .batch-number {
        font-size: 9px;
    }
    
    .metadata-item {
        font-size: 7px;
    }
}

/* For very small screens */
@media screen and (max-width: 480px) {
    .batch-label {
        width: 200px;
        height: 143px; /* 3.5:2.5 ratio */
    }
    
    .labels-grid-container {
        grid-template-columns: 1fr;
    }
    
    .item-name {
        font-size: 12px;
    }
    
    .metadata-grid {
        grid-template-columns: 1fr;
        gap: 2px;
    }
}
</style>
@endpush