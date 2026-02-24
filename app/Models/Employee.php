<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Employee extends Model implements HasMedia
{
    use HasActivityLog, HasFactory, InteractsWithMedia;

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

    /**
     * Define media collections for profile pictures and documents.
     */
    public function registerMediaCollections(): void
    {
        // Profile picture - single file
        $this->addMediaCollection('profile_picture')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->useDisk('public');

        // Resume - single file
        $this->addMediaCollection('resume')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');

        // Certificates - multiple files allowed
        $this->addMediaCollection('certificates')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');

        // Documents - multiple files allowed
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Profile picture conversions
        if ($media?->collection_name === 'profile_picture') {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150)
                ->sharpen(10);

            $this->addMediaConversion('medium')
                ->width(300)
                ->height(300)
                ->sharpen(10);
        }
    }
}
