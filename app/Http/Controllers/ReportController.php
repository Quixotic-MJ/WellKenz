<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        $activeUsers           = DB::table('users')->where('status','active')->count();
        $pendingItemRequests   = DB::table('item_requests')->where('item_req_status','pending')->count();
        $pendingRequisitions   = DB::table('requisitions')->where('req_status','pending')->count();
        $orderedPOs            = DB::table('purchase_orders')->where('po_status','ordered')->count();
        $movementCount         = DB::table('inventory_transactions')->count();
        $lowStockCount         = count(DB::select('select * from get_low_stock_items()'));
        $suppliersCount        = DB::table('suppliers')->count();
        $weeklySummary         = DB::select('select * from stock_in_summary(?)', [7]);
        $weeklyStockInCount    = !empty($weeklySummary) ? ((array)$weeklySummary[0])['week_rcpt'] ?? 0 : 0;
        $negativeStockCount    = DB::table('items')->where('item_stock','<',0)->count();
        $arIssuedCount         = DB::table('acknowledge_receipts')->where('ar_status','issued')->count();

        return view('Admin.Report.report', compact(
            'activeUsers','pendingItemRequests','pendingRequisitions','orderedPOs',
            'movementCount','lowStockCount','suppliersCount','weeklyStockInCount',
            'negativeStockCount','arIssuedCount'
        ));
    }

    public function generate(Request $request, string $report)
    {
        $start  = $request->query('start', now()->subDays(30)->format('Y-m-d'));
        $end    = $request->query('end', now()->format('Y-m-d'));
        $format = $request->query('format','web');

        // Build dataset by report key (aligned to schema)
        switch ($report) {
            case 'user-activity':
                $data = $this->dsUserActivity($start, $end); break;
            case 'item-requests':
                $data = $this->dsItemRequests($start, $end); break;
            case 'requisitions':
                $data = $this->dsRequisitions($start, $end); break;
            case 'purchase-orders':
                $data = $this->dsPurchaseOrders($start, $end); break;
            case 'inventory-movements':
                $data = $this->dsInventoryMovements($start, $end); break;
            case 'expiry-low-stock':
                $data = $this->dsExpiryLowStock($start, $end); break;
            case 'supplier-performance':
                $data = $this->dsSupplierPerformance($start, $end); break;
            case 'weekly-stock-in':
                $data = $this->dsWeeklyStockIn($start, $end); break;
            case 'negative-stock':
                $data = $this->dsNegativeStock(); break;
            case 'ar-issuance':
                $data = $this->dsArIssuance($start, $end); break;
            default:
                $data = collect();
        }

        if ($format === 'csv') {
            return $this->csv($data, $report);
        }
        if ($format === 'pdf') {
            $title = Str::headline(str_replace('-', ' ', $report));
            $viewHtml = view('Admin.Report.pdf', [
                'report' => $report,
                'title'  => $title,
                'start'  => $start,
                'end'    => $end,
                'data'   => $data,
            ])->render();
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($viewHtml)->setPaper('a4','landscape')->download($report.'-'.now()->format('Ymd').'.pdf');
            }
            if (app()->bound('dompdf.wrapper')) {
                $pdf = app('dompdf.wrapper');
                $pdf->loadHTML($viewHtml)->setPaper('a4','landscape');
                return $pdf->download($report.'-'.now()->format('Ymd').'.pdf');
            }
            return response('PDF export requires barryvdh/laravel-dompdf. Install: composer require barryvdh/laravel-dompdf', 400);
        }

        $title = Str::headline(str_replace('-', ' ', $report));
        $html  = $this->htmlFromDataset($report, $data);
        $chart = $this->chartFromDataset($report, $data);
        return response()->json(['title'=>$title,'html'=>$html,'chart'=>$chart]);
    }

    /* ---------- Dataset builders (use schema-correct columns) ---------- */
    private function dsUserActivity($start,$end)
    {
        // No login_logs in schema; provide status counts and list users in range
        return DB::table('users')
            ->select('name','status','created_at')
            ->when($start, fn($q)=>$q->whereDate('created_at','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('created_at','<=',$end))
            ->orderBy('created_at','desc')
            ->get();
    }

    private function dsItemRequests($start,$end)
    {
        return DB::table('item_requests')
            ->select('item_req_status', DB::raw('COUNT(*) as total'))
            ->when($start, fn($q)=>$q->whereDate('created_at','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('created_at','<=',$end))
            ->groupBy('item_req_status')
            ->orderBy('item_req_status')
            ->get();
    }

    private function dsRequisitions($start,$end)
    {
        return DB::table('requisitions')
            ->select('req_status', DB::raw('COUNT(*) as total'))
            ->when($start, fn($q)=>$q->whereDate('req_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('req_date','<=',$end))
            ->groupBy('req_status')
            ->orderBy('req_status')
            ->get();
    }

    private function dsPurchaseOrders($start,$end)
    {
        return DB::table('purchase_orders')
            ->select('po_status', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(total_amount),0) as value'))
            ->when($start, fn($q)=>$q->whereDate('order_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('order_date','<=',$end))
            ->groupBy('po_status')
            ->orderBy('po_status')
            ->get();
    }

    private function dsInventoryMovements($start,$end)
    {
        return DB::table('inventory_transactions')
            ->select('trans_type', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(trans_quantity),0) as qty'))
            ->when($start, fn($q)=>$q->whereDate('trans_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('trans_date','<=',$end))
            ->groupBy('trans_type')
            ->orderBy('trans_type')
            ->get();
    }

    private function dsExpiryLowStock($start,$end)
    {
        $low = DB::select('select * from get_low_stock_items()');
        $days = 30;
        if ($start && $end) {
            try { $days = max(1, (int) ((strtotime($end) - strtotime($start)) / 86400)); } catch (\Throwable $e) {}
        }
        $exp = DB::select('select * from get_expiry_alerts(?)', [$days]);
        return [ 'low_stock' => $low, 'expiry' => $exp ];
    }

    private function dsSupplierPerformance($start,$end)
    {
        return DB::table('suppliers as s')
            ->leftJoin('purchase_orders as p','p.sup_id','=','s.sup_id')
            ->select('s.sup_name',
                DB::raw('COUNT(p.po_id) as orders'),
                DB::raw("SUM(CASE WHEN p.po_status='delivered' AND p.expected_delivery_date < p.updated_at::date THEN 1 ELSE 0 END) as late")
            )
            ->when($start, fn($q)=>$q->whereDate('p.order_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('p.order_date','<=',$end))
            ->groupBy('s.sup_id','s.sup_name')
            ->orderBy('s.sup_name')
            ->get();
    }

    private function dsWeeklyStockIn($start,$end)
    {
        return DB::table('inventory_transactions')
            ->select(DB::raw('trans_date::date as day'), DB::raw("SUM(CASE WHEN trans_type='in' THEN trans_quantity ELSE 0 END) as qty"))
            ->when($start, fn($q)=>$q->whereDate('trans_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('trans_date','<=',$end))
            ->groupBy(DB::raw('trans_date::date'))
            ->orderBy(DB::raw('trans_date::date'))
            ->get();
    }

    private function dsNegativeStock()
    {
        return DB::table('items')
            ->select('item_code','item_name','item_unit','item_stock')
            ->where('item_stock','<',0)
            ->orderBy('item_stock')
            ->get();
    }

    private function dsArIssuance($start,$end)
    {
        return DB::table('acknowledge_receipts')
            ->select('ar_ref','ar_status','issued_date','req_id')
            ->when($start, fn($q)=>$q->whereDate('issued_date','>=',$start))
            ->when($end, fn($q)=>$q->whereDate('issued_date','<=',$end))
            ->orderBy('issued_date','desc')
            ->get();
    }

    /* ---------- HTML builder for datasets ---------- */
    private function htmlFromDataset(string $report, $data): string
    {
        switch ($report) {
            case 'user-activity':
                return $this->table(['Name','Status','Joined'], collect($data)->map(fn($r)=>[$r->name, $r->status, $r->created_at])->toArray());
            case 'item-requests':
                return $this->table(['Status','Total'], collect($data)->map(fn($r)=>[$r->item_req_status, $r->total])->toArray());
            case 'requisitions':
                return $this->table(['Status','Total'], collect($data)->map(fn($r)=>[$r->req_status, $r->total])->toArray());
            case 'purchase-orders':
                return $this->table(['Status','Total','Value'], collect($data)->map(fn($r)=>[$r->po_status, $r->total, $r->value])->toArray());
            case 'inventory-movements':
                return $this->table(['Type','Transactions','Total Qty'], collect($data)->map(fn($r)=>[$r->trans_type, $r->total, $r->qty])->toArray());
            case 'expiry-low-stock':
                $lowRows = array_map(function($r){ $r=(array)$r; return [$r['item_code']??'', $r['item_name']??'', $r['current_stock']??0, $r['reorder_level']??0, $r['stock_status']??'']; }, $data['low_stock'] ?? []);
                $expRows = array_map(function($r){ $r=(array)$r; return [$r['item_code']??'', $r['item_name']??'', $r['current_stock']??0, $r['item_expire_date']??'', $r['days_until_expiry']??'', $r['expiry_status']??'']; }, $data['expiry'] ?? []);
                $lowTable = $this->table(['Item Code','Item Name','Stock','Reorder Level','Status'], $lowRows);
                $expTable = $this->table(['Item Code','Item Name','Stock','Expire Date','Days Left','Status'], $expRows);
                return '<div class="space-y-6">'
                    . '<div><h4 class="font-semibold mb-2">Low-Stock Items</h4>'.$lowTable.'</div>'
                    . '<div><h4 class="font-semibold mb-2">Expiry Alerts</h4>'.$expTable.'</div>'
                    . '</div>';
            case 'supplier-performance':
                return $this->table(['Supplier','Orders','Late'], collect($data)->map(fn($r)=>[$r->sup_name, $r->orders, $r->late])->toArray());
            case 'weekly-stock-in':
                return $this->table(['Date','Stock-In Qty'], collect($data)->map(fn($r)=>[$r->day, $r->qty])->toArray());
            case 'negative-stock':
                return $this->table(['Item Code','Item Name','Unit','Stock'], collect($data)->map(fn($r)=>[$r->item_code,$r->item_name,$r->item_unit,$r->item_stock])->toArray());
            case 'ar-issuance':
                return $this->table(['AR Ref','Status','Issued Date','Requisition ID'], collect($data)->map(fn($r)=>[$r->ar_ref,$r->ar_status,$r->issued_date,$r->req_id])->toArray());
            default:
                return $this->table(['Info'], []);
        }
    }

    /* ---------- CSV export ---------- */
    private function csv($data, string $report)
    {
        $filename = $report.'-'.now()->format('Ymd').'.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->stream(function() use ($data, $report) {
            $out = fopen('php://output','w');

            $writeRows = function($rows) use ($out){
                $rows = collect($rows);
                if ($rows->isEmpty()) { return; }
                fputcsv($out, array_keys((array)$rows->first()));
                foreach ($rows as $r) {
                    fputcsv($out, (array)$r);
                }
            };

            if ($report === 'expiry-low-stock') {
                // two sections
                fputcsv($out, ['Low-Stock']);
                $low = $data['low_stock'] ?? [];
                $writeRows($low);
                fputcsv($out, []);
                fputcsv($out, ['Expiry Alerts']);
                $exp = $data['expiry'] ?? [];
                $writeRows($exp);
            } else {
                $writeRows($data);
            }

            fclose($out);
        }, 200, $headers);
    }

    private function table(array $headers, array $rows): string
    {
        $thead = '<tr>'.implode('', array_map(fn($h)=>"<th class=\"px-4 py-2 text-left text-xs font-semibold text-gray-600\">".e($h)."</th>", $headers)).'</tr>';
        $tbody = '';
        foreach ($rows as $r) {
            $cells = array_map(fn($c)=>"<td class=\"px-4 py-2 text-sm text-gray-800\">".e((string)$c)."</td>", $r);
            $tbody .= '<tr class="border-t">'.implode('', $cells).'</tr>';
        }
        if ($tbody==='') {
            $tbody = '<tr><td class="px-4 py-4 text-sm text-gray-500" colspan="'.count($headers).'">No data</td></tr>';
        }
        return '<div class="overflow-x-auto"><table class="min-w-full">'
             .'<thead class="bg-gray-50">'.$thead.'</thead>'
             .'<tbody>'.$tbody.'</tbody>'
             .'</table></div>';
    }

    /* ---------- Chart builder for Chart.js ---------- */
    private function chartFromDataset(string $report, $data): ?array
    {
        switch ($report) {
            case 'item-requests':
                $labels = collect($data)->pluck('item_req_status')->toArray();
                $vals   = collect($data)->pluck('total')->toArray();
                return ['type'=>'doughnut','labels'=>$labels,'datasets'=>[['label'=>'Item Requests','data'=>$vals]]];
            case 'requisitions':
                $labels = collect($data)->pluck('req_status')->toArray();
                $vals   = collect($data)->pluck('total')->toArray();
                return ['type'=>'doughnut','labels'=>$labels,'datasets'=>[['label'=>'Requisitions','data'=>$vals]]];
            case 'purchase-orders':
                $labels = collect($data)->pluck('po_status')->toArray();
                $vals   = collect($data)->pluck('total')->toArray();
                return ['type'=>'bar','labels'=>$labels,'datasets'=>[['label'=>'POs','data'=>$vals]]];
            case 'inventory-movements':
                $labels = collect($data)->pluck('trans_type')->toArray();
                $vals   = collect($data)->pluck('qty')->toArray();
                return ['type'=>'bar','labels'=>$labels,'datasets'=>[['label'=>'Total Qty','data'=>$vals]]];
            case 'weekly-stock-in':
                $labels = collect($data)->pluck('day')->toArray();
                $vals   = collect($data)->pluck('qty')->toArray();
                return ['type'=>'line','labels'=>$labels,'datasets'=>[['label'=>'Stock-In','data'=>$vals]]];
            case 'negative-stock':
                $labels = collect($data)->pluck('item_code')->toArray();
                $vals   = collect($data)->pluck('item_stock')->toArray();
                return ['type'=>'bar','labels'=>$labels,'datasets'=>[['label'=>'Stock','data'=>$vals]]];
            case 'ar-issuance':
                $labels = collect($data)->pluck('issued_date')->toArray();
                $vals   = array_fill(0, count($labels), 1);
                return ['type'=>'bar','labels'=>$labels,'datasets'=>[['label'=>'AR count','data'=>$vals]]];
            default:
                return null;
        }
    }
}
