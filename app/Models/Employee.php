<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'department',
        'position',
        'salary',
        'hire_date',
        'status',
    ];

    public function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartment(Builder $query, ?string $department): Builder
    {
        if (empty($department)) {
            return $query;
        }

        return $query->where('department', $department);
    }

    /**
     * Scope a query to filter by position.
     */
    public function scopeByPosition(Builder $query, ?string $position): Builder
    {
        if (empty($position)) {
            return $query;
        }

        return $query->where('position', $position);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by hire date range.
     */
    public function scopeByHireDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if (empty($from) && empty($to)) {
            return $query;
        }

        if (!empty($from)) {
            $query->where('hire_date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('hire_date', '<=', $to);
        }

        return $query;
    }
}
