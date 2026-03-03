<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasActivityLog, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * Get the users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        $pivotClass = config('permission.models.role');

        return $this->belongsToMany(
            config('auth.providers.users.model'),
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key') ?? 'role_id',
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Scope a query to only include roles without a specific role.
     */
    public function scopeWithoutSuperuser($query)
    {
        return $query->where('name', '!=', 'Superuser');
    }

    /**
     * Check if role is superuser.
     */
    public function isSuperuser(): bool
    {
        return $this->name === 'Superuser';
    }
}
