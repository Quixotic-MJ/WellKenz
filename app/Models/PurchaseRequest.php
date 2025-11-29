<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'request_date',
        'requested_by',
        'department',
        'priority',
        'status',
        'total_estimated_cost',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'notes'
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_estimated_cost' => 'decimal:2',
    ];

    // Relationships
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors & Mutators
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Draft</span>',
            'pending' => '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Pending</span>',
            'approved' => '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Approved</span>',
            'rejected' => '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Rejected</span>',
            'converted' => '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Converted</span>',
            default => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Unknown</span>'
        };
    }

    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            'low' => '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Low</span>',
            'normal' => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Normal</span>',
            'high' => '<span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">High</span>',
            'urgent' => '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Urgent</span>',
            default => '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Normal</span>'
        };
    }

    public function getIsHighValueAttribute()
    {
        return $this->total_estimated_cost >= 10000;
    }

    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total_estimated_cost, 2);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getStatusColorClass()
    {
        return match($this->status) {
            'draft' => 'border-gray-300',
            'pending' => 'border-yellow-300',
            'approved' => 'border-green-300',
            'rejected' => 'border-red-300',
            'converted' => 'border-blue-300',
            default => 'border-gray-300'
        };
    }

    /**
     * Get the approval history with user details
     */
    public function getApprovalHistoryAttribute()
    {
        $history = [];
        
        // Add creation record
        $history[] = [
            'action' => 'created',
            'user' => $this->requestedBy->name ?? 'Unknown User',
            'timestamp' => $this->created_at->format('M d, Y h:i A'),
            'details' => 'Purchase request created'
        ];

        // Add approval/rejection record if applicable
        if ($this->approved_by && $this->approved_at) {
            $action = $this->status === 'approved' ? 'approved' : 'rejected';
            $history[] = [
                'action' => $action,
                'user' => $this->approvedBy->name ?? 'Unknown User',
                'timestamp' => $this->approved_at->format('M d, Y h:i A'),
                'details' => "Purchase request {$action}"
            ];
        }

        return collect($history)->sortByDesc('timestamp')->values();
    }

    /**
     * Get bulk action capabilities
     */
    public function getBulkActionCapabilitiesAttribute()
    {
        return [
            'can_bulk_approve' => $this->status === 'pending' && auth()->user()->hasRole(['supervisor', 'admin']),
            'can_bulk_reject' => $this->status === 'pending' && auth()->user()->hasRole(['supervisor', 'admin']),
            'can_export' => true,
            'can_modify' => $this->canBeModified(),
            'can_cancel' => in_array($this->status, ['pending', 'draft'])
        ];
    }

    /**
     * Get search and filter capabilities
     */
    public static function getFilterOptions()
    {
        return [
            'priorities' => [
                'urgent' => 'Urgent',
                'high' => 'High', 
                'normal' => 'Normal',
                'low' => 'Low'
            ],
            'statuses' => [
                'draft' => 'Draft',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'converted' => 'Converted'
            ],
            'departments' => self::distinct()->pluck('department')->filter()->sort()->values(),
            'risk_levels' => [
                'high' => 'High Risk (≥₱50K or Urgent)',
                'medium' => 'Medium Risk (₱20K-₱49K or High Priority)',
                'low' => 'Low Risk (<₱20K and Normal/Low Priority)'
            ]
        ];
    }

    /**
     * Get export formats supported
     */
    public function getExportFormatsAttribute()
    {
        return [
            'pdf' => 'PDF Report',
            'excel' => 'Excel Spreadsheet',
            'csv' => 'CSV File',
            'print' => 'Print View'
        ];
    }

    /**
     * Get related purchase orders
     */
    public function getRelatedPurchaseOrdersAttribute()
    {
        return \App\Models\PurchaseOrder::whereHas('purchaseOrderItems', function($query) {
            $query->whereIn('item_id', $this->purchaseRequestItems->pluck('item_id'));
        })->get();
    }

    /**
     * Get supplier recommendations based on items
     */
    public function getSupplierRecommendationsAttribute()
    {
        $supplierItems = \App\Models\SupplierItem::whereIn('item_id', $this->purchaseRequestItems->pluck('item_id'))
            ->with('supplier')
            ->get()
            ->groupBy('supplier_id')
            ->map(function($items) {
                $supplier = $items->first()->supplier;
                return [
                    'supplier' => $supplier,
                    'items_count' => $items->count(),
                    'total_estimated_cost' => $items->sum('unit_price') * $items->first()->quantity_requested ?? 0,
                    'average_rating' => $supplier->rating ?? 0,
                    'preferred' => $items->where('is_preferred', true)->count() > 0
                ];
            })
            ->sortByDesc('preferred')
            ->sortByDesc('average_rating')
            ->values();

        return $supplierItems;
    }

    /**
     * Check if request can be converted to purchase order
     */
    public function getCanConvertToPoAttribute()
    {
        return $this->status === 'approved' && 
               $this->purchaseRequestItems->count() > 0 &&
               auth()->user()->hasRole(['purchasing', 'admin']);
    }

    /**
     * Get budget impact analysis
     */
    public function getBudgetImpactAttribute()
    {
        $monthlyBudget = 100000; // This should come from system settings
        $thisMonthSpend = PurchaseRequest::where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('total_estimated_cost');
        
        $newTotal = $thisMonthSpend + $this->total_estimated_cost;
        $percentage = ($newTotal / $monthlyBudget) * 100;
        
        return [
            'monthly_budget' => $monthlyBudget,
            'current_spend' => $thisMonthSpend,
            'projected_total' => $newTotal,
            'budget_percentage' => round($percentage, 1),
            'impact_level' => $percentage > 90 ? 'critical' : ($percentage > 75 ? 'high' : ($percentage > 50 ? 'medium' : 'low'))
        ];
    }

    /**
     * Get compliance and audit information
     */
    public function getComplianceInfoAttribute()
    {
        return [
            'requires_approval' => $this->total_estimated_cost >= 5000 || $this->priority === 'urgent',
            'approval_chain' => $this->total_estimated_cost >= 25000 ? ['supervisor', 'admin'] : ['supervisor'],
            'audit_trail' => true,
            'compliance_status' => $this->status === 'pending' ? 'pending_review' : 'compliant',
            'documentation_complete' => !empty($this->notes)
        ];
    }

    /**
     * Scope for requests needing supervisor approval
     */
    public function scopeRequiringSupervisorApproval($query)
    {
        return $query->where(function($q) {
            $q->where('total_estimated_cost', '>=', 5000)
              ->orWhere('priority', 'urgent');
        })->where('status', 'pending');
    }

    /**
     * Scope for requests by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for requests with specific risk level
     */
    public function scopeByRiskLevel($query, $riskLevel)
    {
        return match($riskLevel) {
            'high' => $query->where(function($q) {
                $q->where('total_estimated_cost', '>=', 50000)
                  ->orWhere('priority', 'urgent');
            }),
            'medium' => $query->where(function($q) {
                $q->whereBetween('total_estimated_cost', [20000, 49999])
                  ->orWhere('priority', 'high');
            }),
            'low' => $query->where('total_estimated_cost', '<', 20000)
                        ->whereNotIn('priority', ['urgent', 'high']),
            default => $query
        };
    }

    /**
     * PostgreSQL compatible scope for overdue requests
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->whereRaw("created_at < NOW() - INTERVAL '7 days'");
    }

    /**
     * Get formatted request date
     */
    public function getFormattedRequestDateAttribute()
    {
        return $this->request_date->format('M d, Y');
    }

    /**
     * Get time ago for request date
     */
    public function getTimeAgoRequestDateAttribute()
    {
        return $this->request_date->diffForHumans();
    }

    /**
     * Check if the request can be modified
     */
    public function canBeModified()
    {
        return $this->status === 'pending' && $this->requested_by === auth()->id();
    }

    /**
     * Check if the request can be approved by current user
     */
    public function canBeApproved()
    {
        return $this->status === 'pending' && auth()->user()->hasRole(['supervisor', 'admin']);
    }

    /**
     * Get priority level for sorting
     */
    public function getPriorityLevelAttribute()
    {
        return match($this->priority) {
            'urgent' => 1,
            'high' => 2,
            'normal' => 3,
            'low' => 4,
            default => 3
        };
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCountAttribute()
    {
        return $this->purchaseRequestItems->count();
    }

    /**
     * Get unique departments involved
     */
    public function getInvolvedDepartmentsAttribute()
    {
        return collect([$this->department])->filter()->unique();
    }

    /**
     * Get risk level based on amount and priority
     */
    public function getRiskLevelAttribute()
    {
        if ($this->total_estimated_cost >= 50000) return 'high';
        if ($this->total_estimated_cost >= 20000) return 'medium';
        if ($this->priority === 'urgent') return 'medium';
        return 'low';
    }

    /**
     * Get next possible status
     */
    public function getNextPossibleStatusAttribute()
    {
        return match($this->status) {
            'draft' => ['pending'],
            'pending' => ['approved', 'rejected'],
            'approved' => ['converted'],
            'rejected' => ['draft'],
            'converted' => [],
            default => []
        };
    }

    /**
     * Get approval timeline in days
     */
    public function getApprovalTimelineAttribute()
    {
        if (!$this->approved_at) return null;
        
        return $this->created_at->diffInDays($this->approved_at);
    }

    /**
     * Check if overdue (for pending requests)
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'pending') return false;
        
        $daysSinceCreation = $this->created_at->diffInDays(now());
        $threshold = match($this->priority) {
            'urgent' => 1,
            'high' => 3,
            'normal' => 7,
            'low' => 14,
            default => 7
        };
        
        return $daysSinceCreation > $threshold;
    }

    /**
     * Get priority icon
     */
    public function getPriorityIconAttribute()
    {
        return match($this->priority) {
            'urgent' => 'fas fa-exclamation-triangle text-red-500',
            'high' => 'fas fa-arrow-up text-orange-500',
            'normal' => 'fas fa-minus text-gray-500',
            'low' => 'fas fa-arrow-down text-blue-500',
            default => 'fas fa-minus text-gray-500'
        };
    }

    /**
     * Get urgency badge
     */
    public function getUrgencyBadgeAttribute()
    {
        if (!$this->is_overdue) {
            return match($this->priority) {
                'urgent' => '<span class="px-2 py-1 bg-red-500 text-white text-xs rounded-full animate-pulse">URGENT</span>',
                'high' => '<span class="px-2 py-1 bg-orange-500 text-white text-xs rounded-full">HIGH</span>',
                'normal' => '<span class="px-2 py-1 bg-gray-500 text-white text-xs rounded-full">NORMAL</span>',
                'low' => '<span class="px-2 py-1 bg-blue-500 text-white text-xs rounded-full">LOW</span>',
                default => '<span class="px-2 py-1 bg-gray-500 text-white text-xs rounded-full">NORMAL</span>'
            };
        }
        
        return '<span class="px-2 py-1 bg-red-600 text-white text-xs rounded-full animate-bounce">OVERDUE</span>';
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'draft' => 'fas fa-edit text-gray-500',
            'pending' => 'fas fa-clock text-yellow-500',
            'approved' => 'fas fa-check-circle text-green-500',
            'rejected' => 'fas fa-times-circle text-red-500',
            'converted' => 'fas fa-file-export text-blue-500',
            default => 'fas fa-question-circle text-gray-500'
        };
    }

    /**
     * Get progress percentage for the request lifecycle
     */
    public function getProgressPercentageAttribute()
    {
        return match($this->status) {
            'draft' => 10,
            'pending' => 25,
            'approved' => 75,
            'rejected' => 100,
            'converted' => 100,
            default => 0
        };
    }

    /**
     * Get estimated approval time based on priority
     */
    public function getEstimatedApprovalTimeAttribute()
    {
        return match($this->priority) {
            'urgent' => '1 hour',
            'high' => '4 hours', 
            'normal' => '1-2 days',
            'low' => '3-5 days',
            default => '1-2 days'
        };
    }

    /**
     * Scope for high value requests
     */
    public function scopeHighValue($query, $amount = 10000)
    {
        return $query->where('total_estimated_cost', '>=', $amount);
    }

    /**
     * Scope for requests by department
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the latest activity timestamp
     */
    public function getLastActivityAttribute()
    {
        return $this->approved_at ?? $this->created_at;
    }

    /**
     * Check if request requires supervisor approval
     */
    public function getRequiresSupervisorApprovalAttribute()
    {
        return $this->total_estimated_cost >= 5000 || $this->priority === 'urgent';
    }
}