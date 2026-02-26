<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;

class LeaveBalanceService
{
    /**
     * Get or create a leave balance for a user, leave type, and year.
     */
    public function getOrCreateBalance(int $userId, int $leaveTypeId, int $year): LeaveBalance
    {
        $balance = LeaveBalance::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (! $balance) {
            $leaveType = LeaveType::findOrFail($leaveTypeId);

            // Calculate pro-rata entitlement if applicable
            $entitlement = $this->calculateEntitlement($userId, $leaveType, $year);

            $balance = LeaveBalance::create([
                'user_id' => $userId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'total_entitlement' => $entitlement,
                'taken_days' => 0,
                'remaining_days' => $entitlement,
                'pending_days' => 0,
            ]);
        }

        return $balance;
    }

    /**
     * Get all leave balances for a user in a specific year.
     */
    public function getUserBalances(int $userId, int $year): \Illuminate\Database\Eloquent\Collection
    {
        $activeLeaveTypes = LeaveType::active()->pluck('id');

        $balances = LeaveBalance::where('user_id', $userId)
            ->where('year', $year)
            ->whereIn('leave_type_id', $activeLeaveTypes)
            ->with('leaveType')
            ->get();

        // Create balances for active leave types that don't exist yet
        $existingTypeIds = $balances->pluck('leave_type_id')->toArray();
        $missingTypeIds = $activeLeaveTypes->diff($existingTypeIds);

        foreach ($missingTypeIds as $leaveTypeId) {
            $balance = $this->getOrCreateBalance($userId, $leaveTypeId, $year);
            $balances->push($balance);
        }

        return $balances->sortBy('leaveType.sort_order');
    }

    /**
     * Update pending days count for a balance.
     */
    public function updatePendingDays(LeaveBalance $balance): void
    {
        $pendingDays = LeaveRequest::where('user_id', $balance->user_id)
            ->where('leave_type_id', $balance->leave_type_id)
            ->where('year', $balance->year)
            ->pending()
            ->sum('total_days');

        $balance->setPendingDays($pendingDays);
    }

    /**
     * Process an approved leave request: update taken days and remaining balance.
     */
    public function approveRequest(LeaveBalance $balance, int $days): void
    {
        $balance->addTakenDays($days);

        // Recalculate pending days after approval
        $this->updatePendingDays($balance);
    }

    /**
     * Calculate entitlement based on leave type rules and user join date.
     */
    protected function calculateEntitlement(int $userId, LeaveType $leaveType, int $year): int
    {
        $entitlement = $leaveType->max_days_per_year ?? 0;

        if ($entitlement === 0) {
            return 0;
        }

        // Apply pro-rata calculation for mid-year joiners
        if ($leaveType->is_paid_pro_rata) {
            $user = \App\Models\User::find($userId);

            if ($user && $user->employee) {
                $joinDate = $user->employee->join_date ?? $user->created_at;

                if ($joinDate->year === $year && $joinDate->month !== 1) {
                    // Calculate pro-rata based on months remaining in the year
                    $monthsWorked = 12 - $joinDate->month + 1;
                    $proRataRatio = $monthsWorked / 12;
                    $entitlement = (int) round($entitlement * $proRataRatio);
                }
            }
        }

        return $entitlement;
    }

    /**
     * Initialize balances for a user for all active leave types.
     */
    public function initializeUserBalances(int $userId, int $year): void
    {
        $activeLeaveTypes = LeaveType::active()->get();

        foreach ($activeLeaveTypes as $leaveType) {
            $this->getOrCreateBalance($userId, $leaveType->id, $year);
        }
    }

    /**
     * Carry forward unused leave from previous year.
     */
    public function carryForwardLeave(int $userId, int $fromYear, int $toYear): void
    {
        $eligibleLeaveTypes = LeaveType::where('carry_forward', true)
            ->where('is_active', true)
            ->get();

        foreach ($eligibleLeaveTypes as $leaveType) {
            $previousBalance = LeaveBalance::where('user_id', $userId)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $fromYear)
                ->first();

            if (! $previousBalance || $previousBalance->remaining_days <= 0) {
                continue;
            }

            $currentBalance = $this->getOrCreateBalance($userId, $leaveType->id, $toYear);

            // Calculate carry forward amount (respect limits)
            $carryForwardAmount = min(
                $previousBalance->remaining_days,
                $leaveType->carry_forward_limit ?? $previousBalance->remaining_days
            );

            if ($carryForwardAmount > 0) {
                $currentBalance->update([
                    'total_entitlement' => $currentBalance->total_entitlement + $carryForwardAmount,
                    'remaining_days' => $currentBalance->remaining_days + $carryForwardAmount,
                ]);
            }
        }
    }

    /**
     * Get leave summary for a user across all leave types.
     */
    public function getLeaveSummary(int $userId, int $year): array
    {
        $balances = $this->getUserBalances($userId, $year);

        $totalEntitlement = $balances->sum('total_entitlement');
        $totalTaken = $balances->sum('taken_days');
        $totalRemaining = $balances->sum('remaining_days');
        $totalPending = $balances->sum('pending_days');

        return [
            'year' => $year,
            'total_entitlement' => $totalEntitlement,
            'total_taken' => $totalTaken,
            'total_remaining' => $totalRemaining,
            'total_pending' => $totalPending,
            'usage_percentage' => $totalEntitlement > 0 ? round(($totalTaken / $totalEntitlement) * 100, 2) : 0,
            'by_type' => $balances->map(function ($balance) {
                return [
                    'leave_type' => $balance->leaveType->name,
                    'code' => $balance->leaveType->code,
                    'entitlement' => $balance->total_entitlement,
                    'taken' => $balance->taken_days,
                    'remaining' => $balance->remaining_days,
                    'pending' => $balance->pending_days,
                    'usage_percentage' => $balance->usage_percentage,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get aggregated leave summary for all users (admin report).
     */
    public function getAggregatedSummary(int $year): array
    {
        $balances = LeaveBalance::where('year', $year)
            ->with(['user', 'leaveType'])
            ->get()
            ->groupBy('user_id');

        $summary = [];

        foreach ($balances as $userId => $userBalances) {
            $user = $userBalances->first()->user;

            $summary[] = [
                'user_id' => $userId,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'total_entitlement' => $userBalances->sum('total_entitlement'),
                'total_taken' => $userBalances->sum('taken_days'),
                'total_remaining' => $userBalances->sum('remaining_days'),
                'total_pending' => $userBalances->sum('pending_days'),
                'by_type' => $userBalances->map(function ($balance) {
                    return [
                        'leave_type' => $balance->leaveType->name,
                        'entitlement' => $balance->total_entitlement,
                        'taken' => $balance->taken_days,
                        'remaining' => $balance->remaining_days,
                        'pending' => $balance->pending_days,
                    ];
                })->toArray(),
            ];
        }

        return $summary;
    }
}
