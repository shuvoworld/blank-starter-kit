# Laravel Starter Kit with AdminLTE v4

A robust Laravel 12 starter kit with Role-Based Access Control (RBAC), Activity Logging, Role-Based Dashboards, DataTables integration, and dynamic landing page customization. Built with modern PHP 8.4, following Laravel best practices, and styled with AdminLTE v4.

![Laravel](https://img.shields.io/badge/Laravel-12.52.0-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.4.16-purple.svg)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

## Features

### 🔐 Authentication & Authorization
- Complete user authentication system (via Laravel Breeze)
- **Spatie Permission** - Role-Based Access Control (RBAC)
- Module-based permission management
- Role and Permission assignment to users
- Pre-configured roles: Super Admin, Admin, Manager, Editor, Employee, User

### 📊 Role-Based Dashboards
- **Dynamic dashboard routing** via `HomeController` - routes users to role-specific dashboard views
- **Six role-specific dashboards** tailored to user permissions:
  - `superadmin.blade.php` - Full system access, all stats, tech stack overview
  - `admin.blade.php` - Organization management, users, roles, landing page
  - `manager.blade.php` - Team management, employees, products overview
  - `editor.blade.php` - Content creation focus, employees, products
  - `employee.blade.php` - Basic employee access, directory view
  - `user.blade.php` - Standard user dashboard with profile
- **AdminLTE v4 components**: small-box, info-box, card-outline, striped tables, charts

### 🎨 UI/UX
- **AdminLTE v4** responsive layout for dashboard and authentication
- Server-side **DataTables** with filtering (department, position, status, hire date)
- Bootstrap icons integration
- Toast notifications
- Modal confirmations for destructive actions
- Chart.js integration for visual analytics

### 📄 Dynamic Landing Page
- Fully customizable public landing page through admin panel
- Sections: Hero, Features, How It Works, Stats, Testimonials, Pricing, FAQ, Footer
- Dynamic content management without code changes

### 📝 Core Modules
- **Users** - User management with role assignment
- **Roles** - Role management with permission assignment
- **Permissions** - Permission management grouped by module
- **Employees** - Employee CRUD with filtering, profile pictures, documents (resume, certificates)
- **Products** - Product CRUD functionality
- **Activity Logs** - Track and view system activities (Spatie Activity Log)

## Tech Stack

| Package | Version | Description |
|---------|---------|-------------|
| PHP | 8.4.16 | Core language |
| Laravel | 12.52.0 | Web application framework |
| Laravel Breeze | 2.3.8 | Authentication scaffolding |
| Spatie Permission | - | Role & Permission management |
| Spatie Activity Log | - | Activity logging |
| Spatie Media Library | v11 | File & Media management |
| Pest | 4.4.1 | Testing framework |
| Laravel Pint | 1.27.1 | Code style formatter |
| AdminLTE | v4 | Admin dashboard template |
| Alpine.js | 3.15.8 | Frontend interactivity |
| Tailwind CSS | 3.4.19 | Utility-first CSS framework |
| DataTables | - | Server-side table processing |
| Chart.js | 4.4.1 | Charts and graphs |

## Installation

### Prerequisites
- PHP 8.4+
- Composer
- NPM
- MySQL
- Laravel Herd (or equivalent local development server)

### Step 1: Clone and Install

```bash
git clone <your-repo-url>
cd blank-starter-kit
composer install
npm install
```

### Step 2: Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:
```env
DB_DATABASE=blank_starter_kit
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 3: Run Migrations and Seeders

```bash
php artisan migrate --seed
```

The seeder will create:
- All roles (Super Admin, Admin, Manager, Editor, Employee, User)
- All permissions grouped by module
- Role-Permission assignments
- Default admin user
- Landing page sections

### Step 4: Build Frontend Assets

```bash
npm run build
```

### Step 5: Serve the Application

Via Laravel Herd:
```
https://blank-starter-kit.test
```

Or using artisan:
```bash
php artisan serve
```

## Default Credentials

After running `php artisan migrate --seed`, you can log in with:

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Super Admin | `admin@example.com` | `password` | Full system access |
| Admin | `manager@example.com` | `password` | Organization management |
| Manager | `editor@example.com` | `password` | Team & content management |
| Editor | `employee@example.com` | `password` | Content creation |
| Employee | `user@example.com` | `password` | Basic access |

**⚠️ Important:** Change these passwords in production!

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ActivityLogController.php
│   │   ├── EmployeeController.php
│   │   ├── HomeController.php          # Role-based dashboard routing
│   │   ├── LandingPageSectionController.php
│   │   ├── PermissionController.php
│   │   ├── ProductController.php
│   │   ├── ProfileController.php
│   │   ├── RoleController.php
│   │   └── UserController.php
│   ├── Middleware/
│   │   ├── HasPermission.php           # Permission authorization
│   │   └── HasRole.php                 # Role authorization
│   └── Requests/                       # Form request validation
├── Models/
│   ├── Employee.php
│   ├── LandingPageSection.php          # Dynamic landing page
│   ├── Permission.php
│   ├── Product.php
│   ├── Role.php
│   └── User.php
├── Services/
│   ├── DataTableService.php            # DataTable utilities
│   ├── PermissionService.php
│   ├── RbacService.php                 # RBAC functionality
│   └── RoleService.php
└── Traits/
    └── HasActivityLog.php              # Activity logging trait

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php               # Main dashboard layout
│   │   └── guest.blade.php             # Auth layout (AdminLTE)
│   ├── auth/                           # Authentication pages
│   ├── dashboards/                     # Role-based dashboards
│   │   ├── superadmin.blade.php
│   │   ├── admin.blade.php
│   │   ├── manager.blade.php
│   │   ├── editor.blade.php
│   │   ├── employee.blade.php
│   │   └── user.blade.php
│   ├── landing-page-sections/          # Landing page management
│   └── welcome.blade.php               # Dynamic public landing page
```

## Role-Based Dashboard System

The `HomeController` determines which dashboard view to show based on the user's highest priority role:

```php
// Role priority (highest to lowest)
$roleDashboardMap = [
    'Super Admin' => 'superadmin',
    'Admin'       => 'admin',
    'Manager'     => 'manager',
    'Editor'      => 'editor',
    'Employee'    => 'employee',
    'User'        => 'user',
];
```

### Dashboard Features by Role

| Role | Dashboard | Key Features |
|------|-----------|--------------|
| Super Admin | Full system overview | All stats, tech stack, system control |
| Admin | Organization management | Users, employees, products, landing page |
| Manager | Team oversight | Employees, products, team stats |
| Editor | Content creation | Employees, products management |
| Employee | Basic access | Directory view, products |
| User | Personal | Profile, basic information |

## AdminLTE v4 Integration

This starter kit uses AdminLTE v4 components throughout:

### Components Used
- **Info Boxes** - `info-box bg-info/success/warning/danger`
- **Small Boxes** - `small-box bg-info/success/warning/danger`
- **Cards** - `card card-primary card-outline`
- **Profile Widget** - `box-profile` with `profile-username`
- **Progress Bars** - With percentage indicators
- **Input Groups** - With Font Awesome icons
- **Charts** - Chart.js integration

### Authentication Pages
- Login, Register, Forgot Password, Reset Password pages styled with AdminLTE
- Input groups with icons
- Card-based layout with proper validation feedback

## Development

### Module Development
For detailed guidelines on creating new modules and file upload functionality, see:
**[MODULE_DEVELOPMENT_GUIDE.md](MODULE_DEVELOPMENT_GUIDE.md)**

Topics covered:
- File upload guidelines (images, PDFs)
- Spatie Media Library integration
- Creating new modules (models, controllers, views)
- Best practices for media management

### Code Formatting
Run Laravel Pint before committing:
```bash
vendor/bin/pint --dirty
```

### Testing
Run all tests:
```bash
php artisan test --compact
```

Run specific test:
```bash
php artisan test --compact --filter=testName
```

### Clear View Cache
After updating Blade templates:
```bash
php artisan view:clear
```

## Available Routes

### Authentication
| Route | Method | Description |
|-------|--------|-------------|
| `/login` | GET/POST | User login |
| `/register` | GET/POST | User registration |
| `/logout` | POST | User logout |
| `/password/reset` | GET/POST | Password reset |

### Main Routes
| Route | Method | Description | Permission |
|-------|--------|-------------|------------|
| `/dashboard` | GET | Role-based dashboard | auth |
| `/users` | GET/POST | User management | view any users |
| `/users/{user}/roles` | GET/PUT | User role assignment | update users |
| `/roles` | GET/POST | Role management | view any roles |
| `/permissions` | GET/POST | Permission management | view any permissions |
| `/employees` | GET/POST | Employee management | view any employees |
| `/products` | GET/POST | Product management | view any products |
| `/activity-log` | GET | Activity log viewer | view any activity log |
| `/landing-page-sections` | GET/PUT | Landing page customization | manage landing page |

### Public Routes
| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Dynamic public landing page |

## Conventions

### PHP Code Style
- **Type Declarations**: All methods use explicit return type declarations
- **Constructor Property Promotion**: Use PHP 8 syntax
- **Casts**: Model casts in `casts()` method
- **Scopes**: Use query scopes for reusable logic (e.g., `scopeByDepartment()`)

### Naming Conventions
- **Variables/Methods**: Descriptive names like `isRegisteredForDiscounts`
- **Filter Parameters**: Use `filter_{field_name}` to avoid DataTables conflicts

### Laravel Best Practices
- **Form Requests**: Validation in separate Form Request classes
- **Eloquent**: Use models over raw queries; prefer `Model::query()`
- **Routes**: Use named routes with `route()` helper
- **Configuration**: Use `config('key')` not `env()` directly
- **Eager Loading**: Prevent N+1 queries

## Security

- ✅ CSRF protection enabled
- ✅ Authorization via Spatie Permission
- ✅ Input validation via Form Requests
- ✅ SQL injection prevention via Eloquent ORM
- ✅ XSS protection via Blade templating
- ✅ Activity logging for audit trails

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Credits

- [Laravel](https://laravel.com) - The Web Framework
- [AdminLTE](https://adminlte.io) - Admin Dashboard Template
- [Spatie](https://spatie.be) - Permission & Activity Log packages
- [DataTables](https://datatables.net) - Table enhancement plugin
- [Chart.js](https://www.chartjs.org) - Charting library

---

Made with ❤️ using Laravel 12 & AdminLTE v4
