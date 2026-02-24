<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LandingPageSection extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'key',
        'section',
        'type',
        'label',
        'value',
        'options',
        'sort_order',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to filter by section.
     */
    public function scopeBySection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }

    /**
     * Scope a query to filter by active status.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get value by key for a section.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $section = static::where('key', $key)->active()->first();

        if (!$section) {
            return $default;
        }

        return match ($section->type) {
            'boolean' => (bool) $section->value,
            'json' => json_decode($section->value, true),
            'number' => (int) $section->value,
            default => $section->value,
        };
    }

    /**
     * Get all sections as key-value pairs.
     *
     * @return array<string, mixed>
     */
    public static function getAllAsArray(): array
    {
        return static::active()->ordered()->get()
            ->mapWithKeys(fn (self $section) => [$section->key => $section->value])
            ->toArray();
    }

    /**
     * Get sections grouped by section name.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getGroupedBySection(): array
    {
        return static::active()->ordered()->get()
            ->groupBy('section')
            ->map(fn ($items) => $items->pluck('value', 'key')->toArray())
            ->toArray();
    }
}
