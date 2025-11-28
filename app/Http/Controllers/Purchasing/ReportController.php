<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\RtvTransaction;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function purchaseHistory()
    {
        $purchaseHistory = PurchaseOrder::with(['supplier'])
            ->where('status', 'completed')
            ->orderBy('actual_delivery_date', 'desc')
            ->paginate(20);

        return view('Purchasing.reports.purchase_history', compact('purchaseHistory'));
    }

    public function completedHistory(Request $request)
    {
        $query = PurchaseOrder::where('status', 'completed')
            ->with(['supplier', 'purchaseOrderItems'])
            ->orderBy('actual_delivery_date', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('supplier_code', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_filter')) {
            $now = Carbon::now();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('actual_delivery_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('actual_delivery_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('actual_delivery_date', $now->month)
                          ->whereYear('actual_delivery_date', $now->year);
                    break;
                case 'year':
                    $query->whereYear('actual_delivery_date', $now->year);
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('actual_delivery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('actual_delivery_date', '<=', $request->date_to);
        }

        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportCompletedHistory($query->get());
        }

        $completedOrders = $query->paginate($request->get('per_page', 15));

        return view('Purchasing.purchase_orders.completed_history', compact('completedOrders'));
    }

    private function exportCompletedHistory($orders)
    {
        $filename = 'completed_history_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'PO Number', 'Supplier', 'Order Date', 'Delivery Date', 'Total Amount', 'Status'
            ]);
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->po_number,
                    $order->supplier->name ?? 'Unknown',
                    $order->order_date->format('Y-m-d'),
                    $order->actual_delivery_date ? $order->actual_delivery_date->format('Y-m-d') : 'N/A',
                    $order->grand_total,
                    $order->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function supplierPerformance()
    {
        return redirect()->route('purchasing.reports.history')
            ->with('info', 'Supplier performance report functionality has been integrated into the purchase history view.');
    }

    public function rtv(Request $request)
    {
        $query = RtvTransaction::with([
            'supplier', 'purchaseOrder', 'rtvItems.item.unit', 'createdBy'
        ])->orderBy('return_date', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('rtv_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('supplier_code', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('purchaseOrder', function($poq) use ($search) {
                      $poq->where('po_number', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('rtvItems', function($rtvi) use ($search) {
                      $rtvi->whereHas('item', function($itemq) use ($search) {
                          $itemq->where('name', 'ilike', "%{$search}%")
                                ->orWhere('item_code', 'ilike', "%{$search}%");
                      })->orWhere('reason', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        $rtvRecords = $query->paginate($request->get('per_page', 15));
        $summary = $this->getRtvSummaryStats();
        $suppliers = Supplier::where('is_active', true)
            ->whereHas('rtvTransactions')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('Purchasing.reports.RTV', compact('rtvRecords', 'summary', 'suppliers'));
    }

    private function getRtvSummaryStats(): array
    {
        $currentYear = date('Y');
        $totalReturnedYtd = RtvTransaction::whereYear('return_date', $currentYear)
            ->sum('total_value') ?: 0;
        $pendingCredits = RtvTransaction::where('status', 'pending')
            ->sum('total_value') ?: 0;
        $totalTransactionsYtd = RtvTransaction::whereYear('return_date', $currentYear)->count();
        $averageReturnValue = RtvTransaction::whereYear('return_date', $currentYear)
            ->avg('total_value') ?: 0;

        return [
            'total_returned_ytd' => $totalReturnedYtd,
            'pending_credits' => $pendingCredits,
            'total_transactions_ytd' => $totalTransactionsYtd,
            'average_return_value' => $averageReturnValue,
        ];
    }
}
