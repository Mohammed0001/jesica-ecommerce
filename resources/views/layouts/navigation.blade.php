<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center w-100">
            <!-- Brand with Signature Logo -->
            <a href="{{ route('home') }}" class="brand-name d-flex align-items-center">
                <img src="{{ asset('images/signature-logo.png') }}" alt="Jesica Riad" style="" class="brand-logo me-3">
                <span class="d-none d-sm-inline">Jesica Riad</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="d-none d-md-flex align-items-center gap-4">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    Home
                </a>
                <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                    Collections
                </a>
                <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                    <span class="cart-icon">
                        Cart
                        <span class="cart-count">0</span>
                    </span>
                </a>

                <!-- Instagram Link -->
                {{-- <a href="https://www.instagram.com/jessica.riad/" target="_blank" class="instagram-link nav-link">
                    <i class="fab fa-instagram"></i>
                </a> --}}

                @auth
                    @if(Auth::user()->role->name === 'ADMIN')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            Admin
                        </a>
                    @endif
                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
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
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="nav-link">Register</a>
                @endauth
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="d-md-none btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div class="collapse d-md-none mt-3" id="mobileNav">
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('home') }}" class="nav-link">Home</a>
                <a href="{{ route('collections.index') }}" class="nav-link">Collections</a>
                <a href="{{ route('cart.index') }}" class="nav-link">Cart</a>
                <a href="https://www.instagram.com/jessica.riad/" target="_blank" class="nav-link">
                    <i class="fab fa-instagram"></i> Instagram
                </a>

                @auth
                    @if(Auth::user()->role->name === 'ADMIN')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="nav-link">Profile</a>
                    <a href="{{ route('orders.index') }}" class="nav-link">Orders</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link btn text-start p-0">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="nav-link">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
