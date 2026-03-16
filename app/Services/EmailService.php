<?php

namespace App\Services;

use App\Services\Logger\EmailLogger;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(private EmailLogger $logger) {}

    /**
     * Send an email and log failures.
     * Successful sends are logged automatically by the LogSentEmail listener.
     *
     * @param  string|array<int, string>  $to
     * @param  string[]  $cc
     * @param  string[]  $bcc
     */
    public function send(
        string|array $to,
        string $subject,
        string $view,
        array $data = [],
        array $cc = [],
        array $bcc = []
    ): void {
        $to = (array) $to;
        $body = null;

        try {
            $body = view($view, $data)->render();

            Mail::send([], [], function ($message) use ($to, $cc, $bcc, $subject, $body): void {
                $message->to($to)->subject($subject)->html($body);

                if ($cc) {
                    $message->cc($cc);
                }

                if ($bcc) {
                    $message->bcc($bcc);
                }
            });
        } catch (\Throwable $e) {
            $this->logger->log([
                'to' => $to,
                'cc' => $cc ?: null,
                'bcc' => $bcc ?: null,
                'subject' => $subject,
                'body' => $body,
                'status' => 'failed',
                'failed_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send a Mailable and log failures.
     * Successful sends are logged automatically by the LogSentEmail listener.
     */
    public function sendMailable(string|array $to, \Illuminate\Mail\Mailable $mailable): void
    {
        try {
            Mail::to((array) $to)->send($mailable);
        } catch (\Throwable $e) {
            $this->logger->log([
                'to' => (array) $to,
                'subject' => $mailable->subject ?? '',
                'mailable_class' => get_class($mailable),
                'status' => 'failed',
                'failed_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
