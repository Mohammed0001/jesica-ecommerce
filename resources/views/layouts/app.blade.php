<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Each directive needs whitespace before its @, or Blade leaves it as
         literal text. Browsers collapse the whitespace in a <title>. --}}
    <title>
        @hasSection('title')
            @yield('title') | JESSICA RIAD
        @else
            JESSICA RIAD - COOL LUXURY
        @endif
    </title>
    <meta name="description" content="@yield('meta_description', 'JESSICA RIAD — cool luxury. Collectible fashion pieces that merge craftsmanship, heritage and storytelling.')">

    {{-- Open the TCP + TLS connections to the third-party origins while the
         HTML is still being parsed, rather than when the tags are reached. --}}
    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="preconnect" href="https://p.typekit.net" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <!-- Example: <link rel="stylesheet" href="https://use.typekit.net/your-kit-id.css"> -->
    <link rel="stylesheet" href="https://use.typekit.net/ckz0ivc.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome is decorative only, so it is loaded without blocking the
         first paint: media="print" makes it a low-priority fetch, and onload
         promotes it to the real stylesheet once it arrives. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          media="print" onload="this.media='all';this.onload=null;">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </noscript>

    <!-- Navbar CSS -->
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // The intro loader belongs to the visit, not to every page view. Mark
        // the document before first paint so the CSS below decides whether the
        // loader is ever painted at all: no flash, no second appearance when
        // the visitor navigates on to another page.
        (function () {
            try {
                if (!sessionStorage.getItem('jr_intro_shown')) {
                    document.documentElement.classList.add('jr-intro');
                    sessionStorage.setItem('jr_intro_shown', '1');
                }
            } catch (e) {
                // Private browsing can throw on sessionStorage; skip the intro.
            }
        })();
    </script>
@stack('styles')
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
            box-sizing: border-box !important;
            outline: none !important;
        }

        body,
        a,
        button,
        input,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        li {
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

        /* The card itself is an <a>, so the entire tile is one click target. */
        .product-card {
            background: var(--secondary-color);
            overflow: hidden;
            transition: transform 0.3s ease;
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .product-card:hover,
        .product-card:focus {
            transform: translateY(-3px);
            color: inherit;
            text-decoration: none;
        }

        .product-card:focus-visible {
            outline: 2px solid var(--primary-color) !important;
            outline-offset: 3px;
        }

        /* Hovering anywhere on the card fills the View Details button, so the
           tile reads as a single control rather than a button in a box. */
        .product-card:hover .btn-outline {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }

        .product-image-wrap {
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 350px;
            object-fit: contain;
            background-color: #f8f9fa;
            display: block;
        }

        .sold-out-badge {
            position: absolute;
            top: var(--spacing-sm);
            left: var(--spacing-sm);
            background: #212529;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 2;
        }

        .sale-badge {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            background: #b02a37;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 2;
        }

        .price-sale {
            color: #b02a37;
        }

        .price-original {
            color: var(--text-muted);
            font-size: var(--font-size-sm);
            margin-left: var(--spacing-sm);
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
        .form-control,
        .form-select {
            font-family: 'futura-pt', sans-serif;
            font-weight: 200;
            border: 1px solid var(--border-light);
            border-radius: 0;
            padding: var(--spacing-sm) var(--spacing-md);
        }

        .form-control:focus,
        .form-select:focus {
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

        /* Intro Loader
           Hidden by default and only painted when the head script decided this
           is the first page of the visit. Covers every viewport size: inset:0
           rather than 100vw, which would otherwise add a horizontal scrollbar. */
        #global-loader {
            display: none;
        }

        html.jr-intro #global-loader {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary-color);
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.4s ease;
        }

        html.jr-intro #global-loader.is-hiding {
            opacity: 0;
            pointer-events: none;
        }

        #global-loader img {
            height: 80px;
            width: auto;
            max-width: 60vw;
        }

        @media (prefers-reduced-motion: reduce) {
            html.jr-intro #global-loader {
                transition: none;
            }
        }

        /* Flash Messages */
        .flash-stack {
            position: fixed;
            top: 96px;
            right: 1rem;
            left: 1rem;
            z-index: 1080;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        @media (min-width: 576px) {
            .flash-stack {
                left: auto;
                max-width: 30rem;
            }
        }

        .flash {
            pointer-events: auto;
            padding: var(--spacing-md);
            border: 1px solid var(--primary-color);
            background: var(--secondary-color);
            font-size: var(--font-size-sm);
            line-height: 1.5;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }

        .flash--error {
            border-color: #b02a37;
            color: #b02a37;
        }

        .flash--success {
            border-color: #146c43;
            color: #146c43;
        }

        .flash--info {
            border-color: #0a58ca;
            color: #0a58ca;
        }

        .flash__body {
            flex: 1;
        }

        .flash__list {
            margin: 0.35rem 0 0;
            padding-left: 1.1rem;
        }

        .flash__close {
            background: none;
            border: 0;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
            color: inherit;
            padding: 0;
        }
    </style>
</head>

<body>
    <!-- Global Loading Screen (first page of a visit only) -->
    <div id="global-loader" role="status" aria-live="polite">
        <img src="{{ asset('images/loader.png') }}" alt="JESSICA Riad" fetchpriority="high">
    </div>

    <div id="app">
        <x-navbar />

        {{-- Server-side messages: without this region every ->with('error', ...)
             the controllers set would be discarded unseen. --}}
        @php
            $flashes = collect([
                'error' => session('error'),
                'success' => session('success'),
                'info' => session('info'),
                'status' => session('status'),
            ])->filter(fn ($message) => filled($message) && is_string($message));
        @endphp

        @if ($flashes->isNotEmpty() || $errors->any())
            <div class="flash-stack" id="flashStack">
                @foreach ($flashes as $type => $message)
                    <div class="flash flash--{{ $type === 'status' ? 'info' : $type }}">
                        <div class="flash__body">{{ $message }}</div>
                        <button type="button" class="flash__close" aria-label="Dismiss">&times;</button>
                    </div>
                @endforeach

                @if ($errors->any())
                    <div class="flash flash--error">
                        <div class="flash__body">
                            {{ $errors->count() === 1 ? 'Please fix this before continuing:' : 'Please fix these ' . $errors->count() . ' problems before continuing:' }}
                            <ul class="flash__list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="flash__close" aria-label="Dismiss">&times;</button>
                    </div>
                @endif
            </div>
        @endif

        <main style="margin-top: 80px;">
            @yield('content')
        </main>

        @include('layouts.partials.footer')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <script>
        (function () {
            var loader = document.getElementById('global-loader');

            function hideLoader() {
                if (!loader) return;
                loader.classList.add('is-hiding');
                setTimeout(function () { loader.style.display = 'none'; }, 400);
            }

            if (document.documentElement.classList.contains('jr-intro')) {
                window.addEventListener('load', hideLoader);
                // A single stalled image must never leave the visitor staring
                // at the logo, so cap the intro regardless of load events.
                setTimeout(hideLoader, 4000);
            }

            // Dismissible flash messages, auto-clearing the non-critical ones.
            document.querySelectorAll('.flash__close').forEach(function (btn) {
                btn.addEventListener('click', function () { btn.closest('.flash').remove(); });
            });

            document.querySelectorAll('.flash--success, .flash--info').forEach(function (flash) {
                setTimeout(function () { flash.remove(); }, 6000);
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>
