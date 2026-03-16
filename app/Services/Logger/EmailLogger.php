<?php

namespace App\Services\Logger;

use App\Models\EmailLog;

class EmailLogger extends BaseLogger
{
    protected function write(array $data): void
    {
        EmailLog::create([
            'to' => $data['to'],
            'cc' => $data['cc'] ?? null,
            'bcc' => $data['bcc'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'] ?? null,
            'mailable_class' => $data['mailable_class'] ?? null,
            'status' => $data['status'],
            'failed_reason' => $data['failed_reason'] ?? null,
            'sent_at' => $data['status'] === 'sent' ? now() : null,
        ]);
    }
}
