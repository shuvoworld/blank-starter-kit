# Email Service Guide

How to send emails from anywhere in this application using `EmailService`.

---

## Table of Contents

- [How It Works](#how-it-works)
- [Sending an Email](#sending-an-email)
  - [Step 1 — Create the Blade view](#step-1--create-the-blade-view)
  - [Step 2 — Inject EmailService](#step-2--inject-emailservice)
  - [Step 3 — Call send()](#step-3--call-send)
- [Sending a Mailable](#sending-a-mailable)
  - [Step 1 — Create the Mailable](#step-1--create-the-mailable)
  - [Step 2 — Call sendMailable()](#step-2--call-sendmailable)
- [Automatic Logging](#automatic-logging)
- [Failure Handling](#failure-handling)
- [Where to Use EmailService](#where-to-use-emailservice)
- [Viewing Sent Emails](#viewing-sent-emails)
- [Common Mistakes](#common-mistakes)

---

## How It Works

```
EmailService::send()
    └── Mail::send()           ← Laravel mailer
            └── MessageSent event fired
                    └── LogSentEmail listener
                            └── EmailLog::create()   ← written to email_logs table
```

Every email sent through `EmailService` — or through `Mail::send()` / `Mail::to()->send()` anywhere in the app — is automatically captured by the `LogSentEmail` listener and stored in the `email_logs` table. You do not need to add any logging code yourself.

If sending fails, `EmailService` catches the exception, writes a `failed` record to `email_logs`, then re-throws so the caller knows it failed.

---

## Sending an Email

### Step 1 — Create the Blade view

All email views live under `resources/views/emails/`. Organise by domain:

```
resources/views/emails/
└── leave-request/
    ├── submitted-employee.blade.php
    └── submitted-admin.blade.php
└── user/
    └── welcome.blade.php
```

Write a self-contained HTML email. Use inline styles (email clients strip `<style>` blocks):

```blade
{{-- resources/views/emails/user/welcome.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px;">
        <h2 style="color: #0d6efd;">Welcome, {{ $user->name }}!</h2>
        <p>Your account has been created. You can now log in at:</p>
        <a href="{{ $loginUrl }}" style="display:inline-block; padding: 10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:5px;">
            Log In
        </a>
    </div>
</body>
</html>
```

### Step 2 — Inject EmailService

Inject `EmailService` in the constructor of any controller, service, job, or listener:

```php
use App\Services\EmailService;

class UserController extends BaseController
{
    public function __construct(
        // ... existing dependencies ...
        private EmailService $emailService
    ) {
        // ...
    }
}
```

For a service class:

```php
use App\Services\EmailService;

class UserService extends BaseService
{
    public function __construct(private EmailService $emailService)
    {
        $this->modelClass = User::class;
    }
}
```

### Step 3 — Call send()

```php
$this->emailService->send(
    to: 'jane@example.com',           // string or array of strings
    subject: 'Welcome to the system',
    view: 'emails.user.welcome',       // dot-notation path under resources/views/
    data: ['user' => $user, 'loginUrl' => route('login')],
    cc: [],                            // optional
    bcc: []                            // optional
);
```

**Signature:**
```php
public function send(
    string|array $to,
    string $subject,
    string $view,
    array $data = [],
    array $cc = [],
    array $bcc = []
): void
```

**Sending to multiple recipients:**
```php
$this->emailService->send(
    to: ['alice@example.com', 'bob@example.com'],
    subject: 'Team Announcement',
    view: 'emails.team.announcement',
    data: ['message' => $text]
);
```

**With CC and BCC:**
```php
$this->emailService->send(
    to: $manager->email,
    subject: 'Leave Request Submitted',
    view: 'emails.leave-request.submitted-admin',
    data: ['leaveRequest' => $leaveRequest, 'reviewUrl' => $url],
    cc: [$hrDirector->email],
    bcc: ['audit@company.com']
);
```

---

## Sending a Mailable

Use `sendMailable()` when you have an existing `Mailable` class (e.g., from a package or a class built with `php artisan make:mail`).

### Step 1 — Create the Mailable

```bash
php artisan make:mail UserWelcomeMail --no-interaction
```

```php
// app/Mail/UserWelcomeMail.php
class UserWelcomeMail extends Mailable
{
    public function __construct(public User $user) {}

    public function content(): Content
    {
        return new Content(view: 'emails.user.welcome');
    }
}
```

### Step 2 — Call sendMailable()

```php
use App\Mail\UserWelcomeMail;

$this->emailService->sendMailable(
    to: $user->email,
    mailable: new UserWelcomeMail($user)
);
```

---

## Automatic Logging

Every sent email is logged to `email_logs` automatically — you never need to call the logger manually. The log record captures:

| Field | Description |
|-------|-------------|
| `to` | Array of recipient addresses |
| `cc` | Array of CC addresses (nullable) |
| `bcc` | Array of BCC addresses (nullable) |
| `subject` | Email subject |
| `body` | Full rendered HTML body |
| `mailable_class` | Class name if a Mailable was used |
| `status` | `sent` or `failed` |
| `failed_reason` | Exception message on failure |
| `sent_at` | Timestamp when sent |

This even covers emails sent directly via `Mail::send()` or `Mail::to()->send()` — the `LogSentEmail` listener catches the framework's `MessageSent` event globally.

---

## Failure Handling

`EmailService` guarantees the following:

- **Failure is logged** — a `failed` record is always written to `email_logs` before re-throwing.
- **Failure is re-thrown** — the caller receives the original exception and can handle or ignore it.
- **`BaseLogger` swallows its own errors** — if the logger itself fails (e.g., DB down), it logs to `storage/logs/laravel.log` and does not throw.

**Pattern: fire-and-forget after a successful transaction**

Wrap the email send in try-catch after `DB::commit()`. Never send emails inside the transaction.

```php
DB::beginTransaction();
try {
    $record = MyModel::create($data);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return redirect()->back()->with('error', 'Something went wrong.');
}

// After commit — email failure must not affect the stored record
try {
    $this->emailService->send(
        $record->user->email,
        'Your record has been created',
        'emails.my-module.created',
        ['record' => $record]
    );
} catch (\Throwable $e) {
    Log::error('Email failed after record creation', ['id' => $record->id, 'error' => $e->getMessage()]);
}
```

---

## Where to Use EmailService

| Location | How to inject |
|----------|---------------|
| Controller | Constructor injection |
| Service class | Constructor injection |
| Job (queued) | Constructor injection |
| Artisan command | `app(EmailService::class)` in `handle()` |
| Observer | Constructor injection |
| Event listener | Constructor injection |

**Do not** use `EmailService` inside a Blade view, migration, or factory.

---

## Viewing Sent Emails

Admins and Superusers can view all email logs at `/email-logs`. The page shows:

- Subject, recipients, status badge (Sent / Failed), and sent timestamp
- Detail view with the full rendered HTML body in an iframe, plus CC/BCC/mailable class
- Status filter (All / Sent / Failed)

Permission required: `email-logs.view`

---

## Common Mistakes

**1. Sending inside a DB transaction**

```php
// WRONG — if email throws, the transaction rolls back and data is lost
DB::beginTransaction();
$record = Model::create($data);
$this->emailService->send(...);  // ← do not put here
DB::commit();

// CORRECT — send after commit
DB::commit();
$this->emailService->send(...);  // ← outside the transaction
```

**2. Not wrapping in try-catch for non-critical emails**

```php
// WRONG — an SMTP failure crashes the whole request
$this->emailService->send($user->email, 'Welcome', 'emails.welcome', []);

// CORRECT — catch and log, let the user flow continue
try {
    $this->emailService->send($user->email, 'Welcome', 'emails.welcome', []);
} catch (\Throwable $e) {
    Log::error('Welcome email failed', ['user_id' => $user->id]);
}
```

**3. Using `Mail::send()` directly and expecting the logger to handle failures**

The `LogSentEmail` listener only fires on `MessageSent` (successful sends). Failed direct `Mail::send()` calls are NOT automatically logged as failures. Use `EmailService::send()` so failures are always captured.

**4. Putting HTML styles in a `<style>` block**

Many email clients (Outlook, Gmail) strip `<style>` blocks. Use inline styles only:

```html
<!-- WRONG -->
<style>.btn { background: blue; }</style>
<a class="btn">Click</a>

<!-- CORRECT -->
<a style="background: blue; color: white; padding: 10px 20px;">Click</a>
```

**5. Using relative URLs in email body**

Email links must be absolute. Always use `route()` which generates full URLs when `APP_URL` is set correctly:

```blade
{{-- WRONG --}}
<a href="/dashboard">Go to dashboard</a>

{{-- CORRECT --}}
<a href="{{ route('dashboard') }}">Go to dashboard</a>
```
