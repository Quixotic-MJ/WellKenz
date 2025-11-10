<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_id';
    protected $fillable = ['po_ref','po_status','order_date','delivery_address','expected_delivery_date','total_amount','sup_id','req_id'];

    public function supplier(){ return $this->belongsTo(Supplier::class,'sup_id','sup_id'); }
    public function requisition(){ return $this->belongsTo(Requisition::class,'req_id','req_id'); }
    public function items(){ return $this->hasMany(PurchaseItem::class,'po_id','po_id'); }
}