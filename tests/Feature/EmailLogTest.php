<?php

use App\Listeners\LogSentEmail;
use App\Models\EmailLog;
use App\Models\Permission;
use App\Models\User;
use App\Services\EmailService;
use App\Services\Logger\EmailLogger;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'email-logs.view', 'guard_name' => 'web']);
});

// ── EmailLogger ───────────────────────────────────────────────────────────────

it('writes a sent email to the database', function () {
    $logger = app(EmailLogger::class);

    $logger->log([
        'to' => ['recipient@example.com'],
        'subject' => 'Hello',
        'body' => '<p>Test</p>',
        'status' => 'sent',
    ]);

    expect(EmailLog::count())->toBe(1);

    $log = EmailLog::first();
    expect($log->to)->toBe(['recipient@example.com'])
        ->and($log->subject)->toBe('Hello')
        ->and($log->status)->toBe('sent')
        ->and($log->sent_at)->not->toBeNull();
});

it('writes a failed email to the database', function () {
    $logger = app(EmailLogger::class);

    $logger->log([
        'to' => ['recipient@example.com'],
        'subject' => 'Hello',
        'status' => 'failed',
        'failed_reason' => 'Connection refused',
    ]);

    $log = EmailLog::first();
    expect($log->status)->toBe('failed')
        ->and($log->failed_reason)->toBe('Connection refused')
        ->and($log->sent_at)->toBeNull();
});

it('does not throw when logger write fails', function () {
    // Force DB failure by using a non-existent table name
    // We test this by expecting no exception even if write() would fail
    $logger = new class extends \App\Services\Logger\BaseLogger
    {
        protected function write(array $data): void
        {
            throw new \RuntimeException('DB is down');
        }
    };

    // Should not throw — logging must never crash the app
    expect(fn () => $logger->log(['to' => ['x@x.com'], 'subject' => 'test', 'status' => 'sent']))
        ->not->toThrow(\Throwable::class);
});

// ── LogSentEmail listener ─────────────────────────────────────────────────────

it('logs email automatically via the log mail driver', function () {
    config(['mail.default' => 'log']);

    Mail::send([], [], function ($message): void {
        $message->to('test@example.com')->subject('Auto Log Test')->html('<p>body</p>');
    });

    expect(EmailLog::where('subject', 'Auto Log Test')->exists())->toBeTrue();
});

it('listener can be resolved from the container', function () {
    expect(fn () => app(LogSentEmail::class))->not->toThrow(\Throwable::class);
});

// ── EmailService ──────────────────────────────────────────────────────────────

it('EmailService logs failed emails when view does not exist', function () {
    $service = app(EmailService::class);

    try {
        $service->send('fail@example.com', 'Should fail', 'emails.nonexistent-view-xyz');
    } catch (\Throwable) {
        // expected
    }

    expect(EmailLog::where('status', 'failed')->where('subject', 'Should fail')->exists())->toBeTrue();
});

// ── EmailLogController ────────────────────────────────────────────────────────

it('can display email logs index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('email-logs.view');

    actingAs($user)
        ->get(route('email-logs.index'))
        ->assertSuccessful();
});

it('requires permission to view email logs', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('email-logs.index'))
        ->assertForbidden();
});

it('can view email log detail', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('email-logs.view');

    $log = EmailLog::create([
        'to' => ['someone@example.com'],
        'subject' => 'Test Email',
        'body' => '<p>Hello</p>',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    actingAs($user)
        ->get(route('email-logs.show', $log))
        ->assertSuccessful()
        ->assertSee('Test Email')
        ->assertSee('someone@example.com');
});

it('returns datatable json for email logs', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('email-logs.view');

    EmailLog::create([
        'to' => ['john@example.com'],
        'subject' => 'Invoice #001',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->getJson(route('email-logs.index'))
        ->assertSuccessful()
        ->assertJsonFragment(['subject' => 'Invoice #001']);
});

it('can filter datatable by status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('email-logs.view');

    EmailLog::create(['to' => ['a@x.com'], 'subject' => 'Sent One', 'status' => 'sent', 'sent_at' => now()]);
    EmailLog::create(['to' => ['b@x.com'], 'subject' => 'Failed One', 'status' => 'failed', 'failed_reason' => 'Timeout']);

    $response = actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->getJson(route('email-logs.index', ['status' => 'failed']))
        ->assertSuccessful();

    expect($response->json('recordsTotal'))->toBe(1);
});
