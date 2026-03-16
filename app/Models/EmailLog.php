<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'mailable_class',
        'status',
        'failed_reason',
        'sent_at',
    ];

    public function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
