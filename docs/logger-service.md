# Logger Service

A structured reference for the Logger utility layer — how it is built, how `EmailLogger` uses it, and the full step-by-step guide to implementing `ApiLogger` (or any future logger).

---

## Table of Contents

- [Architecture](#architecture)
- [BaseLogger](#baselogger)
  - [Contract](#contract)
  - [Fault-isolation guarantee](#fault-isolation-guarantee)
- [EmailLogger — complete implementation](#emaillogger--complete-implementation)
  - [How it plugs into EmailService](#how-it-plugs-into-emailservice)
  - [How it plugs into LogSentEmail listener](#how-it-plugs-into-logsentemail-listener)
  - [Listener auto-discovery](#listener-auto-discovery)
- [ApiLogger — full implementation guide](#apilogger--full-implementation-guide)
  - [Step 1 — Migration](#step-1--migration)
  - [Step 2 — Model](#step-2--model)
  - [Step 3 — Implement ApiLogger::write()](#step-3--implement-apiloggerwrite)
  - [Step 4 — Create ApiService](#step-4--create-apiservice)
  - [Step 5 — Create the listener (optional)](#step-5--create-the-listener-optional)
  - [Step 6 — Permission seeder](#step-6--permission-seeder)
  - [Step 7 — Admin UI](#step-7--admin-ui)
  - [Step 8 — Routes](#step-8--routes)
- [Adding a New Logger Type](#adding-a-new-logger-type)
- [Design Rules](#design-rules)

---

## Architecture

```
app/Services/Logger/
├── BaseLogger.php      ← abstract: defines log() + abstract write()
├── EmailLogger.php     ← writes to email_logs table
└── ApiLogger.php       ← writes to api_logs table (stub — see guide below)

app/Services/
└── EmailService.php    ← injects EmailLogger; uses it to log failures

app/Listeners/
└── LogSentEmail.php    ← injects EmailLogger; logs every successful send
```

Every concrete logger follows the same pattern:

```
Caller
  └── ConcreteLogger::log(array $data)     ← public, inherited from BaseLogger
          └── ConcreteLogger::write(array $data)  ← protected, YOU implement this
                  └── XxxLog::create([...])        ← writes to DB
```

`log()` wraps `write()` in try-catch so a DB failure never bubbles up to the caller.

---

## BaseLogger

### Contract

```php
// app/Services/Logger/BaseLogger.php

abstract class BaseLogger
{
    abstract protected function write(array $data): void;

    public function log(array $data): void
    {
        try {
            $this->write($data);
        } catch (\Throwable $e) {
            Log::error(static::class.' failed to write log: '.$e->getMessage());
        }
    }
}
```

| Method | Visibility | Responsibility |
|--------|-----------|----------------|
| `write(array $data)` | `protected abstract` | YOU implement: map `$data` to an Eloquent `create()` call |
| `log(array $data)` | `public` | INHERITED: calls `write()`, swallows any exceptions |

### Fault-isolation guarantee

`log()` **never throws**. If the database is unavailable, the exception is caught and written to `storage/logs/laravel.log` instead. This means:

- A broken `email_logs` table does NOT crash mail sending.
- A broken `api_logs` table does NOT crash outgoing API calls.
- You can always call `$logger->log(...)` without a try-catch in the caller.

---

## EmailLogger — complete implementation

```php
// app/Services/Logger/EmailLogger.php

class EmailLogger extends BaseLogger
{
    protected function write(array $data): void
    {
        EmailLog::create([
            'to'             => $data['to'],
            'cc'             => $data['cc'] ?? null,
            'bcc'            => $data['bcc'] ?? null,
            'subject'        => $data['subject'],
            'body'           => $data['body'] ?? null,
            'mailable_class' => $data['mailable_class'] ?? null,
            'status'         => $data['status'],            // 'sent' | 'failed'
            'failed_reason'  => $data['failed_reason'] ?? null,
            'sent_at'        => $data['status'] === 'sent' ? now() : null,
        ]);
    }
}
```

`write()` only maps keys — all fault-isolation is inherited from `BaseLogger::log()`.

### How it plugs into EmailService

`EmailService` injects `EmailLogger` and calls `$this->logger->log()` **only on failure**. Successful sends are logged by the listener (see below), avoiding double-writes.

```php
class EmailService
{
    public function __construct(private EmailLogger $logger) {}

    public function send(...): void
    {
        $body = null;
        try {
            $body = view($view, $data)->render();
            Mail::send([], [], fn ($m) => $m->to($to)->subject($subject)->html($body));
            // SUCCESS — listener handles the log, nothing more to do here
        } catch (\Throwable $e) {
            // FAILURE — log it directly, then re-throw
            $this->logger->log([
                'to' => $to, 'subject' => $subject, 'body' => $body,
                'status' => 'failed', 'failed_reason' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
```

### How it plugs into LogSentEmail listener

The listener catches every `Mail::send()` / `Mail::to()->send()` fired anywhere in the app — including code that does not use `EmailService` at all.

```php
class LogSentEmail
{
    public function __construct(private EmailLogger $logger) {}

    public function handle(MessageSent $event): void
    {
        $message = $event->message;

        $this->logger->log([
            'to'      => array_map(fn ($a) => $a->getAddress(), $message->getTo() ?? []),
            'cc'      => array_map(fn ($a) => $a->getAddress(), $message->getCc() ?? []) ?: null,
            'bcc'     => array_map(fn ($a) => $a->getAddress(), $message->getBcc() ?? []) ?: null,
            'subject' => $message->getSubject() ?? '',
            'body'    => $message->getHtmlBody() ?? $message->getTextBody(),
            'status'  => 'sent',
        ]);
    }
}
```

> **Why `$addr->getAddress()`?** Laravel 12 uses Symfony Mailer. `$message->getTo()` returns `Symfony\Component\Mime\Address[]` objects — not a keyed array. Calling `array_keys()` on them gives integer indices `[0, 1, 2...]`. `getAddress()` extracts the actual email string.

### Listener auto-discovery

Laravel auto-discovers `LogSentEmail` because its `handle()` method is type-hinted with `MessageSent`. **No manual registration is needed.** Do not add it to `AppServiceProvider` or an `EventServiceProvider` — that causes double-logging.

```
✓  handle(MessageSent $event)   ← Laravel discovers this automatically
✗  Event::listen(MessageSent::class, LogSentEmail::class)  ← do NOT add this
```

---

## ApiLogger — full implementation guide

`ApiLogger` extends `BaseLogger` but its `write()` is currently a stub. Follow these steps to implement it fully.

### Step 1 — Migration

```bash
php artisan make:migration create_api_logs_table --no-interaction
```

```php
Schema::create('api_logs', function (Blueprint $table) {
    $table->id();
    $table->string('method', 10);               // GET, POST, PUT, DELETE…
    $table->string('url');
    $table->json('request_headers')->nullable();
    $table->json('request_payload')->nullable();
    $table->json('response_payload')->nullable();
    $table->unsignedSmallInteger('status_code')->nullable();
    $table->unsignedInteger('duration_ms')->nullable();
    $table->enum('status', ['success', 'failed'])->default('success');
    $table->text('failed_reason')->nullable();
    $table->string('service')->nullable();      // e.g. 'payment-gateway', 'sms-provider'
    $table->timestamps();
});
```

```bash
php artisan migrate --no-interaction
```

### Step 2 — Model

```bash
php artisan make:model ApiLog --no-interaction
```

```php
// app/Models/ApiLog.php

class ApiLog extends Model
{
    protected $fillable = [
        'method', 'url',
        'request_headers', 'request_payload',
        'response_payload', 'status_code',
        'duration_ms', 'status', 'failed_reason', 'service',
    ];

    public function casts(): array
    {
        return [
            'request_headers'  => 'array',
            'request_payload'  => 'array',
            'response_payload' => 'array',
        ];
    }
}
```

### Step 3 — Implement ApiLogger::write()

```php
// app/Services/Logger/ApiLogger.php

use App\Models\ApiLog;

class ApiLogger extends BaseLogger
{
    protected function write(array $data): void
    {
        ApiLog::create([
            'method'           => strtoupper($data['method']),
            'url'              => $data['url'],
            'request_headers'  => $data['request_headers'] ?? null,
            'request_payload'  => $data['request_payload'] ?? null,
            'response_payload' => $data['response_payload'] ?? null,
            'status_code'      => $data['status_code'] ?? null,
            'duration_ms'      => $data['duration_ms'] ?? null,
            'status'           => $data['status'],          // 'success' | 'failed'
            'failed_reason'    => $data['failed_reason'] ?? null,
            'service'          => $data['service'] ?? null,
        ]);
    }
}
```

### Step 4 — Create ApiService

This is the equivalent of `EmailService` — it wraps HTTP calls and logs them automatically.

```bash
php artisan make:class Services/ApiService --no-interaction
```

```php
// app/Services/ApiService.php

namespace App\Services;

use App\Services\Logger\ApiLogger;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiService
{
    public function __construct(private ApiLogger $logger) {}

    /**
     * Make an outgoing HTTP request and log the result automatically.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     */
    public function request(
        string $method,
        string $url,
        array $payload = [],
        array $headers = [],
        ?string $service = null
    ): Response {
        $start = microtime(true);
        $response = null;

        try {
            $response = Http::withHeaders($headers)
                ->{strtolower($method)}($url, $payload);

            $this->logger->log([
                'method'           => $method,
                'url'              => $url,
                'request_headers'  => $headers ?: null,
                'request_payload'  => $payload ?: null,
                'response_payload' => $response->json() ?? ['raw' => $response->body()],
                'status_code'      => $response->status(),
                'duration_ms'      => (int) ((microtime(true) - $start) * 1000),
                'status'           => $response->successful() ? 'success' : 'failed',
                'failed_reason'    => $response->successful() ? null : $response->body(),
                'service'          => $service,
            ]);

            return $response;
        } catch (\Throwable $e) {
            $this->logger->log([
                'method'        => $method,
                'url'           => $url,
                'request_headers' => $headers ?: null,
                'request_payload' => $payload ?: null,
                'duration_ms'   => (int) ((microtime(true) - $start) * 1000),
                'status'        => 'failed',
                'failed_reason' => $e->getMessage(),
                'service'       => $service,
            ]);

            throw $e;
        }
    }
}
```

**Usage anywhere in the app:**

```php
// In a controller or service
public function __construct(private ApiService $apiService) {}

// GET request
$response = $this->apiService->request(
    method: 'GET',
    url: 'https://api.example.com/users/123',
    service: 'example-api'
);

// POST request with payload
$response = $this->apiService->request(
    method: 'POST',
    url: 'https://payment.example.com/charge',
    payload: ['amount' => 5000, 'currency' => 'USD'],
    headers: ['Authorization' => 'Bearer '.$token],
    service: 'payment-gateway'
);
```

### Step 5 — Create the listener (optional)

`ApiLogger` does not need a catch-all listener the way `EmailLogger` does, because there is no framework-level "HTTP request sent" event. All API calls go through `ApiService`, which logs them directly. No listener needed.

If you use a third-party HTTP client (e.g., Guzzle middleware), you can hook into its middleware stack instead of a Laravel event listener.

### Step 6 — Permission seeder

```bash
php artisan make:seeder ApiLogPermissionsSeeder --no-interaction
```

```php
// database/seeders/ApiLogPermissionsSeeder.php

public function run(): void
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permission = Permission::firstOrCreate(
        ['name' => 'api-logs.view', 'guard_name' => 'web'],
        ['module' => 'api-logs', 'description' => 'API Logs — View']
    );

    foreach (['Superuser', 'Admin'] as $roleName) {
        Role::where('name', $roleName)->first()?->givePermissionTo($permission);
    }

    $this->command->info('API log permission seeded successfully.');
}
```

```bash
php artisan db:seed --class=ApiLogPermissionsSeeder --no-interaction
```

### Step 7 — Admin UI

Create a read-only controller following the same pattern as `EmailLogController`:

```bash
php artisan make:controller ApiLogController --no-interaction
```

```php
// app/Http/Controllers/ApiLogController.php

class ApiLogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = ApiLog::query()->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('service')) {
                $query->where('service', $request->service);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', fn (ApiLog $log) => $log->status === 'success'
                    ? '<span class="badge bg-success">Success</span>'
                    : '<span class="badge bg-danger">Failed</span>')
                ->addColumn('method_badge', fn (ApiLog $log) => match ($log->method) {
                    'GET'    => '<span class="badge bg-info">'.$log->method.'</span>',
                    'POST'   => '<span class="badge bg-primary">'.$log->method.'</span>',
                    'PUT',
                    'PATCH'  => '<span class="badge bg-warning text-dark">'.$log->method.'</span>',
                    'DELETE' => '<span class="badge bg-danger">'.$log->method.'</span>',
                    default  => '<span class="badge bg-secondary">'.$log->method.'</span>',
                })
                ->addColumn('action', fn (ApiLog $log) =>
                    '<a href="'.route('api-logs.show', $log).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>'
                )
                ->rawColumns(['status_badge', 'method_badge', 'action'])
                ->make(true);
        }

        $services = ApiLog::distinct()->orderBy('service')->pluck('service')->filter();

        return view('api-logs.index', compact('services'));
    }

    public function show(ApiLog $apiLog): View
    {
        return view('api-logs.show', compact('apiLog'));
    }
}
```

Create views at `resources/views/api-logs/index.blade.php` and `show.blade.php` following the same structure as `resources/views/email-logs/`.

### Step 8 — Routes

```php
// routes/web.php

use App\Http\Controllers\ApiLogController;

// API Logs — view only
Route::prefix('api-logs')->name('api-logs.')->middleware('permission:api-logs.view')->group(function () {
    Route::get('/', [ApiLogController::class, 'index'])->name('index');
    Route::get('/{apiLog}', [ApiLogController::class, 'show'])->name('show');
});
```

Add to sidebar under SYSTEM:
```blade
<li class="nav-item">
    <a href="{{ route('api-logs.index') }}"
       class="nav-link {{ request()->routeIs('api-logs.*') ? 'active' : '' }}">
        <i class="nav-icon bi bi-cloud-arrow-up"></i>
        <p>API Logs</p>
    </a>
</li>
```

---

## Adding a New Logger Type

To add any new logger (e.g., `SmsLogger`, `WebhookLogger`, `PaymentLogger`), the pattern is always the same four steps:

**1. Create the log table and model**
```bash
php artisan make:migration create_sms_logs_table --no-interaction
php artisan make:model SmsLog --no-interaction
```

**2. Extend BaseLogger**
```php
// app/Services/Logger/SmsLogger.php

class SmsLogger extends BaseLogger
{
    protected function write(array $data): void
    {
        SmsLog::create([
            'to'           => $data['to'],
            'message'      => $data['message'],
            'provider'     => $data['provider'] ?? null,
            'status'       => $data['status'],
            'failed_reason' => $data['failed_reason'] ?? null,
            'sent_at'      => $data['status'] === 'sent' ? now() : null,
        ]);
    }
}
```

**3. Inject into the service that sends SMS**
```php
class SmsService
{
    public function __construct(private SmsLogger $logger) {}

    public function send(string $to, string $message): void
    {
        try {
            // ... call SMS provider ...
            $this->logger->log(['to' => $to, 'message' => $message, 'status' => 'sent']);
        } catch (\Throwable $e) {
            $this->logger->log(['to' => $to, 'message' => $message, 'status' => 'failed', 'failed_reason' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

**4. Add permission, admin route, and view** — follow the same pattern as email-logs or api-logs above.

Laravel's container resolves `SmsLogger` automatically — no binding or registration needed.

---

## Design Rules

| Rule | Why |
|------|-----|
| `write()` is the only method you implement | All fault-isolation lives in `BaseLogger::log()` — never override `log()` |
| `log()` never throws | Logging must never crash the application feature it's attached to |
| Log after `DB::commit()`, never inside a transaction | A failed log write must not roll back business data |
| Each logger has one model, one table | One concern per logger — do not share tables between logger types |
| Do not register listeners manually | Laravel auto-discovers listeners via type-hinted `handle(EventClass $event)` — manual registration causes double-firing |
| Services log failures directly; listeners log successes | This division prevents double-writes when both a service and a listener are active for the same event |
