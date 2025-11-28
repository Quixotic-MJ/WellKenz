<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Carbon\Carbon;

class Requisition extends Model
{
    use HasFactory;

    protected $table = 'requisitions';

    protected $fillable = [
        'requisition_number',
        'request_date',
        'requested_by',
        'department',
        'purpose',
        'status',
        'total_estimated_value',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'fulfilled_by',
        'fulfilled_at',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'total_estimated_value' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FULFILLED = 'fulfilled';

    // Relationships
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function requisitionItems(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    public function canBeModified(): bool
    {
        return $this->isPending();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800 border-green-200',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800 border-red-200',
            self::STATUS_FULFILLED => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-amber-100 text-amber-800 border-amber-200'
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'fa-check-circle',
            self::STATUS_REJECTED => 'fa-times-circle',
            self::STATUS_FULFILLED => 'fa-check-double',
            default => 'fa-clock'
        };
    }

    public function getProcessingTimeAttribute(): ?float
    {
        if (!$this->approved_at && !$this->rejected_at) {
            return null;
        }

        $endTime = $this->approved_at ?? $this->rejected_at;
        return $this->created_at->diffInHours($endTime);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_estimated_value, 2);
    }

    // Stock analysis methods
    public function getStockAnalysis(): array
    {
        $analysis = [
            'total_items' => $this->requisitionItems->count(),
            'sufficient_stock' => 0,
            'insufficient_stock' => 0,
            'out_of_stock' => 0,
            'high_usage' => 0,
            'items_detail' => []
        ];

        foreach ($this->requisitionItems as $item) {
            $currentStock = $item->currentStockRecord?->current_quantity ?? 0;
            $requestedQty = $item->quantity_requested;
            $stockPercentage = $currentStock > 0 ? round(($requestedQty / $currentStock) * 100, 1) : 0;
            
            $status = 'sufficient';
            if ($currentStock == 0) {
                $status = 'out_of_stock';
                $analysis['out_of_stock']++;
            } elseif ($currentStock < $requestedQty) {
                $status = 'insufficient';
                $analysis['insufficient_stock']++;
            } else {
                $analysis['sufficient_stock']++;
            }

            if ($stockPercentage > 80) {
                $analysis['high_usage']++;
            }

            $analysis['items_detail'][] = [
                'item' => $item,
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQty,
                'stock_percentage' => $stockPercentage,
                'status' => $status,
                'can_fulfill' => $currentStock >= $requestedQty && $currentStock > 0
            ];
        }

        return $analysis;
    }

    public function canBeFulfilled(): bool
    {
        $analysis = $this->getStockAnalysis();
        return $analysis['insufficient_stock'] === 0 && $analysis['out_of_stock'] === 0;
    }

    public function hasHighStockUsage(): bool
    {
        $analysis = $this->getStockAnalysis();
        return $analysis['high_usage'] > 0;
    }

    // Scope methods
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', self::STATUS_FULFILLED);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByRequestedBy($query, int $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopeApprovedToday($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                    ->whereDate('approved_at', Carbon::today());
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('requisition_number', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%")
              ->orWhere('purpose', 'like', "%{$search}%")
              ->orWhereHas('requestedBy', function($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
              })
              ->orWhereHas('requisitionItems.item', function($itemQuery) use ($search) {
                  $itemQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('item_code', 'like', "%{$search}%");
              });
        });
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    // Model events for audit trail
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($requisition) {
            if ($requisition->isDirty('status')) {
                $requisition->auditLog('UPDATE', $requisition->getOriginal('status'), $requisition->status);
            }
        });

        static::created(function ($requisition) {
            $requisition->auditLog('CREATE', null, $requisition->status);
        });
    }

    private function auditLog(string $action, ?string $oldValue, string $newValue): void
    {
        // This would integrate with your audit log system
        // For now, just log to Laravel log
        \Log::info("Requisition {$action}: {$this->requisition_number} - Status: {$oldValue} -> {$newValue}");
    }
}