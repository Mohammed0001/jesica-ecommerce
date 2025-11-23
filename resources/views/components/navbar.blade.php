@props(['activeRoute' => ''])

<nav class="iris-navbar" aria-label="Main navigation">
    <div class="iris-navbar-container">
        <div class="iris-navbar-main">
            <!-- Left: Logo -->
            <a href="/"  style="background-color: transparent;">
                <div class="iris-navbar-logo" style="background-color: transparent;">
                    <img src="{{ asset('images/signature-logo.png') }}"
                         alt="Jesica Riad Signature"
                         class="iris-logo-image"
                         style="filter: invert(1); height: 50px;"
                         loading="lazy" />
                </div>
            </a>

            <!-- Right: Icons and Menu Toggle -->
            <div class="iris-navbar-right">
                <x-icon-button icon="search" :href="route('search')" aria-label="Search" class="iris-icon-btn" />

                <a href="{{ Auth::check() ? route('profile.edit') : route('login') }}"
                   class="iris-icon-btn" aria-label="Account">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                <a href="{{ route('cart.index') }}" class="iris-icon-btn" aria-label="Cart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </a>

                <!-- Mobile Menu Toggle Button -->
                <button id="iris-menu-toggle"
                        class="iris-menu-toggle"
                        type="button"
                        aria-label="Open menu">
                    MENU
                </button>
            </div>
        </div>

        <!-- Fullscreen Mobile Navigation -->
        <div class="iris-fullscreen-nav" id="iris-mobile-nav">
            <div class="iris-nav-content">
                <!-- Close Button -->
                <button id="iris-menu-close"
                        class="iris-nav-close"
                        type="button"
                        aria-label="Close menu">
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
    .iris-navbar {
        background: transparent;
        position: sticky;
        top: 0;
        z-index: 1000;

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
    }

    .iris-navbar-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1.5rem;
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

    .iris-icon-btn:hover { opacity: 0.7; }

    .iris-menu-toggle {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        letter-spacing: 1.5px;
        color: #000;
        transition: opacity 0.2s;
    }

    .iris-menu-toggle:hover { opacity: 0.7; }

    /* Fullscreen Mobile Menu */
    .iris-fullscreen-nav {
        position: fixed;
        top: 0; left: 0;
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

    .iris-nav-close:hover { opacity: 0.6; }

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

    .iris-nav-button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    @media (max-width: 768px) {
        .iris-navbar-container { padding: 0 1rem; }
        .iris-navbar-main { padding: 1rem 0; }
        .iris-navbar-right { gap: 1rem; }
    }
</style>

<!-- Vanilla JavaScript for Menu Toggle (No Bootstrap Needed) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileNav = document.getElementById('iris-mobile-nav');
        const openBtn   = document.getElementById('iris-menu-toggle');
        const closeBtn  = document.getElementById('iris-menu-close');

        const openMenu = () => mobileNav.classList.add('show');
        const closeMenu = () => mobileNav.classList.remove('show');

        openBtn?.addEventListener('click', openMenu);
        closeBtn?.addEventListener('click', closeMenu);

        // Close when clicking on the backdrop
        mobileNav?.addEventListener('click', (e) => {
            if (e.target === mobileNav) closeMenu();
        });

        // Optional: Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('show')) {
                closeMenu();
            }
        });
    });
</script>
