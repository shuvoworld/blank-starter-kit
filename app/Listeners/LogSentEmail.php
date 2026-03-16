<?php

namespace App\Listeners;

use App\Services\Logger\EmailLogger;
use Illuminate\Mail\Events\MessageSent;

class LogSentEmail
{
    public function __construct(private EmailLogger $logger) {}

    public function handle(MessageSent $event): void
    {
        $message = $event->message;

        $toAddresses = array_map(fn ($addr) => $addr->getAddress(), $message->getTo() ?? []);
        $ccAddresses = array_map(fn ($addr) => $addr->getAddress(), $message->getCc() ?? []);
        $bccAddresses = array_map(fn ($addr) => $addr->getAddress(), $message->getBcc() ?? []);

        $mailableClass = null;
        if (isset($event->data['message']) && is_object($event->data['message'])) {
            $mailableClass = get_class($event->data['message']);
        }

        $this->logger->log([
            'to' => $toAddresses,
            'cc' => $ccAddresses ?: null,
            'bcc' => $bccAddresses ?: null,
            'subject' => $message->getSubject() ?? '',
            'body' => $message->getHtmlBody() ?? $message->getTextBody(),
            'mailable_class' => $mailableClass,
            'status' => 'sent',
        ]);
    }
}
