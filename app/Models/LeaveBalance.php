<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasActivityLog, HasAuditFields, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'total_entitlement',
        'taken_days',
        'remaining_days',
        'pending_days',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Register model event listeners.
     *
     * booted() is called once when the model class is first loaded.
     * Attaching the observer here means every LeaveBalance create/update/delete
     * will automatically trigger the matching method in LeaveBalanceObserver.
     */
    protected static function booted(): void
    {
        static::observe(\App\Observers\LeaveBalanceObserver::class);
    }

    public function casts(): array
    {
        return [];
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByLeaveType(Builder $query, int $leaveTypeId): Builder
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Update balance based on approved leave days.
     */
    public function addTakenDays(int $days): void
    {
        $this->taken_days += $days;
        $this->remaining_days = max(0, $this->total_entitlement - $this->taken_days);
        $this->save();
    }

    /**
     * Update pending days count.
     */
    public function setPendingDays(int $days): void
    {
        $this->pending_days = $days;
        $this->save();
    }

    /**
     * Check if user has sufficient balance for requested days.
     */
    public function hasSufficientBalance(int $requestedDays): bool
    {
        return $this->remaining_days >= $requestedDays;
    }

    /**
     * Get the usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_entitlement === 0) {
            return 0;
        }

        return round(($this->taken_days / $this->total_entitlement) * 100, 2);
    }

    /**
     * Get the remaining percentage.
     */
    public function getRemainingPercentageAttribute(): float
    {
        return 100 - $this->usage_percentage;
    }
}
