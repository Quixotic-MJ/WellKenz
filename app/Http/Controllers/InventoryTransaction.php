<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;

class InventoryTransaction extends Controller
{
    /* ---------------- scopes ---------------- */
    public function scopeStockIn($q){ return $q->where('trans_type','in'); }
    public function scopeRecent($q,$days=7){
        return $q->whereBetween('trans_date',[now()->subDays($days),now()]);
    }

    /* ---------------- relations ---------------- */
    public function item(){ return $this->belongsTo(Item::class,'item_id','item_id'); }
    public function purchaseOrder(){ return $this->belongsTo(PurchaseOrder::class,'po_id','po_id'); }
    public function user(){ return $this->belongsTo(User::class,'trans_by','user_id'); }
}