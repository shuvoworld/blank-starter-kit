<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <x-application-logo class="brand-image opacity-75 shadow"/>
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
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Profile -->
                <li class="nav-item">
                    <a href="{{ route('profile.edit') }}"
                       class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person"></i>
                        <p>Profile</p>
                    </a>
                </li>

                @php
                    $userRole = auth()->user()->roles->pluck('name')->first();
                    $rolePriority = ['Superuser' => 3, 'Admin' => 2, 'Employee' => 1];
                    $priority = $rolePriority[$userRole] ?? 0;
                @endphp

                @if($priority >= 3)
                    {{-- Superuser Menu --}}
                    <li class="nav-header">MANAGEMENT</li>

                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                           class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </a>
                    </li>

                    <li class="nav-header">ORGANIZATION</li>

                    <li class="nav-item">
                        <a href="{{ route('departments.index') }}"
                           class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building"></i>
                            <p>Departments</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('designations.index') }}"
                           class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge"></i>
                            <p>Designations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('employee-categories.index') }}"
                           class="nav-link {{ request()->routeIs('employee-categories.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-caret-right"></i>
                            <p>Employee Category</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employees.index') }}"
                           class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Employees</p>
                        </a>
                    </li>

                    <li class="nav-header">LEAVE MANAGEMENT</li>

                    <li class="nav-item">
                        <a href="{{ route('leave-types.index') }}"
                           class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-check"></i>
                            <p>Leave Types</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('holidays.index') }}"
                           class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-event"></i>
                            <p>Holidays</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('leave-requests.index') }}"
                           class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar2-check"></i>
                            <p>Leave Requests</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('leave-balances.index') }}"
                           class="nav-link {{ request()->routeIs('leave-balances.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pie-chart"></i>
                            <p>Leave Balances</p>
                        </a>
                    </li>

                    <li class="nav-header">SYSTEM</li>

                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}"
                           class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield"></i>
                            <p>Roles</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('emails.index') }}"
                           class="nav-link {{ request()->routeIs('emails.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-envelope"></i>
                            <p>Emails</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('permissions.index') }}"
                           class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-key"></i>
                            <p>Permissions</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('activity-log.index') }}"
                           class="nav-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-activity"></i>
                            <p>Activity Log</p>
                        </a>
                    </li>

                @elseif($priority >= 2)
                    {{-- Admin Menu --}}
                    <li class="nav-header">ORGANIZATION</li>

                    <li class="nav-item">
                        <a href="{{ route('departments.index') }}"
                           class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building"></i>
                            <p>Departments</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('designations.index') }}"
                           class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge"></i>
                            <p>Designations</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employees.index') }}"
                           class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Employees</p>
                        </a>
                    </li>

                    <li class="nav-header">LEAVE MANAGEMENT</li>

                    <li class="nav-item">
                        <a href="{{ route('leave-types.index') }}"
                           class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-check"></i>
                            <p>Leave Types</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('holidays.index') }}"
                           class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-event"></i>
                            <p>Holidays</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('leave-requests.index') }}"
                           class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar2-check"></i>
                            <p>Leave Requests</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('leave-balances.index') }}"
                           class="nav-link {{ request()->routeIs('leave-balances.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pie-chart"></i>
                            <p>Leave Balances</p>
                        </a>
                    </li>

                    <li class="nav-header">SYSTEM</li>

                    <li class="nav-item">
                        <a href="{{ route('activity-log.index') }}"
                           class="nav-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-activity"></i>
                            <p>Activity Log</p>
                        </a>
                    </li>

                @else
                    {{-- Employee Menu --}}
                    <li class="nav-header">MY ACCOUNT</li>

                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}"
                           class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person"></i>
                            <p>My Profile</p>
                        </a>
                    </li>

                    <li class="nav-header">LEAVE MANAGEMENT</li>

                    <li class="nav-item">
                        <a href="{{ route('my-leave-requests.index') }}"
                           class="nav-link {{ request()->routeIs('my-leave-requests.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar2-check"></i>
                            <p>My Requests</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('my-leave-summary') }}"
                           class="nav-link {{ request()->routeIs('my-leave-summary') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pie-chart"></i>
                            <p>My Leave Summary</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('holidays.index') }}"
                           class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-event"></i>
                            <p>Holidays</p>
                        </a>
                    </li>

                    <li class="nav-header">TEAM</li>

                    <li class="nav-item">
                        <a href="{{ route('departments.index') }}"
                           class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building"></i>
                            <p>Departments</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('designations.index') }}"
                           class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge"></i>
                            <p>Designations</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employees.index') }}"
                           class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Team Directory</p>
                        </a>
                    </li>

                @endif
            </ul>
        </nav>
    </div>
</aside>
