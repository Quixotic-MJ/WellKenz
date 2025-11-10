<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model
{
    use HasFactory;
    protected $table = 'purchase_items';
    protected $primaryKey = 'pi_id';
    protected $fillable = ['pi_quantity','pi_unit_price','pi_subtotal','po_id','item_id'];
    public function item(){ return $this->belongsTo(Item::class,'item_id','item_id'); }
}