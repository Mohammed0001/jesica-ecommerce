<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentication') - Jesica Riad</title>

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
            --iris-maroon: #8B4513;
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
        }

        .auth-container {
            min-height: 100vh;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }

        .auth-card {
            background: var(--secondary-color);
            border: 1px solid var(--border-light);
            max-width: 400px;
            width: 100%;
            padding: var(--spacing-xl);
            text-align: center;
        }

        .auth-logo {
            margin-bottom: var(--spacing-xl);
        }

        .auth-logo img {
            height: 50px;
            width: auto;
            filter: contrast(1.2);
            margin-bottom: var(--spacing-md);
        }

        .auth-brand {
            font-size: var(--font-size-lg);
            font-weight: 200;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--primary-color);
            text-decoration: none;
            display: block;
            margin: auto;
            margin-bottom: var(--spacing-lg);
            width: 100%;
        }

        .auth-brand img {
            margin: auto;
            filter: invert(1);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
        }

        .auth-title {
            font-size: var(--font-size-xl);
            font-weight: 200;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: var(--spacing-lg);
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
            text-align: left;
        }

        .form-label {
            font-size: var(--font-size-sm);
            font-weight: 200;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
            display: block;
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--border-light);
            background: var(--secondary-color);
            font-family: "Futura PT", system-ui, sans-serif;
            font-weight: 200;
            font-size: var(--font-size-base);
            color: var(--primary-color);
            border-radius: 0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--iris-maroon);
            box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.1);
        }

        .form-check {
            margin: var(--spacing-lg) 0;
            text-align: left;
        }

        .form-check-input {
            margin-right: var(--spacing-sm);
        }

        .form-check-label {
            font-size: var(--font-size-sm);
            font-weight: 200;
            color: var(--text-muted);
        }

        .btn-auth {
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: var(--secondary-color);
            font-family: "Futura PT", system-ui, sans-serif;
            font-weight: 200;
            font-size: var(--font-size-sm);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: 0;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
            margin-top: var(--spacing-md);
        }

        .btn-auth:hover {
            background-color: var(--iris-maroon);
            border-color: var(--iris-maroon);
            color: var(--secondary-color);
        }

        .auth-link {
            color: var(--text-muted);
            font-size: var(--font-size-sm);
            font-weight: 200;
            letter-spacing: 0.05em;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-link:hover {
            color: var(--iris-maroon);
            text-decoration: none;
        }

        .auth-links {
            margin-top: var(--spacing-lg);
            text-align: center;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: var(--font-size-xs);
            font-weight: 200;
            margin-top: var(--spacing-xs);
            text-align: left;
        }

        .alert {
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            border: 1px solid var(--border-light);
            background: rgba(139, 69, 19, 0.05);
            color: var(--iris-maroon);
            font-size: var(--font-size-sm);
            font-weight: 200;
        }

        @media (max-width: 576px) {
            .auth-container {
                padding: var(--spacing-md);
            }

            .auth-card {
                padding: var(--spacing-lg);
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo and Brand -->
            <div class="auth-logo">
                <a href="{{ route('home') }}" class="auth-brand">
                    <img src="{{ asset('images/signature-logo.png') }}" alt="Jesica Riad Signature" loading="lazy" />
                    Jesica Riad
                </a>
            </div>

            {{ $slot }}
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
