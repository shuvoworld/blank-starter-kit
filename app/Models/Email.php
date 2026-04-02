<?php

namespace App\Models;

use App\Observers\EmailObserver;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'to',
        'cc',
        'bcc',
        'subject',
        'html',
        'status',
        'attempts',
        'last_attempted_at',
        'successfully_delivered_at',
        'to_user_id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted(): void
    {
        static::observe(EmailObserver::class);
    }
}
