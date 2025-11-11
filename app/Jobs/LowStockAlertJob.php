<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find items at or below reorder level
        $items = DB::table('items')
            ->where('is_active', true)
            ->whereColumn('item_stock', '<=', 'reorder_level')
            ->select('item_id','item_name','item_code','item_unit','item_stock','reorder_level')
            ->limit(500)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $purchasingUsers = DB::table('users')->where('role','purchasing')->pluck('user_id')->all();
        if (empty($purchasingUsers)) {
            Log::info('LowStockAlertJob: no purchasing users to notify');
            return;
        }

        $now = now();
        $toInsert = [];
        foreach ($items as $it) {
            $msg = sprintf('Low stock: %s (%s). Stock: %s%s, Reorder at: %s%s',
                $it->item_name, $it->item_code, (string)$it->item_stock, $it->item_unit ? ' '.$it->item_unit : '', (string)$it->reorder_level, $it->item_unit ? ' '.$it->item_unit : ''
            );
            foreach ($purchasingUsers as $uid) {
                $toInsert[] = [
                    'user_id'       => $uid,
                    'notif_title'   => 'Low Stock Alert',
                    'notif_content' => $msg,
                    'related_type'  => 'item',
                    'related_id'    => $it->item_id,
                    'is_read'       => false,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        if ($toInsert) {
            DB::table('notifications')->insert($toInsert);
        }
    }
}
