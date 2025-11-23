<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.item', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('Inventory.purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
        
        return view('Inventory.purchase-orders.create', compact('suppliers', 'items'));
    }

    /**
     * Display the specified purchase order
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier', 
            'items.item.unit', 
            'createdBy',
            'approvedBy'
        ])->findOrFail($id);

        return view('Inventory.purchase-orders.show', compact('purchaseOrder'));
    }
}