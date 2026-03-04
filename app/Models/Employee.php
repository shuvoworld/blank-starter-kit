<?php

namespace App\Models;

use App\Observers\EmployeeObserver;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Milenmk\LaravelLocations\Models\Area;
use Milenmk\LaravelLocations\Models\City;
use Milenmk\LaravelLocations\Models\Country;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Employee extends Model implements HasMedia
{
    use HasActivityLog, HasAuditFields, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'department',
        'department_id',
        'position',
        'designation_id',
        'salary',
        'hire_date',
        'status',
        'country_id',
        'city_id',
        'area_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    /**
     * Get the department that owns the employee.
     */
    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the designation that owns the employee.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    /**
     * Get the country that owns the employee.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get the city (state) that owns the employee.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the area that owns the employee.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
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
     * Scope a query to filter by department ID.
     */
    public function scopeByDepartmentId(Builder $query, ?int $departmentId): Builder
    {
        if (empty($departmentId)) {
            return $query;
        }

        return $query->where('department_id', $departmentId);
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
     * Scope a query to filter by designation ID.
     */
    public function scopeByDesignationId(Builder $query, ?int $designationId): Builder
    {
        if (empty($designationId)) {
            return $query;
        }

        return $query->where('designation_id', $designationId);
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

        if (! empty($from)) {
            $query->where('hire_date', '>=', $from);
        }

        if (! empty($to)) {
            $query->where('hire_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope a query to filter by country.
     */
    public function scopeByCountry(Builder $query, ?int $countryId): Builder
    {
        if (empty($countryId)) {
            return $query;
        }

        return $query->where('country_id', $countryId);
    }

    /**
     * Scope a query to filter by city (state).
     */
    public function scopeByCity(Builder $query, ?int $cityId): Builder
    {
        if (empty($cityId)) {
            return $query;
        }

        return $query->where('city_id', $cityId);
    }

    /**
     * Scope a query to filter by area.
     */
    public function scopeByArea(Builder $query, ?int $areaId): Builder
    {
        if (empty($areaId)) {
            return $query;
        }

        return $query->where('area_id', $areaId);
    }

    /**
     * Register model event listeners.
     *
     * booted() is called once when the model class is first loaded.
     * Attaching the observer here means every Employee create/update/delete
     * will automatically trigger the matching method in EmployeeObserver.
     */
    protected static function booted(): void
    {
        static::observe(EmployeeObserver::class);
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
    public function registerMediaConversions(?Media $media = null): void
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
