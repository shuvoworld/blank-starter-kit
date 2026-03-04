<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasActivityLog, HasAuditFields, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'holiday_type',
        'country_id',
        'city_id',
        'is_recurring',
        'notes',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Register model event listeners.
     *
     * booted() is called once when the model class is first loaded.
     * Attaching the observer here means every Holiday create/update/delete
     * will automatically trigger the matching method in HolidayObserver.
     */
    protected static function booted(): void
    {
        static::observe(\App\Observers\HolidayObserver::class);
    }

    public function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function country()
    {
        return $this->belongsTo(\Milenmk\LaravelLocations\Models\Country::class);
    }

    public function city()
    {
        return $this->belongsTo(\Milenmk\LaravelLocations\Models\City::class);
    }
}
