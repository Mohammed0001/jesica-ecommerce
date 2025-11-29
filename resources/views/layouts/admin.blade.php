<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/signature-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/signature-logo.png') }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Jesica Riad') }}</title>

    <!-- Adobe Fonts -->
    <link rel="stylesheet" href="https://use.typekit.net/ckz0ivc.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Admin Styles -->
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #ffffff;
            --text-muted: #6c757d;
            --border-light: #f0f0f0;
            --accent-color: #8B4513;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --sidebar-bg: #000000;
            --sidebar-width: 280px;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 2rem;
            --spacing-xl: 4rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "futura-pt", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: 200;
            line-height: 1.6;
            color: var(--primary-color);
            background-color: #f8f9fc;
            font-size: 0.875rem;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            letter-spacing: 0.05em;
            line-height: 1.2;
            color: var(--primary-color);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: calc(var(--sidebar-width) + 10px);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #2c2c2c 100%);
            z-index: 1000;
            overflow-y: none;
            box-sizing: border-box border-right: 1px solid var(--border-light);
        }

        .sidebar .navbar-brand {
            color: var(--secondary-color);
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 1.25rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 2rem 1.5rem;
            display: block;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar .navbar-brand:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .sidebar-nav .nav-item {
            margin: 0.25rem 0;
        }

        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            margin: 0 0.5rem;
            border-radius: 0.25rem;
        }

        .sidebar-nav .nav-link:hover {
            color: var(--secondary-color);
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--secondary-color);
        }

        .sidebar-nav .nav-link.active {
            color: var(--secondary-color);
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: var(--secondary-color);
            font-weight: 300;
        }

        .sidebar-nav .nav-link i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
        }

        .content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--secondary-color);
        }

        .topbar {
            background-color: var(--secondary-color);
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem 2rem;
            margin-bottom: 0;
        }

        .topbar h1 {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 2rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--primary-color);
            margin: 0;
        }

        .topbar .nav-link {
            color: var(--primary-color);
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .topbar .nav-link:hover {
            color: var(--text-muted);
        }

        .main-content {
            padding: 2rem;
            background-color: var(--secondary-color);
        }

        .card {
            border: 1px solid var(--border-light);
            border-radius: 0;
            box-shadow: none;
            background-color: var(--secondary-color);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: var(--secondary-color);
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem;
            border-radius: 0;
        }

        .card-header h6 {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 1.125rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--primary-color);
            margin: 0;
        }

        .btn {
            border-radius: 0;
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: transparent;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: var(--secondary-color);
        }

        .btn-success:hover {
            background-color: transparent;
            border-color: var(--success-color);
            color: var(--success-color);
        }

        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
            color: var(--secondary-color);
        }

        .btn-info:hover {
            background-color: transparent;
            border-color: var(--info-color);
            color: var(--info-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: var(--primary-color);
        }

        .btn-warning:hover {
            background-color: transparent;
            border-color: var(--warning-color);
            color: var(--warning-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: var(--secondary-color);
        }

        .btn-danger:hover {
            background-color: transparent;
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .table {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
        }

        .table th {
            border-top: none;
            border-bottom: 1px solid var(--border-light);
            color: var(--primary-color);
            font-weight: 300;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            padding: 1rem 0.75rem;
        }

        .table td {
            border-top: 1px solid var(--border-light);
            padding: 1rem 0.75rem;
            color: var(--primary-color);
        }

        .badge {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            border-radius: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content-wrapper {
                margin-left: 0;
            }

            .mobile-sidebar-toggle {
                display: block !important;
            }
        }

        .mobile-sidebar-toggle {
            display: none;
        }
    </style>

    @stack('styles')
    <style>
        /* Hide admin navigation and sidebar when printing */
        @media print {
            .sidebar,
            .topbar,
            .mobile-sidebar-toggle {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            /* Use full-width content for printouts */
            .content-wrapper {
                margin-left: 0 !important;
                background: #fff !important;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            {{-- <i class="fas fa-gem me-2"></i> --}}
            Jesica Riad
        </a>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                    href="{{ route('admin.products.index') }}">
                    <i class="fas fa-shopping-bag"></i>
                    Products
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.collections.*') ? 'active' : '' }}"
                    href="{{ route('admin.collections.index') }}">
                    <i class="fas fa-layer-group"></i>
                    Collections
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                    href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-clipboard-list"></i>
                    Orders
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.special-orders.*') ? 'active' : '' }}"
                    href="{{ route('admin.special-orders.index') }}">
                    <i class="fas fa-star"></i>
                    Special Orders
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"
                    href="{{ route('admin.clients.index') }}">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}"
                    href="{{ route('admin.analytics.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    Analytics
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                    href="{{ route('admin.settings.edit') }}">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.newsletter.*') ? 'active' : '' }}"
                    href="{{ route('admin.newsletter.send.form') }}">
                    <i class="fas fa-newspaper"></i>
                    Newsletter
                </a>
            </li>

            <li class="nav-item mt-4">
                <a class="nav-link" href="{{ route('home') }}" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Website
                </a>
            </li>

            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent text-start w-100">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>


    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Topbar -->
        <nav class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-link mobile-sidebar-toggle me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h4 mb-0">@yield('title', 'Admin Dashboard')</h1>
            </div>

            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, {{ auth()->user()->name }}</span>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        Account
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @include('layouts.partials.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-sidebar-toggle');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggle.contains(event.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>

</html>
