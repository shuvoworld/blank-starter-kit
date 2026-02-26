# Laravel Starter Kit with AdminLTE v4

A robust Laravel 12 starter kit with Role-Based Access Control (RBAC), Activity Logging, Role-Based Dashboards, Leave Management, Department/Designation management, Location-aware employee profiles, DataTables integration, and dynamic landing page customization. Built with PHP 8.4, following Laravel best practices, and styled with AdminLTE v4.

![Laravel](https://img.shields.io/badge/Laravel-12.52.0-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.4.16-purple.svg)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

## Features

### Authentication & Authorization
- Complete user authentication (via Laravel Breeze)
- **Spatie Permission** — Role-Based Access Control (RBAC)
- Module-based permission management
- Role and permission assignment to users
- Pre-configured roles: Super Admin, Admin, Manager, Editor, Employee, User

### Role-Based Dashboards
- Dynamic dashboard routing via `HomeController` — routes users to role-specific views
- Six role-specific dashboards tailored to user permissions
- AdminLTE v4 components: small-box, info-box, card-outline, striped tables, charts

### Core Modules
- **Users** — User management with role assignment
- **Roles** — Role management with permission assignment
- **Permissions** — Permission management grouped by module
- **Departments** — Department CRUD with employee associations
- **Designations** — Designation CRUD linked to departments
- **Employees** — Employee CRUD with filtering, profile pictures, documents (resume, certificates), location-aware address (country/city/area via laravel-locations)
- **Products** — Product CRUD functionality
- **Activity Logs** — Track and view system activities (Spatie Activity Log)

### Leave & Attendance Management
- **Leave Types** — Configurable leave types (Annual, Sick, Casual, Maternity, Paternity, Unpaid) with rules per type
- **Leave Requests** — Employee leave submission, manager approval/rejection workflow
- **Leave Balances** — Per-employee, per-type balance tracking with carry-forward support
- **Holidays** — Holiday calendar management with date-range API endpoint

See [docs/leave-attendance-management.md](docs/leave-attendance-management.md) for full details.

### UI/UX
- **AdminLTE v4** responsive layout for dashboard and authentication
- Server-side **DataTables** with filtering (department, designation, status, hire date)
- Bootstrap Icons integration
- Toast notifications
- Modal confirmations for destructive actions
- Chart.js integration for visual analytics

### Dynamic Landing Page
- Fully customizable public landing page through the admin panel
- Sections: Hero, Features, How It Works, Stats, Testimonials, Pricing, FAQ, Footer

## Tech Stack

### Backend

| Package | Version | Purpose |
|---------|---------|---------|
| PHP | 8.4.16 | Core language |
| Laravel | 12.52.0 | Web framework |
| Laravel Breeze | 2.3.8 | Authentication scaffolding |
| Spatie Permission | ^7.2 | Role & permission management |
| Spatie Activity Log | ^4.12 | Activity logging |
| Spatie Media Library | ^11.21 | File & media management |
| Yajra DataTables | ^12.7 | Server-side DataTables |
| milenmk/laravel-locations | ^1.4 | Country/city/area location data |

### Frontend

| Package | Version | Purpose |
|---------|---------|---------|
| AdminLTE | v4 | Admin dashboard template |
| Alpine.js | 3.15.8 | Frontend interactivity |
| Tailwind CSS | 3.4.19 | Utility-first CSS framework |
| Chart.js | 4.4.1 | Charts and graphs |
| DataTables | — | Table enhancement plugin |

### Dev Tools

| Package | Version | Purpose |
|---------|---------|---------|
| Pest | 4.4.1 | Testing framework |
| Laravel Pint | 1.27.1 | Code style formatter |
| Laravel Sail | 1.53.0 | Docker dev environment |

## Installation

### Prerequisites
- PHP 8.4+
- Composer
- NPM
- MySQL
- Laravel Herd (or equivalent local development server)

### Quick Setup

```bash
git clone <your-repo-url>
cd blank-starter-kit
composer run setup
```

The `setup` script runs: `composer install`, `.env` copy, `key:generate`, `migrate`, `npm install`, and `npm run build`.

### Manual Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure database in .env
DB_DATABASE=blank_starter_kit
DB_USERNAME=root
DB_PASSWORD=your_password

# 4. Migrate and seed
php artisan migrate --seed

# 5. Build assets
npm run build
```

The seeder creates all roles, permissions, role-permission assignments, a default admin user, and landing page sections.

### Development Server

```bash
composer run dev   # starts artisan serve + queue + vite concurrently
```

Via Laravel Herd: `https://blank-starter-kit.test`

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@example.com` | `password` |
| Admin | `manager@example.com` | `password` |
| Manager | `editor@example.com` | `password` |
| Editor | `employee@example.com` | `password` |
| Employee | `user@example.com` | `password` |

> **Change these passwords before deploying to production.**

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php               # Role-based dashboard routing
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   ├── PermissionController.php
│   │   ├── DepartmentController.php
│   │   ├── DesignationController.php
│   │   ├── EmployeeController.php
│   │   ├── ProductController.php
│   │   ├── HolidayController.php
│   │   ├── LeaveTypeController.php
│   │   ├── LeaveRequestController.php
│   │   ├── LeaveBalanceController.php
│   │   ├── LandingPageSectionController.php
│   │   ├── ActivityLogController.php
│   │   └── ProfileController.php
│   ├── Middleware/
│   │   ├── HasPermission.php                # Permission authorization
│   │   └── HasRole.php                      # Role authorization
│   └── Requests/                            # Form request validation classes
├── Models/
│   ├── User.php
│   ├── Department.php
│   ├── Designation.php
│   ├── Employee.php
│   ├── Product.php
│   ├── Holiday.php
│   ├── LeaveType.php
│   ├── LeaveRequest.php
│   ├── LeaveBalance.php
│   ├── LandingPageSection.php
│   ├── Permission.php
│   └── Role.php
├── Services/
│   ├── DataTableService.php
│   ├── PermissionService.php
│   ├── RbacService.php
│   ├── RoleService.php
│   ├── LeaveBalanceService.php
│   └── LeaveCalculationService.php
└── Traits/
    └── HasActivityLog.php

resources/views/
├── layouts/
│   ├── app.blade.php                        # Main dashboard layout
│   └── guest.blade.php                      # Auth layout (AdminLTE)
├── auth/                                    # Authentication pages
├── dashboards/                              # Role-based dashboard views
├── departments/
├── designations/
├── employees/
├── products/
├── holidays/
├── leave-types/
├── leave-requests/
├── leave-balances/
├── landing-page-sections/
├── permissions/ / roles/ / users/
├── activity-log/
└── welcome.blade.php                        # Dynamic public landing page

docs/
└── leave-attendance-management.md           # Leave module documentation
```

## Available Routes

### Authentication
| Route | Method | Description |
|-------|--------|-------------|
| `/login` | GET/POST | User login |
| `/register` | GET/POST | User registration |
| `/logout` | POST | User logout |
| `/forgot-password` | GET/POST | Password reset request |
| `/reset-password/{token}` | GET/POST | Password reset |

### Core Management
| Route | Description |
|-------|-------------|
| `/dashboard` | Role-based dashboard |
| `/users` | User management |
| `/roles` | Role management + permission assignment |
| `/permissions` | Permission management |
| `/departments` | Department CRUD |
| `/designations` | Designation CRUD |
| `/employees` | Employee CRUD with location support |
| `/products` | Product CRUD |
| `/activity-log` | Activity log viewer |
| `/landing-page-sections` | Landing page customization |

### Leave & Attendance
| Route | Description |
|-------|-------------|
| `/leave-types` | Leave type configuration (admin) |
| `/holidays` | Holiday calendar management |
| `/leave-requests` | All leave requests (manager/admin) |
| `/my-leave-requests` | Employee's own leave requests |
| `/my-leave-summary` | Employee leave balance summary |
| `/leave-balances` | Leave balance management |

## Development

### Module Development
For guidelines on creating new modules and file upload functionality, see:
**[MODULE_DEVELOPMENT_GUIDE.md](MODULE_DEVELOPMENT_GUIDE.md)**

For leave and attendance module details, see:
**[docs/leave-attendance-management.md](docs/leave-attendance-management.md)**

### Code Formatting
Run Laravel Pint before committing:
```bash
vendor/bin/pint --dirty
```

### Testing
```bash
# Run all tests
php artisan test --compact

# Run specific test
php artisan test --compact --filter=testName
```

### Useful Commands
```bash
php artisan view:clear    # Clear compiled views
php artisan config:clear  # Clear config cache
php artisan route:list    # List all routes
```

## Conventions

- **Form Requests** — Validation in dedicated Form Request classes
- **Eloquent** — Use models over raw queries; prefer `Model::query()`
- **Named Routes** — Use `route()` helper throughout
- **Config** — Use `config('key')` not `env()` directly
- **Eager Loading** — Prevent N+1 queries
- **Type Declarations** — All methods use explicit return types
- **Constructor Promotion** — PHP 8 syntax in `__construct()`

## Security

- CSRF protection enabled
- Authorization via Spatie Permission
- Input validation via Form Requests
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade templating
- Activity logging for audit trails

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.

## Credits

- [Laravel](https://laravel.com) — Web framework
- [AdminLTE](https://adminlte.io) — Admin dashboard template
- [Spatie](https://spatie.be) — Permission, Activity Log & Media Library packages
- [Yajra DataTables](https://yajrabox.com/docs/laravel-datatables) — Server-side DataTables
- [milenmk/laravel-locations](https://github.com/milenmk/laravel-locations) — Location data
- [Chart.js](https://www.chartjs.org) — Charting library
