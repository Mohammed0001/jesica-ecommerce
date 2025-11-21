<div class="iris-logo">
    <a href="{{ route('home') }}" class="iris-logo-link">
        <div class="iris-logo-container">
            <!-- Vertical separators -->
            <div class="iris-logo-separator"></div>

            <!-- Logo image -->
            <img
                src="{{ asset('images/signature-logo.png') }}"
                alt="Jesica Riad Signature"
                class="iris-logo-image"
                style="filter: invert(1);"
                loading="lazy"
            />

            <!-- Vertical separators -->
            <div class="iris-logo-separator"></div>
        </div>

        <!-- Brand name -->
        <h1 class="iris-brand-name">JESICA RIAD</h1>
    </a>
</div>

<style>
.iris-logo {
    text-align: center;
}

.iris-logo-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.iris-logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.iris-logo-separator {
    width: 1px;
    height: 40px;
    background: #000;
    opacity: 0.3;
}

.iris-logo-image {
    height: 40px;
    width: auto;
    filter: contrast(1.2);
}

.iris-brand-name {
    font-family: "Futura PT", system-ui, sans-serif;
    font-weight: 200;
    font-size: 1rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
    color: #000;
    transition: color 0.3s ease;
}

.iris-logo-link:hover .iris-brand-name {
    color: #8B4513;
}

@media (max-width: 767px) {
    .iris-logo-separator {
        height: 30px;
    }

    .iris-logo-image {
        height: 30px;
    }

    .iris-brand-name {
        font-size: 0.875rem;
        letter-spacing: 0.15em;
    }
}
</style>
