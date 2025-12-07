<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class Supplier extends Model
{
    use HasFactory, Auditable;

    protected $table = 'suppliers';

    protected $fillable = [
        'supplier_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'province',
        'postal_code',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'rating',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'rating' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (!$supplier->supplier_code) {
                $supplier->supplier_code = static::generateUniqueSupplierCode();
            }
            
            // Automatically set created_by and updated_by
            if (auth()->check()) {
                if (!$supplier->created_by) {
                    $supplier->created_by = auth()->id();
                }
                $supplier->updated_by = auth()->id();
            }
        });

        static::updating(function ($supplier) {
            // Automatically set updated_by on every update
            if (auth()->check()) {
                $supplier->updated_by = auth()->id();
            }
        });
    }

    /**
     * Generate a unique supplier code
     */
    private static function generateUniqueSupplierCode(): string
    {
        $prefix = 'SUP';
        $lastSupplier = static::orderBy('id', 'desc')->first();
        
        if ($lastSupplier && $lastSupplier->supplier_code) {
            $lastCode = $lastSupplier->supplier_code;
            $lastNumber = (int) substr($lastCode, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $newCode = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness
        while (static::where('supplier_code', $newCode)->exists()) {
            $newNumber++;
            $newCode = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return $newCode;
    }

    /**
     * Get the user who created this supplier
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this supplier
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the purchase orders for this supplier
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the batches for this supplier
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Get the supplier items for this supplier
     */
    public function supplierItems(): HasMany
    {
        return $this->hasMany(\App\Models\SupplierItem::class);
    }

    /**
     * Get the RTV transactions for this supplier
     */
    public function rtvTransactions(): HasMany
    {
        return $this->hasMany(\App\Models\RtvTransaction::class);
    }

    /**
     * Get the audit logs for this supplier
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'record_id')->where('table_name', 'suppliers');
    }

    /**
     * Accessor for formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Accessor for items count.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->supplierItems->count();
    }

    /**
     * Accessor for display phone (prefer mobile over phone).
     */
    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->mobile ?: $this->phone;
    }

    /**
     * Accessor for full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code
        ]);

        return implode(', ', $parts);
    }

    /**
     * Accessor for contact display
     */
    public function getContactDisplayAttribute(): string
    {
        $contact = $this->contact_person ? $this->contact_person : 'N/A';
        $phone = $this->display_phone ? " - " . $this->display_phone : '';
        
        return $contact . $phone;
    }

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive suppliers
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('supplier_code', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%")
              ->orWhere('tax_id', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('province', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for filtering by rating
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Get the route key for model binding
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Check if supplier can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->supplierItems()->count() === 0 && 
               $this->purchaseOrders()->count() === 0 &&
               $this->rtvTransactions()->count() === 0;
    }

    /**
     * Get the reason why supplier cannot be deleted
     */
    public function getCannotDeleteReason(): ?string
    {
        $itemsCount = $this->supplierItems()->count();
        $purchaseOrdersCount = $this->purchaseOrders()->count();
        $rtvTransactionsCount = $this->rtvTransactions()->count();
        
        if ($itemsCount > 0) {
            return "This supplier has {$itemsCount} associated item(s) and cannot be deleted.";
        }
        
        if ($purchaseOrdersCount > 0) {
            return "This supplier has {$purchaseOrdersCount} associated purchase order(s) and cannot be deleted.";
        }
        
        if ($rtvTransactionsCount > 0) {
            return "This supplier has {$rtvTransactionsCount} associated RTV transaction(s) and cannot be deleted.";
        }
        
        return null;
    }
}