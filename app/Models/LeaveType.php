<?php

namespace App\Models;

use App\Observers\LeaveTypeObserver;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_paid',
        'requires_approval',
        'max_days_per_year',
        'max_days_per_month',
        'carry_forward',
        'carry_forward_limit',
        'carry_forward_expiry_days',
        'requires_document',
        'min_days_before_request',
        'max_consecutive_days',
        'is_gender_specific',
        'applicable_gender',
        'is_paid_pro_rata',
        'is_active',
        'sort_order',
    ];

    public function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'carry_forward' => 'boolean',
            'requires_document' => 'boolean',
            'is_gender_specific' => 'boolean',
            'is_paid_pro_rata' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Register model event listeners.
     *
     * booted() is called once when the model class is first loaded.
     * Attaching the observer here means every LeaveType create/update/delete
     * will automatically trigger the matching method in LeaveTypeObserver.
     */
    protected static function booted(): void
    {
        static::observe(LeaveTypeObserver::class);
    }
}
