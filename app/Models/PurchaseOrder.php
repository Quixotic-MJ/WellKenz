<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'payment_terms',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isOrdered()
    {
        return in_array($this->status, ['sent', 'confirmed']);
    }

    public function isDelivered()
    {
        return $this->status === 'completed';
    }

    public function sourcePurchaseRequests()
    {
        return $this->belongsToMany(PurchaseRequest::class, 'purchase_request_purchase_order_link', 'purchase_order_id', 'purchase_request_id');
    }

    public function getAverageDeliveryTimeInDays()
    {
        if ($this->actual_delivery_date && $this->order_date) {
            return $this->order_date->diffInDays($this->actual_delivery_date);
        }
        return null;
    }

    /**
     * Get status badge with dynamic styling
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Draft</span>',
            'sent' => '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Sent</span>',
            'confirmed' => '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Confirmed</span>',
            'partial' => '<span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">Partial</span>',
            'completed' => '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Completed</span>',
            'cancelled' => '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Cancelled</span>',
            default => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Unknown</span>'
        };
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->grand_total ?? 0, 2);
    }

    /**
     * Get delivery status
     */
    public function getDeliveryStatusAttribute()
    {
        if (!$this->expected_delivery_date) {
            return ['status' => 'not_set', 'text' => 'Not Set', 'class' => 'text-gray-400'];
        }

        if ($this->actual_delivery_date) {
            return ['status' => 'delivered', 'text' => 'Delivered', 'class' => 'text-green-600'];
        }

        if ($this->expected_delivery_date->isPast()) {
            return ['status' => 'overdue', 'text' => 'Overdue', 'class' => 'text-red-600'];
        }

        $daysUntilDelivery = now()->diffInDays($this->expected_delivery_date);
        if ($daysUntilDelivery <= 1) {
            return ['status' => 'urgent', 'text' => 'Due Tomorrow', 'class' => 'text-orange-600'];
        }

        return ['status' => 'scheduled', 'text' => 'Scheduled', 'class' => 'text-blue-600'];
    }

    /**
     * Get action capabilities based on status and user permissions
     */
    public function getActionCapabilitiesAttribute()
    {
        $user = auth()->user();
        
        return [
            'can_edit' => $this->status === 'draft' && $user->hasRole(['purchasing', 'admin']),
            'can_submit' => $this->status === 'draft' && $user->hasRole(['purchasing', 'admin']),
            'can_view' => true,
            'can_delete' => $this->status === 'draft' && $user->hasRole(['purchasing', 'admin']),
            'can_cancel' => in_array($this->status, ['sent', 'confirmed']) && $user->hasRole(['purchasing', 'admin']),
        ];
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCountAttribute()
    {
        return $this->purchaseOrderItems->count();
    }

    /**
     * Get total quantity ordered
     */
    public function getTotalQuantityOrderedAttribute()
    {
        return $this->purchaseOrderItems->sum('quantity_ordered');
    }

    /**
     * Get total quantity received
     */
    public function getTotalQuantityReceivedAttribute()
    {
        return $this->purchaseOrderItems->sum('quantity_received');
    }

    /**
     * Check if order is overdue for delivery
     */
    public function getIsOverdueAttribute()
    {
        return $this->expected_delivery_date && 
               $this->expected_delivery_date->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get priority based on delivery date
     */
    public function getPriorityAttribute()
    {
        if (!$this->expected_delivery_date) {
            return 'low';
        }

        if ($this->is_overdue) {
            return 'urgent';
        }

        $daysUntilDelivery = now()->diffInDays($this->expected_delivery_date);
        
        if ($daysUntilDelivery <= 1) {
            return 'high';
        }

        if ($daysUntilDelivery <= 3) {
            return 'normal';
        }

        return 'low';
    }

    /**
     * Get filtered and sorted purchase orders for display
     */
    public static function getDraftOrdersWithFilters($filters = [])
    {
        $query = self::with(['supplier', 'purchaseOrderItems.item', 'createdBy', 'sourcePurchaseRequests'])
            ->where('status', 'draft');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('supplier_code', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['date_filter'])) {
            $now = now();
            switch ($filters['date_filter']) {
                case 'today':
                    $query->whereDate('order_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('order_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('order_date', $now->month)
                          ->whereYear('order_date', $now->year);
                    break;
            }
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get available suppliers for filter dropdown
     */
    public static function getAvailableSuppliers()
    {
        return Supplier::where('is_active', true)
            ->whereHas('purchaseOrders', function($query) {
                $query->where('status', 'draft');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get bulk action capabilities
     */
    public function getBulkActionCapabilitiesAttribute()
    {
        $user = auth()->user();
        return [
            'can_bulk_submit' => $user->hasRole(['purchasing', 'admin']),
            'can_bulk_delete' => $user->hasRole(['purchasing', 'admin']),
            'can_bulk_edit' => $user->hasRole(['purchasing', 'admin']),
        ];
    }
}