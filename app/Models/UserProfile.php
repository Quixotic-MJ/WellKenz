<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'phone',
        'address',
        'date_of_birth',
        'hire_date',
        'department',
        'position',
        'salary',
        'emergency_contact_name',
        'emergency_contact_phone',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->user->name ?? 'N/A';
    }

    /**
     * Get the initials of the user's name.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $name = $this->user->name ?? '';
        if (empty($name)) {
            return 'U';
        }
        
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get the hire date formatted for display.
     *
     * @return string
     */
    public function getFormattedHireDateAttribute()
    {
        return $this->hire_date ? $this->hire_date->format('M j, Y') : 'N/A';
    }

    /**
     * Get the time since hire in years and months.
     *
     * @return string
     */
    public function getTimeWithCompanyAttribute()
    {
        if (!$this->hire_date) {
            return 'N/A';
        }
        
        $now = now();
        $years = $now->diffInYears($this->hire_date);
        $months = $now->diffInMonths($this->hire_date) % 12;
        
        if ($years > 0 && $months > 0) {
            return "{$years}y {$months}m";
        } elseif ($years > 0) {
            return "{$years}y";
        } else {
            return "{$months}m";
        }
    }
}