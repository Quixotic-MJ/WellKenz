<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Supplier extends Model
{
    use HasFactory;
    protected $table = 'suppliers';
    protected $primaryKey = 'sup_id';
    protected $fillable = ['sup_name','sup_email','sup_address','contact_person','contact_number','sup_status'];
}