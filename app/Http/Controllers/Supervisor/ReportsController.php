<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function expiryReport(Request $request)
    {
        $filter = $request->get('filter', '7days'); // Default to next 7 days
        $search = $request->get('search', '');

        // Calculate expiry categories
        $now = Carbon::now();
        $criticalBatches = $this->getExpiringBatches('critical', $search); // < 48 hours
        $warningBatches = $this->getExpiringBatches('warning', $search); // < 7 days
        $expiredBatches = $this->getExpiringBatches('expired', $search); // Already expired

        // Calculate summary statistics
        $summary = $this->calculateExpirySummary($criticalBatches, $warningBatches, $expiredBatches);

        // Get batches based on filter
        $expiringBatches = $this->getFilteredBatches($filter, $search);

        return view('Supervisor.reports.expiry_report', compact(
            'expiringBatches',
            'summary',
            'filter',
            'search',
            'criticalBatches',
            'warningBatches',
            'expiredBatches'
        ));
    }

    public function printUseFirstList(Request $request)
    {
        // Get priority batches (critical and warning)
        $priorityBatches = Batch::with(['item.unit', 'supplier'])
            ->where('status', 'active')
            ->where(function($query) {
                $query->where('expiry_date', '<=', Carbon::now()->addDays(7))
                      ->orWhere('expiry_date', '<', Carbon::now());
            })
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Calculate summary statistics
        $criticalCount = $priorityBatches->filter(function($batch) {
            return Carbon::parse($batch->expiry_date)->diffInDays(Carbon::now(), false) <= 1;
        })->count();

        $warningCount = $priorityBatches->filter(function($batch) {
            $daysUntil = Carbon::parse($batch->expiry_date)->diffInDays(Carbon::now(), false);
            return $daysUntil > 1 && $daysUntil <= 7;
        })->count();

        $totalCount = $priorityBatches->count();
        $totalValue = $priorityBatches->sum(function($batch) {
            return $batch->quantity * $batch->unit_cost;
        });

        $batches = $priorityBatches;

        return view('Supervisor.reports.print_use_first_list', compact(
            'batches',
            'criticalCount',
            'warningCount',
            'totalCount',
            'totalValue'
        ));
    }

    public function exportUseFirstListPDF(Request $request)
    {
        // Get priority batches (critical and warning) - same logic as print method
        $priorityBatches = \App\Models\Batch::with(['item.unit', 'supplier', 'item.category'])
            ->where('status', 'active')
            ->where(function($query) {
                $query->where('expiry_date', '<=', \Carbon\Carbon::now()->addDays(7))
                      ->orWhere('expiry_date', '<', \Carbon\Carbon::now());
            })
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Calculate summary statistics
        $criticalCount = $priorityBatches->filter(function($batch) {
            return \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(\Carbon\Carbon::now(), false) <= 1;
        })->count();

        $warningCount = $priorityBatches->filter(function($batch) {
            $daysUntil = \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(\Carbon\Carbon::now(), false);
            return $daysUntil > 1 && $daysUntil <= 7;
        })->count();

        $expiredCount = $priorityBatches->filter(function($batch) {
            return \Carbon\Carbon::parse($batch->expiry_date)->isPast();
        })->count();

        $totalCount = $priorityBatches->count();
        $totalValue = $priorityBatches->sum(function($batch) {
            return $batch->quantity * $batch->unit_cost;
        });

        // Prepare data for PDF
        $data = [
            'batches' => $priorityBatches,
            'criticalCount' => $criticalCount,
            'warningCount' => $warningCount,
            'expiredCount' => $expiredCount,
            'totalCount' => $totalCount,
            'totalValue' => $totalValue,
            'generatedAt' => now()->format('F d, Y • h:i A'),
            'generatedBy' => Auth::user()->name ?? 'System'
        ];

        // Generate PDF
        $pdf = Pdf::loadView('Supervisor.reports.print_use_first_list', $data);
        
        // Configure PDF settings with proper margins
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isCssEnabled' => true,
            'margin_top' => 20,
            'margin_right' => 15,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'dpi' => 150,
            'enable_font_subsetting' => true,
            'defaultPaperSize' => 'a4',
            'defaultPaperOrientation' => 'portrait',
        ]);

        // Generate filename with timestamp
        $filename = 'use_first_list_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        // Download PDF
        return $pdf->download($filename);
    }

    public function alertBakers(Request $request)
    {
        try {
            // Get critical batches (expiring in next 48 hours)
            $criticalBatches = Batch::with(['item.unit'])
                ->where('status', 'active')
                ->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(2)])
                ->orderBy('expiry_date', 'asc')
                ->get();

            // Get warning batches (expiring in next 7 days)
            $warningBatches = Batch::with(['item.unit'])
                ->where('status', 'active')
                ->where('expiry_date', '>', Carbon::now()->addDays(2))
                ->where('expiry_date', '<=', Carbon::now()->addDays(7))
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($criticalBatches->isEmpty() && $warningBatches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No expiring items found to alert about.'
                ]);
            }

            // Create notifications for bakers (users with 'employee' role)
            $bakerUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            $notificationsCreated = 0;

            foreach ($bakerUsers as $user) {
                // Critical alert
                foreach ($criticalBatches as $batch) {
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'URGENT: Items Expiring Soon',
                        'message' => "{$batch->item->name} (Batch: {$batch->batch_number}) expires on " .
                                    Carbon::parse($batch->expiry_date)->format('M j, Y') .
                                    ". Use immediately to avoid waste.",
                        'type' => 'expiry_alert',
                        'priority' => 'urgent',
                        'is_read' => false,
                        'created_at' => Carbon::now()
                    ]);
                    $notificationsCreated++;
                }

                // Warning alert
                foreach ($warningBatches as $batch) {
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'Notice: Items Expiring Soon',
                        'message' => "{$batch->item->name} (Batch: {$batch->batch_number}) expires on " .
                                    Carbon::parse($batch->expiry_date)->format('M j, Y') .
                                    ". Please plan usage accordingly.",
                        'type' => 'expiry_alert',
                        'priority' => 'high',
                        'is_read' => false,
                        'created_at' => Carbon::now()
                    ]);
                    $notificationsCreated++;
                }
            }

            // Create audit log
            AuditLog::create([
                'table_name' => 'notifications',
                'record_id' => 0, // Multiple records created
                'action' => 'CREATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => json_encode([
                    'type' => 'batch_expiry_alert',
                    'critical_batches' => $criticalBatches->count(),
                    'warning_batches' => $warningBatches->count(),
                    'recipients' => $bakerUsers->count(),
                    'total_notifications' => $notificationsCreated
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Alert sent to {$bakerUsers->count()} baker(s) about {$criticalBatches->count()} critical and {$warningBatches->count()} warning items.",
                'data' => [
                    'critical_count' => $criticalBatches->count(),
                    'warning_count' => $warningBatches->count(),
                    'bakers_notified' => $bakerUsers->count(),
                    'total_notifications' => $notificationsCreated
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending baker alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send alerts. Please try again.'
            ], 500);
        }
    }

    public function getBatchDetails($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])
                ->whereHas('item', function($query) {
                    $query->where('is_active', true);
                })
                ->findOrFail($batchId);

            // Format batch data for display
            $now = Carbon::now();
            $expiryDate = Carbon::parse($batch->expiry_date);
            $manufacturingDate = $batch->manufacturing_date ? Carbon::parse($batch->manufacturing_date) : null;
            $daysUntilExpiry = $now->diffInDays($expiryDate, false);
            $isPastExpiry = $expiryDate->isPast();

            // Determine status and priority
            $status = 'active';
            $priority = 'Normal';
            $priorityClass = 'text-green-600';
            $urgentAction = false;

            if ($isPastExpiry) {
                $status = 'expired';
                $priority = 'EXPIRED';
                $priorityClass = 'text-red-600 font-bold';
                $urgentAction = true;
            } elseif ($daysUntilExpiry <= 1) {
                $priority = 'CRITICAL';
                $priorityClass = 'text-red-600 font-bold';
                $urgentAction = true;
            } elseif ($daysUntilExpiry <= 3) {
                $priority = 'High Priority';
                $priorityClass = 'text-orange-600';
                $urgentAction = true;
            } elseif ($daysUntilExpiry <= 7) {
                $priority = 'Warning';
                $priorityClass = 'text-amber-600';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'item' => [
                        'id' => $batch->item->id,
                        'name' => $batch->item->name ?? 'Unknown Item',
                        'item_code' => $batch->item->item_code ?? '',
                        'unit_symbol' => $batch->item->unit->symbol ?? ''
                    ],
                    'supplier' => [
                        'name' => $batch->supplier->name ?? 'Unknown Supplier'
                    ],
                    'quantity' => (float) $batch->quantity,
                    'unit_cost' => (float) $batch->unit_cost,
                    'total_value' => number_format($batch->quantity * $batch->unit_cost, 2),
                    'manufacturing_date' => $manufacturingDate ? $manufacturingDate->format('M d, Y') : 'N/A',
                    'expiry_date' => $expiryDate->format('M d, Y'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'location' => $batch->location ?? 'Main Storage',
                    'status' => $status,
                    'priority' => $priority,
                    'priority_class' => $priorityClass,
                    'urgent_action' => $urgentAction,
                    'is_expired' => $isPastExpiry,
                    'created_at' => $batch->created_at->format('M d, Y H:i'),
                    'notes' => $batch->notes ?? 'No additional notes'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting batch details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }
    }

    /**
     * Get expiring batches by category
     */
    private function getExpiringBatches($category, $search = '')
    {
        $now = Carbon::now();
        $query = Batch::with(['item.unit', 'supplier']);

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        switch ($category) {
            case 'critical':
                return $query->where('status', 'active')
                    ->whereBetween('expiry_date', [$now, $now->copy()->addDays(2)])
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });

            case 'warning':
                return $query->where('status', 'active')
                    ->where('expiry_date', '>', $now->copy()->addDays(2))
                    ->where('expiry_date', '<=', $now->copy()->addDays(7))
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });

            case 'expired':
                return $query->where('status', 'active')
                    ->where('expiry_date', '<', $now)
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });
        }

        return collect();
    }

    /**
     * Get filtered batches for table display
     */
    private function getFilteredBatches($filter, $search = '')
    {
        $now = Carbon::now();
        $query = Batch::with(['item.unit', 'supplier'])
            ->where('status', 'active');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        switch ($filter) {
            case '7days':
                $query->where('expiry_date', '<=', $now->copy()->addDays(7));
                break;
            case '30days':
                $query->where('expiry_date', '<=', $now->copy()->addDays(30));
                break;
            case 'expired':
                $query->where('expiry_date', '<', $now);
                break;
        }

        return $query->orderBy('expiry_date', 'asc')->paginate(20);
    }

    /**
     * Format batch data for display
     */
    private function formatBatchData($batch)
    {
        $now = Carbon::now();
        $expiryDate = Carbon::parse($batch->expiry_date);
        $daysUntilExpiry = $now->diffInDays($expiryDate, false); // false for absolute difference
        $isPastExpiry = $expiryDate->isPast();

        // Determine priority status
        $priority = 'Normal';
        $priorityClass = 'text-green-600';
        $statusClass = 'text-green-600';
        $urgentAction = false;

        if ($isPastExpiry) {
            $priority = 'EXPIRED';
            $priorityClass = 'text-red-600 font-bold';
            $statusClass = 'text-red-600 font-bold';
            $urgentAction = true;
        } elseif ($daysUntilExpiry <= 1) {
            $priority = 'CRITICAL';
            $priorityClass = 'text-red-600 font-bold';
            $statusClass = 'text-red-600 font-bold';
            $urgentAction = true;
        } elseif ($daysUntilExpiry <= 3) {
            $priority = 'High Priority';
            $priorityClass = 'text-orange-600';
            $statusClass = 'text-orange-600';
        } elseif ($daysUntilExpiry <= 7) {
            $priority = 'Monitor';
            $priorityClass = 'text-amber-600';
            $statusClass = 'text-amber-600';
        }

        // Format countdown text
        $countdownText = '';
        $countdownClass = '';

        if ($isPastExpiry) {
            $countdownText = 'EXPIRED';
            $countdownClass = 'bg-red-600 text-white animate-pulse';
        } elseif ($daysUntilExpiry == 0) {
            $countdownText = 'EXPIRES TODAY';
            $countdownClass = 'bg-red-600 text-white animate-pulse';
        } elseif ($daysUntilExpiry == 1) {
            $countdownText = '1 Day Left';
            $countdownClass = 'bg-red-600 text-white';
        } else {
            $countdownText = $daysUntilExpiry . ' Days Left';
            if ($daysUntilExpiry <= 3) {
                $countdownClass = 'bg-red-100 text-red-800 border border-red-200';
            } elseif ($daysUntilExpiry <= 7) {
                $countdownClass = 'bg-amber-100 text-amber-800';
            } else {
                $countdownClass = 'bg-gray-100 text-gray-600';
            }
        }

        return [
            'id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'item_name' => $batch->item->name ?? 'Unknown Item',
            'item_code' => $batch->item->item_code ?? '',
            'unit_symbol' => $batch->item->unit->symbol ?? '',
            'quantity' => number_format($batch->quantity, 1),
            'unit_cost' => number_format($batch->unit_cost, 2),
            'total_value' => number_format($batch->quantity * $batch->unit_cost, 2),
            'expiry_date' => $expiryDate->format('M j, Y'),
            'manufacturing_date' => $batch->manufacturing_date ? Carbon::parse($batch->manufacturing_date)->format('M j, Y') : 'N/A',
            'days_until_expiry' => $daysUntilExpiry,
            'countdown_text' => $countdownText,
            'countdown_class' => $countdownClass,
            'priority' => $priority,
            'priority_class' => $priorityClass,
            'status_class' => $statusClass,
            'supplier_name' => $batch->supplier->name ?? 'Unknown Supplier',
            'location' => $batch->location ?? 'Storage',
            'urgent_action' => $urgentAction,
            'is_expired' => $isPastExpiry,
            'is_critical' => $daysUntilExpiry <= 1,
            'is_warning' => $daysUntilExpiry > 1 && $daysUntilExpiry <= 7
        ];
    }

    /**
     * Calculate expiry summary statistics
     */
    private function calculateExpirySummary($criticalBatches, $warningBatches, $expiredBatches)
    {
        // Count batches
        $criticalCount = $criticalBatches->count();
        $warningCount = $warningBatches->count();
        $expiredCount = $expiredBatches->count();

        // Calculate total value at risk
        $criticalValue = $criticalBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $warningValue = $warningBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $expiredValue = $expiredBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $totalValueAtRisk = $criticalValue + $warningValue + $expiredValue;

        return [
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'expired_count' => $expiredCount,
            'total_batches' => $criticalCount + $warningCount + $expiredCount,
            'critical_value' => $criticalValue,
            'warning_value' => $warningValue,
            'expired_value' => $expiredValue,
            'total_value_at_risk' => $totalValueAtRisk,
            'formatted_total_value' => '₱' . number_format($totalValueAtRisk, 2)
        ];
    }
}
