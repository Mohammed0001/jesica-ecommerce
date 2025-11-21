@props([
    'href' => '#',
    'active' => false
])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'iris-nav-link' . ($active ? ' iris-nav-link--active' : '')]) }}
>
    {{ $slot }}
</a>

<style>
.iris-nav-link {
    font-family: "Futura PT", system-ui, sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #000;
    text-decoration: none;
    padding: 0.5rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
}

.iris-nav-link:hover {
    color: #8B4513;
    text-decoration: none;
}

.iris-nav-link:focus {
    outline: 2px solid #8B4513;
    outline-offset: 4px;
    border-radius: 2px;
}

.iris-nav-link--active {
    color: #8B4513;
    border-bottom-color: #8B4513;
}

.iris-nav-link--active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #8B4513;
}

/* Mobile styles */
@media (max-width: 767px) {
    .iris-nav-link {
        display: block;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
        border-bottom-width: 1px;
    }

    .iris-nav-link:last-child {
        border-bottom: none;
    }

    .iris-nav-link--active::after {
        display: none;
    }

    .iris-nav-link--active {
        border-bottom-color: #f0f0f0;
        background: rgba(139, 69, 19, 0.05);
    }
}

/* Dropdown integration */
.dropdown .iris-nav-link.dropdown-toggle {
    display: flex;
    align-items: center;
}

.dropdown .iris-nav-link.dropdown-toggle::after {
    margin-left: 0.5rem;
}

.dropdown-menu .dropdown-item {
    font-family: "Futura PT", system-ui, sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
}

.cart-count {
    font-size: 0.75rem;
    font-weight: 400;
}
</style>
