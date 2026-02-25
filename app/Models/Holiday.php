<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasActivityLog, HasFactory;

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
    ];

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
