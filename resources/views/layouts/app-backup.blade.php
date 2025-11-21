<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Jesica Riad') - Luxury Fashion</title>

        <!-- Adobe Fonts - Futura PT -->
        <link rel="stylesheet" href="https://use.typekit.net/ckz0ivc.css">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

            body {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                line-height: 1.6;
                color: var(--primary-color);
                background-color: var(--secondary-color);
                font-size: var(--font-size-base);
            }

            /* Typography */
            .brand-name {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
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
                font-weight: 300;
                letter-spacing: 0.05em;
                line-height: 1.2;
            }

            .section-title {
                font-size: var(--font-size-2xl);
                font-weight: 300;
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
                font-weight: 400;
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

            /* Buttons */
            .btn-primary {
                background-color: var(--primary-color);
                border: 1px solid var(--primary-color);
                color: var(--secondary-color);
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
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
                font-weight: 400;
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
                font-weight: 300;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                margin-bottom: var(--spacing-md);
            }

            .hero-subtitle {
                font-size: var(--font-size-xl);
                font-weight: 300;
                letter-spacing: 0.15em;
                text-transform: uppercase;
                color: var(--text-muted);
                margin-bottom: var(--spacing-lg);
            }

            .hero-description {
                font-size: var(--font-size-lg);
                font-weight: 300;
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
                font-weight: 300;
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
                font-weight: 300;
                letter-spacing: 0.05em;
                margin-bottom: var(--spacing-sm);
            }

            .product-price {
                font-size: var(--font-size-lg);
                font-weight: 400;
                color: var(--primary-color);
                margin-bottom: var(--spacing-lg);
            }

            /* Currency */
            .currency {
                font-size: var(--font-size-sm);
                font-weight: 300;
            }

            /* Dropdown */
            .dropdown-menu {
                border: 1px solid var(--border-light);
                border-radius: 0;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .dropdown-item {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
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
                font-weight: 300;
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
        </style>
    </head>
                font-size: 16px;
                letter-spacing: 0.02em;
            }

            /* Typography */
            h1, h2, h3, h4, h5, h6 {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                line-height: 1.2;
                letter-spacing: 0.05em;
                margin-bottom: var(--spacing-sm);
            }

            h1 { font-size: 3rem; }
            h2 { font-size: 2.5rem; }
            h3 { font-size: 2rem; }
            h4 { font-size: 1.5rem; }
            h5 { font-size: 1.25rem; }
            h6 { font-size: 1rem; }

            p {
                margin-bottom: var(--spacing-sm);
                font-weight: 300;
                color: var(--text-secondary);
            }

            a {
                color: var(--text-primary);
                text-decoration: none;
                transition: all 0.3s ease;
            }

            a:hover {
                color: var(--text-secondary);
            }

            /* Minimalist Navigation */
            .navbar {
                background: var(--secondary-color);
                border-bottom: 1px solid var(--border-color);
                padding: var(--spacing-sm) 0;
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .brand-name {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 1.5rem;
                letter-spacing: 0.15em;
                text-transform: uppercase;
                color: var(--text-primary);
            }

            .nav-link {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 0.9rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: var(--text-secondary);
                padding: 0.5rem 1rem;
                border: none;
                background: none;
            }

            .nav-link:hover,
            .nav-link.active {
                color: var(--text-primary);
            }

            /* Buttons */
            .btn {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 0.9rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                padding: 0.75rem 2rem;
                border: 1px solid var(--text-primary);
                background: transparent;
                color: var(--text-primary);
                transition: all 0.3s ease;
                border-radius: 0;
            }

            .btn:hover {
                background: var(--text-primary);
                color: var(--secondary-color);
            }

            .btn-primary {
                background: var(--text-primary);
                color: var(--secondary-color);
                border-color: var(--text-primary);
            }

            .btn-primary:hover {
                background: transparent;
                color: var(--text-primary);
            }

            /* Cards */
            .card {
                border: none;
                border-radius: 0;
                box-shadow: none;
                background: var(--secondary-color);
            }

            .card-img-top {
                border-radius: 0;
            }

            .card-body {
                padding: var(--spacing-sm);
            }

            .card-title {
                font-weight: 400;
                font-size: 1rem;
                letter-spacing: 0.05em;
                margin-bottom: 0.5rem;
            }

            .card-text {
                font-size: 0.85rem;
                color: var(--text-light);
                line-height: 1.4;
            }

            /* Sections */
            .section {
                padding: var(--spacing-xl) 0;
            }

            .section-sm {
                padding: var(--spacing-lg) 0;
            }

            /* Container */
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 var(--spacing-sm);
            }

            /* Forms */
            .form-control {
                border: none;
                border-bottom: 1px solid var(--border-color);
                border-radius: 0;
                padding: 0.75rem 0;
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                background: transparent;
            }

            .form-control:focus {
                border-bottom-color: var(--text-primary);
                box-shadow: none;
                background: transparent;
            }

            /* Hero Section */
            .hero {
                min-height: 80vh;
                display: flex;
                align-items: center;
                text-align: center;
                background: var(--secondary-color);
            }

            .hero h1 {
                font-size: 4rem;
                font-weight: 200;
                letter-spacing: 0.1em;
                margin-bottom: var(--spacing-md);
            }

            .hero p {
                font-size: 1.1rem;
                max-width: 600px;
                margin: 0 auto var(--spacing-lg);
            }

            /* Product Grid */
            .product-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: var(--spacing-lg);
                margin: var(--spacing-lg) 0;
            }

            .product-card {
                transition: transform 0.3s ease;
            }

            .product-card:hover {
                transform: translateY(-5px);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 2.5rem;
                }

                .product-grid {
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: var(--spacing-md);
                }

                h1 { font-size: 2rem; }
                h2 { font-size: 1.75rem; }
                h3 { font-size: 1.5rem; }
            }

            /* Remove Bootstrap defaults */
            .text-muted {
                color: var(--text-light) !important;
            }

            .bg-light {
                background-color: var(--accent-color) !important;
            }

            /* Minimal cart icon */
            .cart-icon {
                position: relative;
                font-size: 1rem;
            }

            .cart-count {
                position: absolute;
                top: -8px;
                right: -8px;
                background: var(--text-primary);
                color: var(--secondary-color);
                border-radius: 50%;
                width: 18px;
                height: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                font-weight: 400;
            }

            /* Hero Section */
            .hero-section {
                padding: var(--section-padding-large) 0;
                background: var(--color-background);
            }

            .hero-title {
                font-family: var(--font-futura);
                font-weight: 300;
                font-size: 4rem;
                line-height: 1.1;
                margin-bottom: var(--spacing-sm);
                color: var(--color-primary);
            }

            .hero-subtitle {
                font-family: var(--font-futura);
                font-weight: 500;
                font-size: 2.5rem;
                color: var(--color-text);
                margin-bottom: var(--spacing-lg);
            }

            .hero-description {
                font-size: 1.25rem;
                line-height: 1.6;
                color: var(--color-text-muted);
                margin-bottom: var(--spacing-xl);
                max-width: 500px;
            }

            .hero-image {
                text-align: center;
            }

            .hero-image img {
                max-width: 100%;
                height: auto;
                border-radius: var(--border-radius);
            }

            /* Collections Grid */
            .collections-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: var(--spacing-xl);
            }

            .collection-card {
                background: white;
                border-radius: var(--border-radius);
                overflow: hidden;
                transition: var(--transition);
            }

            .collection-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .collection-image {
                width: 100%;
                height: 250px;
                object-fit: cover;
            }

            .collection-image.placeholder {
                background: var(--color-light);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--color-text-muted);
            }

            .collection-content {
                padding: var(--spacing-lg);
            }

            .collection-title {
                font-size: 1.5rem;
                font-weight: 500;
                margin-bottom: var(--spacing-sm);
                color: var(--color-text);
            }

            .collection-description {
                color: var(--color-text-muted);
                margin-bottom: var(--spacing-lg);
                line-height: 1.6;
            }

            /* Products Grid */
            .products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: var(--spacing-lg);
            }

            .product-card {
                background: white;
                border-radius: var(--border-radius);
                overflow: hidden;
                transition: var(--transition);
            }

            .product-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .product-image {
                width: 100%;
                height: 250px;
                object-fit: cover;
            }

            .product-image.placeholder {
                background: var(--color-light);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--color-text-muted);
            }

            .product-content {
                padding: var(--spacing-lg);
            }

            .product-title {
                font-size: 1.25rem;
                font-weight: 500;
                margin-bottom: var(--spacing-xs);
                color: var(--color-text);
            }

            .product-price {
                font-size: 1.5rem;
                font-weight: 600;
                color: var(--color-primary);
                margin-bottom: var(--spacing-md);
            }

            /* About Section */
            .about-image {
                text-align: center;
            }

            .about-image img {
                max-width: 100%;
                height: auto;
                border-radius: var(--border-radius);
            }

            .text-large {
                font-size: 1.25rem;
                line-height: 1.6;
                margin-bottom: var(--spacing-lg);
                color: var(--color-text);
            }

            /* Background Variants */
            .bg-light {
                background-color: var(--color-background) !important;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .hero-title {
                    font-size: 2.5rem;
                }

                .hero-subtitle {
                    font-size: 1.75rem;
                }

                .collections-grid {
                    grid-template-columns: 1fr;
                }

                .products-grid {
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                }
            }
        </style>

            .brand-name {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 1.8rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: #2c3e50;
            }

            .futura-light {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
            }

            .futura-book {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
            }

            .futura-medium {
                font-family: 'futura-pt', sans-serif;
                font-weight: 500;
            }

            .futura-bold {
                font-family: 'futura-pt', sans-serif;
                font-weight: 700;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                letter-spacing: 0.05em;
            }

            .btn {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .nav-link {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body>
        @include('layouts.navigation')

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
