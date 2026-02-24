<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <x-application-logo class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!-- Sidebar Menu -->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Profile -->
                <li class="nav-item">
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person"></i>
                        <p>Profile</p>
                    </a>
                </li>

                <!-- Users Management -->
                @can('view any users')
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endcan

                <!-- RBAC Management -->
                @can('view any roles')
                    <li class="nav-header">RBAC</li>
                @endcan

                @can('view any roles')
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield"></i>
                            <p>Roles</p>
                        </a>
                    </li>
                @endcan

                @can('view any permissions')
                    <li class="nav-item">
                        <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-key"></i>
                            <p>Permissions</p>
                        </a>
                    </li>
                @endcan

                <!-- Activity Log -->
                @can('view any activity log')
                    <li class="nav-header">SYSTEM</li>
                @endcan

                @can('view any activity log')
                    <li class="nav-item">
                        <a href="{{ route('activity-log.index') }}" class="nav-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-activity"></i>
                            <p>Activity Log</p>
                        </a>
                    </li>
                @endcan

                @can('manage landing page')
                    <li class="nav-item">
                        <a href="{{ route('landing-page-sections.index') }}" class="nav-link {{ request()->routeIs('landing-page-sections.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-palette"></i>
                            <p>Landing Page</p>
                        </a>
                    </li>
                @endcan

                <!-- Modules -->
                @canany(['view any employees', 'view any products'])
                    <li class="nav-header">MODULES</li>
                @endcanany

                @can('view any employees')
                    <li class="nav-item">
                        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge"></i>
                            <p>Employees</p>
                        </a>
                    </li>
                @endcan

                @can('view any products')
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Products</p>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>
