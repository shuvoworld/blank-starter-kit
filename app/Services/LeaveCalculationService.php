<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveCalculationService
{
    /**
     * Calculate actual leave days excluding weekends and holidays.
     */
    public function calculateActualDays(Carbon $startDate, Carbon $endDate, ?int $countryId = null, ?int $cityId = null): int
    {
        $totalDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip weekends (Saturday/Sunday)
            if (! $this->isWeekend($currentDate)) {
                // Skip holidays
                if (! $this->isHoliday($currentDate, $countryId, $cityId)) {
                    $totalDays++;
                }
            }
            $currentDate->addDay();
        }

        return $totalDays;
    }

    /**
     * Check if a date is a weekend (Saturday or Sunday).
     */
    private function isWeekend(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
    }

    /**
     * Check if a date is a holiday.
     */
    private function isHoliday(Carbon $date, ?int $countryId, ?int $cityId): bool
    {
        $query = Holiday::where('date', $date->format('Y-m-d'))
            ->where('is_active', true);

        // Check global holidays
        $isGlobalHoliday = $query->clone()->where('holiday_type', 'global')->exists();

        if ($isGlobalHoliday) {
            return true;
        }

        // Check regional holidays
        if ($countryId || $cityId) {
            return $query->clone()
                ->where('holiday_type', 'regional')
                ->where(function ($q) use ($countryId, $cityId) {
                    if ($countryId) {
                        $q->where('country_id', $countryId);
                    }
                    if ($cityId) {
                        $q->orWhere('city_id', $cityId);
                    }
                })
                ->exists();
        }

        return false;
    }

    /**
     * Get all holidays in a date range for a location.
     */
    public function getHolidaysInRange(Carbon $startDate, Carbon $endDate, ?int $countryId = null, ?int $cityId = null): Collection
    {
        $query = Holiday::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_active', true)
            ->orderBy('date');

        // Filter by location if specified
        if ($countryId || $cityId) {
            $query->where(function ($q) {
                $q->where('holiday_type', 'global');
            })->orWhere(function ($q) use ($countryId, $cityId) {
                $q->where('holiday_type', 'regional');
                if ($countryId) {
                    $q->where('country_id', $countryId);
                }
                if ($cityId) {
                    $q->where('city_id', $cityId);
                }
            });
        } else {
            // If no location specified, only return global holidays
            $query->where('holiday_type', 'global');
        }

        return $query->get();
    }

    /**
     * Get all weekends in a date range.
     */
    public function getWeekendsInRange(Carbon $startDate, Carbon $endDate): Collection
    {
        $weekends = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($this->isWeekend($currentDate)) {
                $weekends->push($currentDate->copy());
            }
            $currentDate->addDay();
        }

        return $weekends;
    }

    /**
     * Get leave summary including total days, weekends, holidays, and actual leave days.
     */
    public function getLeaveSummary(Carbon $startDate, Carbon $endDate, ?int $countryId = null, ?int $cityId = null): array
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $weekends = $this->getWeekendsInRange($startDate, $endDate);
        $holidays = $this->getHolidaysInRange($startDate, $endDate, $countryId, $cityId);

        // Calculate holidays that fall on weekdays (not on weekends)
        $weekdayHolidays = $holidays->filter(function ($holiday) {
            return ! $this->isWeekend($holiday->date);
        });

        $actualLeaveDays = $this->calculateActualDays($startDate, $endDate, $countryId, $cityId);

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => $totalDays,
            'weekend_count' => $weekends->count(),
            'weekends' => $weekends->map(fn ($date) => $date->format('Y-m-d'))->values(),
            'holiday_count' => $weekdayHolidays->count(),
            'holidays' => $weekdayHolidays->map(fn ($holiday) => [
                'name' => $holiday->name,
                'date' => $holiday->date->format('Y-m-d'),
                'type' => $holiday->holiday_type,
            ])->values(),
            'actual_leave_days' => $actualLeaveDays,
        ];
    }
}
