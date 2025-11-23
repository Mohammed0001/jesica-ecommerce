@props(['activeRoute' => ''])

<nav class="iris-navbar" aria-label="Main navigation">
    <div class="iris-navbar-container">
        <!-- Top row: search, logo, social icons -->
        <div class="iris-navbar-top">
            <!-- Search Icon -->
            <div class="iris-navbar-left">
                <x-icon-button
                    icon="search"
                    :href="route('search')"
                    aria-label="Search"
                    class="iris-search-btn"
                />
            </div>

            <!-- Center Logo -->
            <div class="iris-navbar-center">
                <x-logo />
            </div>

            <!-- Social Icons -->
            <div class="iris-navbar-right">
                <x-icon-button
                    icon="instagram"
                    href="https://www.instagram.com/jessica.riad/"
                    target="_blank"
                    aria-label="Follow us on Instagram"
                    class="iris-social-btn"
                />
                {{-- <x-icon-button
                    icon="facebook"
                    href="#"
                    target="_blank"
                    aria-label="Follow us on Facebook"
                    class="iris-social-btn"
                /> --}}
            </div>

            <!-- Mobile hamburger -->
            <button class="iris-mobile-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#iris-mobile-nav" aria-controls="iris-mobile-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="iris-hamburger"></span>
                <span class="iris-hamburger"></span>
                <span class="iris-hamburger"></span>
            </button>
        </div>

        <!-- Navigation row -->
        <div class="iris-navbar-nav">
            <div class="iris-nav-links d-none d-md-flex">
                <x-nav-link
                    :href="route('home')"
                    :active="request()->routeIs('home')"
                >
                    Home
                </x-nav-link>

                <x-nav-link
                    :href="route('collections.index')"
                    :active="request()->routeIs('collections.*')"
                >
                    Collections
                </x-nav-link>

                <x-nav-link
                    :href="route('about')"
                    :active="request()->routeIs('about')"
                >
                    About
                </x-nav-link>

                <x-nav-link
                    :href="route('contact')"
                    :active="request()->routeIs('contact')"
                >
                    Contact
                </x-nav-link>

                <x-nav-link
                    :href="route('cart.index')"
                    :active="request()->routeIs('cart.*')"
                >
                    Cart (<span class="cart-count">0</span>)
                </x-nav-link>

                @auth
                    @if(Auth::user()->role->name === 'ADMIN')
                        <x-nav-link :href="route('admin.dashboard')">
                            Admin
                        </x-nav-link>
                    @endif

                    <div class="dropdown">
                        <a href="#" class="iris-nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <x-nav-link :href="route('login')">Login</x-nav-link>
                    <x-nav-link :href="route('register')">Register</x-nav-link>
                @endauth
            </div>

            <!-- Mobile Navigation -->
            <div class="collapse d-md-none" id="iris-mobile-nav">
                <div class="iris-mobile-links">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>
                    <x-nav-link :href="route('collections.index')" :active="request()->routeIs('collections.*')">Collections</x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>
                    <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">Cart</x-nav-link>

                    @auth
                        @if(Auth::user()->role->name === 'ADMIN')
                            <x-nav-link :href="route('admin.dashboard')">Admin</x-nav-link>
                        @endif
                        <x-nav-link :href="route('profile.edit')">Profile</x-nav-link>
                        <x-nav-link :href="route('orders.index')">Orders</x-nav-link>
                        <form method="POST" action="{{ route('logout') }}">
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
    background: white;
    border-bottom: 1px solid #f0f0f0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.iris-navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.iris-navbar-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.iris-navbar-left,
.iris-navbar-right {
    flex: 1;
    display: flex;
    gap: 0.5rem;
}

.iris-navbar-right {
    justify-content: flex-end;
}

.iris-navbar-center {
    flex: 0 0 auto;
}

.iris-navbar-nav {
    padding: 0.75rem 0;
}

.iris-nav-links {
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.iris-mobile-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem 0;
}

.iris-mobile-toggle {
    display: flex;
    flex-direction: column;
    gap: 3px;
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
}

.iris-hamburger {
    width: 20px;
    height: 2px;
    background: #000;
    transition: all 0.3s ease;
}

.iris-nav-button {
    background: none;
    border: none;
    padding: 0;
    text-align: left;
    width: 100%;
}

@media (max-width: 767px) {
    .iris-navbar-left,
    .iris-navbar-right {
        display: none;
    }
}
</style>
