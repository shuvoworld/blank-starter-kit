<?php

namespace App\Models;

use App\Observers\DesignationObserver;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        $isActive = $status === 'active' ? true : ($status === 'inactive' ? false : null);

        if ($isActive !== null) {
            return $query->where('is_active', $isActive);
        }

        return $query;
    }

    /**
     * Register model event listeners.
     *
     * booted() is called once when the model class is first loaded.
     * Attaching the observer here means every Designation create/update/delete
     * will automatically trigger the matching method in DesignationObserver.
     */
    protected static function booted(): void
    {
        static::observe(DesignationObserver::class);
    }
}
