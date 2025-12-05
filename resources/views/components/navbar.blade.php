@props(['activeRoute' => ''])

<nav class="iris-navbar" aria-label="Main navigation">
    <div class="iris-navbar-container">
        <div class="iris-navbar-main">
            <!-- Left: Logo -->
            <a href="/" class="iris-navbar-logo-link">
                <div class="iris-navbar-logo">
                    <img src="{{ asset('images/signature-logo.png') }}" alt="Jesica Riad Signature" class="iris-logo-image"
                        style="filter: invert(1); height: 50px;" loading="lazy" />
                </div>
            </a>

            <!-- Right: Icons and Menu Toggle -->
            <div class="iris-navbar-right">
                <x-icon-button icon="search" :href="route('search')" aria-label="Search" class="iris-icon-btn" />

                <a href="{{ Auth::check() ? route('profile.edit') : route('login') }}" class="iris-icon-btn"
                    aria-label="Account">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                @auth
                <a href="{{ route('special-orders.index') }}" class="iris-icon-btn" aria-label="Request Special Order" title="Request Special Order">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                    </svg>
                </a>
                @endauth

                <a href="{{ route('cart.index') }}" class="iris-icon-btn cart-icon-wrapper" aria-label="Cart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    @php
                        $cart = session()->get('cart', []);
                        $cartCount = array_sum(array_column($cart, 'quantity'));
                    @endphp
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>

                <!-- Mobile Menu Toggle Button -->
                <button id="iris-menu-toggle" class="iris-menu-toggle" type="button" aria-label="Open menu">
                    MENU
                </button>
            </div>
        </div>

        <!-- Fullscreen Mobile Navigation -->
        <div class="iris-fullscreen-nav" id="iris-mobile-nav">
            <div class="iris-nav-content">
                <!-- Close Button -->
                <button id="iris-menu-close" class="iris-nav-close" type="button" aria-label="Close menu">
                    ×
                </button>

                <div class="iris-nav-links-mobile">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>
                    <x-nav-link :href="route('collections.index')" :active="request()->routeIs('collections.*')">Collections</x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>

                    @auth
                        @if (Auth::user()->role->name === 'ADMIN')
                            <x-nav-link :href="route('admin.dashboard')">Admin</x-nav-link>
                        @endif
                            <x-nav-link :href="route('special-orders.index')">Special Orders</x-nav-link>
                        <x-nav-link :href="route('orders.index')">Orders</x-nav-link>
                        <x-nav-link :href="route('cart.index')">Cart</x-nav-link>
                        <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                            @csrf
                            <button type="submit" class="iris-nav-link iris-nav-button">Logout</button>
                        </form>
                    @else
                        <x-nav-link :href="route('login')">Login</x-nav-link>
                        <x-nav-link :href="route('register')">Register</x-nav-link>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    html,
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box !important;
        outline: none !important;
        overflow-x: hidden;
    }

    .iris-navbar {
        background: transparent;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 9999999999999999999999999;
        /* Smooth transition for background color */
        transition: all 0.5s ease-in;
        /* Add subtle shadow when scrolled */
        box-shadow: 0 0 0 rgba(0, 0, 0, 0);
        max-width: 100%;
        box-sizing: border-box !important
    }

    /* Scrolled state - White background with shadow */
    .iris-navbar.scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .iris-navbar-container {
        margin: 0 auto;
        padding: 0 2rem;
    }

    .iris-navbar-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 0;
        position: relative;
    }

    /* Default: Logo on left, icons on right */
    .iris-navbar-logo-link {
        position: absolute;
        left: 2rem;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .iris-navbar-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1.5rem;
        margin-left: auto;
    }

    /* Home page: Logo in center, icons on right */
    .is-home .iris-navbar-logo-link {
        left: 50%;
        transform: translateX(-50%);
    }

    .iris-navbar-logo {
        background-color: transparent;
    }

    .iris-icon-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        color: #000;
        font-size: 1.1rem;
        transition: opacity 0.2s;
    }

    .iris-icon-btn:hover {
        opacity: 0.7;
    }

    .cart-icon-wrapper {
        position: relative;
        display: inline-block;
    }

    .cart-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #000;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 600;
        line-height: 1;
    }

    .iris-menu-toggle {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        letter-spacing: 1.5px;
        color: #000;
        transition: all 0.3s ease;
    }

    .iris-menu-toggle:hover {
        opacity: 0.7;
    }

    /* Fullscreen Mobile Menu */
    .iris-fullscreen-nav {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: white;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.35s ease, visibility 0.35s ease;
        pointer-events: none;
        overflow-y: auto;
    }

    .iris-fullscreen-nav.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .iris-nav-content {
        position: relative;
        text-align: center;
        padding: 2rem;
        width: 100%;
    }

    .iris-nav-close {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: none;
        border: none;
        font-size: 3rem;
        font-weight: 300;
        cursor: pointer;
        color: #000;
        line-height: 1;
        padding: 0.5rem;
        transition: opacity 0.2s;
    }

    .iris-nav-close:hover {
        opacity: 0.6;
    }

    .iris-nav-links-mobile {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2.5rem;
        margin-top: 4rem;
    }

    .iris-nav-links-mobile .iris-nav-link,
    .iris-nav-button {
        font-size: 1.8rem;
        font-weight: 400;
        letter-spacing: 1.2px;
        color: #000;
        text-decoration: none;
        transition: opacity 0.2s;
    }

    .iris-nav-links-mobile .iris-nav-link:hover,
    .iris-nav-button:hover {
        opacity: 0.7;
    }

    .iris-navbar-logo-link {
        background-color: transparent;
    }


    .iris-nav-button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    @media (max-width: 768px) {
        .iris-navbar-container {
            padding: 0 1rem;
        }

        .is-home .iris-navbar-logo-link {
            left: 0rem;
            transform: translateX(0);
        }

        .iris-navbar-logo-link {
            left: 1rem;
        }

        .iris-navbar-logo-link {
            background-color: transparent;
        }


        .iris-navbar-main {
            padding: 1rem 0;
        }


        .iris-navbar-right {
            gap: 5px;
        }
    }
</style>

    <!-- Hide navbars when printing -->
    <style>
        @media print {
            .iris-navbar,
            .iris-fullscreen-nav {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>

<!-- Add home page class to navbar container -->
@if (request()->routeIs('home'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.iris-navbar').classList.add('is-home');
        });
    </script>
@endif

<!-- Enhanced JavaScript for Menu Toggle + Scroll Effect -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const navbar = document.querySelector('.iris-navbar');
        const mobileNav = document.getElementById('iris-mobile-nav');
        const openBtn = document.getElementById('iris-menu-toggle');
        const closeBtn = document.getElementById('iris-menu-close');

        // Menu toggle functions
        const openMenu = () => mobileNav.classList.add('show');
        const closeMenu = () => mobileNav.classList.remove('show');

        openBtn?.addEventListener('click', openMenu);
        closeBtn?.addEventListener('click', closeMenu);

        // Close when clicking on the backdrop
        mobileNav?.addEventListener('click', (e) => {
            if (e.target === mobileNav) closeMenu();
        });

        // Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('show')) {
                closeMenu();
            }
        });

        // Navbar scroll effect
        let ticking = false;

        function updateNavbar() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        }

        // Listen for scroll events
        window.addEventListener('scroll', requestTick, {
            passive: true
        });

        // Initial check
        updateNavbar();
    });
</script>
