<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Jesica Riad') - Luxury Fashion</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/signature-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/signature-logo.png') }}">

        <!-- Adobe Fonts - Futura PT -->
        <!-- INSTRUCTION: Replace the placeholder URL below with your Adobe Fonts embed code -->
        <!-- Example: <link rel="stylesheet" href="https://use.typekit.net/your-kit-id.css"> -->
        <link rel="stylesheet" href="https://use.typekit.net/ckz0ivc.css">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Navbar CSS -->
        <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --primary-color: #000000;
                --secondary-color: #ffffff;
                --text-muted: #6c757d;
                --border-light: #f0f0f0;
                --spacing-xs: 0.25rem;
                --spacing-sm: 0.5rem;
                --spacing-md: 1rem;
                --spacing-lg: 2rem;
                --spacing-xl: 4rem;
                --font-size-xs: 0.75rem;
                --font-size-sm: 0.875rem;
                --font-size-base: 1rem;
                --font-size-lg: 1.25rem;
                --font-size-xl: 1.5rem;
                --font-size-2xl: 2rem;
                --font-size-3xl: 3rem;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body, a, button, input, h1, h2, h3, h4, h5, h6, p, li {
                font-family: "Futura PT", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                font-weight: 200;
                line-height: 1.6;
                color: var(--primary-color);
                background-color: var(--secondary-color);
                font-size: var(--font-size-base);
            }

            /* Typography */
            .brand-name {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-xl);
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: var(--primary-color);
                text-decoration: none;
            }

            .brand-name:hover {
                color: var(--primary-color);
                text-decoration: none;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                letter-spacing: 0.05em;
                line-height: 1.2;
            }

            .section-title {
                font-size: var(--font-size-2xl);
                font-weight: 200;
                text-align: center;
                margin-bottom: var(--spacing-lg);
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            /* Navigation */
            .navbar {
                background: var(--secondary-color);
                border-bottom: 1px solid var(--border-light);
                padding: var(--spacing-md) 0;
            }

            .nav-link {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-sm);
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: var(--primary-color);
                text-decoration: none;
                padding: var(--spacing-sm) var(--spacing-md);
                transition: all 0.3s ease;
            }

            .nav-link:hover,
            .nav-link.active {
                color: var(--text-muted);
                text-decoration: none;
            }

            /* Brand Logo */
            .brand-logo {
                height: 40px;
                width: auto;
                filter: invert(1);
                transition: all 0.3s ease;
            }

            .brand-logo:hover {
                opacity: 0.7;
            }

            .brand-name {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-lg);
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: var(--primary-color);
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .brand-name:hover {
                color: var(--text-muted);
            }

            /* Instagram Link */
            .instagram-link {
                color: var(--primary-color);
                font-size: var(--font-size-base);
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .instagram-link:hover {
                color: var(--text-muted);
            }

            /* Buttons */
            .btn-primary {
                background-color: var(--primary-color);
                border: 1px solid var(--primary-color);
                color: var(--secondary-color);
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-sm);
                letter-spacing: 0.1em;
                text-transform: uppercase;
                padding: var(--spacing-md) var(--spacing-xl);
                border-radius: 0;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                background-color: var(--secondary-color);
                color: var(--primary-color);
                border-color: var(--primary-color);
            }

            .btn-outline {
                background-color: transparent;
                border: 1px solid var(--primary-color);
                color: var(--primary-color);
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-sm);
                letter-spacing: 0.1em;
                text-transform: uppercase;
                padding: var(--spacing-md) var(--spacing-xl);
                border-radius: 0;
                text-decoration: none;
                display: inline-block;
                text-align: center;
                transition: all 0.3s ease;
            }

            .btn-outline:hover {
                background-color: var(--primary-color);
                color: var(--secondary-color);
                text-decoration: none;
            }

            /* Layout */
            .section {
                padding: var(--spacing-xl) 0;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 var(--spacing-md);
            }

            /* Hero Section */
            .hero-section {
                min-height: 80vh;
                display: flex;
                align-items: center;
                padding: var(--spacing-xl) 0;
            }

            .hero-title {
                font-size: var(--font-size-3xl);
                font-weight: 200;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                margin-bottom: var(--spacing-md);
            }

            .hero-subtitle {
                font-size: var(--font-size-xl);
                font-weight: 200;
                letter-spacing: 0.15em;
                text-transform: uppercase;
                color: var(--text-muted);
                margin-bottom: var(--spacing-lg);
            }

            .hero-description {
                font-size: var(--font-size-lg);
                font-weight: 200;
                line-height: 1.8;
                color: var(--text-muted);
                margin-bottom: var(--spacing-xl);
                max-width: 500px;
            }

            /* Collections Grid */
            .collections-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: var(--spacing-xl);
                margin-top: var(--spacing-xl);
            }

            .collection-card {
                background: var(--secondary-color);
                overflow: hidden;
                transition: transform 0.3s ease;
            }

            .collection-card:hover {
                transform: translateY(-5px);
            }

            .collection-image {
                width: 100%;
                height: 400px;
                object-fit: cover;
                display: block;
            }

            .collection-content {
                padding: var(--spacing-lg);
                text-align: center;
            }

            .collection-title {
                font-size: var(--font-size-xl);
                font-weight: 200;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                margin-bottom: var(--spacing-md);
            }

            .collection-title a {
                color: var(--primary-color);
                text-decoration: none;
            }

            .collection-description {
                font-size: var(--font-size-base);
                color: var(--text-muted);
                line-height: 1.8;
                margin-bottom: var(--spacing-lg);
            }

            /* Products Grid */
            .products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: var(--spacing-lg);
                margin-top: var(--spacing-xl);
            }

            .product-card {
                background: var(--secondary-color);
                overflow: hidden;
                transition: transform 0.3s ease;
            }

            .product-card:hover {
                transform: translateY(-3px);
            }

            .product-image {
                width: 100%;
                height: 350px;
                object-fit: cover;
                display: block;
            }

            .product-content {
                padding: var(--spacing-lg);
                text-align: center;
            }

            .product-title {
                font-size: var(--font-size-lg);
                font-weight: 200;
                letter-spacing: 0.05em;
                margin-bottom: var(--spacing-sm);
            }

            .product-price {
                font-size: var(--font-size-lg);
                font-weight: 200;
                color: var(--primary-color);
                margin-bottom: var(--spacing-lg);
            }

            /* Currency */
            .currency {
                font-size: var(--font-size-sm);
                font-weight: 200;
            }

            /* Dropdown */
            .dropdown-menu {
                border: 1px solid var(--border-light);
                border-radius: 0;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .dropdown-item {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                font-size: var(--font-size-sm);
                letter-spacing: 0.05em;
                color: var(--primary-color);
                padding: var(--spacing-sm) var(--spacing-md);
            }

            .dropdown-item:hover {
                background-color: var(--border-light);
                color: var(--primary-color);
            }

            /* Form Controls */
            .form-control, .form-select {
                font-family: 'futura-pt', sans-serif;
                font-weight: 200;
                border: 1px solid var(--border-light);
                border-radius: 0;
                padding: var(--spacing-sm) var(--spacing-md);
            }

            .form-control:focus, .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .hero-title {
                    font-size: var(--font-size-2xl);
                }

                .section-title {
                    font-size: var(--font-size-xl);
                }

                .collections-grid {
                    grid-template-columns: 1fr;
                }

                .products-grid {
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                }

                .container {
                    padding: 0 var(--spacing-sm);
                }
            }

            /* Image Placeholder */
            .placeholder {
                background-color: var(--border-light);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--text-muted);
                font-size: var(--font-size-sm);
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            /* Mobile Navigation */
            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(0,0,0,0.8)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 8h24M4 16h24M4 24h24'/%3E%3C/svg%3E");
            }

            /* About Section */
            .about-image {
                text-align: center;
            }

            .about-image img {
                max-width: 100%;
                height: auto;
            }

            .text-large {
                font-size: var(--font-size-lg);
                line-height: 1.8;
                margin-bottom: var(--spacing-lg);
                color: var(--primary-color);
            }

            /* Background Variants */
            .bg-light {
                background-color: #fafafa !important;
            }
        </style>
    </head>

    <body>
        <div id="app">
            <x-navbar />

            <main>
                @yield('content')
            </main>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
