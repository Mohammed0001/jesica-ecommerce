@extends('layouts.app')

@section('title', 'About Jesica Riad')

@section('content')
    <main class="about-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center min-vh-50">
                    <div class="col-lg-6">
                        <h1 class="hero-title">About Jesica Riad</h1>
                        <p class="hero-subtitle">Crafting beautiful, unique pieces with passion and precision</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="{{ asset('images/about-hero.jpg') }}" alt="Jesica Riad at work" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section class="story-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="story-content">
                            <h2 class="section-title">My Story</h2>
                            <div class="story-text">
                                {{-- <p>
                                Welcome to my world of handcrafted artistry. I'm Jesica Riad, and my journey into the realm of
                                handmade creations began with a simple belief: that every object we surround ourselves with
                                should tell a story, evoke emotion, and bring beauty into our daily lives.
                            </p> --}}
                                <p>
                                    Jessica Riad is a visual artist and fashion designer who transforms emotion, memory, and
                                    culture into wearable art. Her creations merge craftsmanship with storytelling, blending
                                    recycled materials, bold textures, and heritage influences into collectible fashion
                                    pieces. Each design carries a fragment of identity — a story shaped by feeling and
                                    detail. Rooted in Cairo and inspired by global artistry, Jessica’s work redefines cool
                                    luxury as something soulful, personal, and timeless.
                                </p>
                                <p>
                                    For those who seek meaning beyond fashion, Jessica Riad invites you to carry art, not
                                    trend.
                                </p>
                                <span
                                    style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: 1.4em; font-weight: 400; color: #2c3e50; letter-spacing: 1px; display: inline-block; margin: 10px 0; position: relative; z-index: 1;"
                                    title="Hand-signed quote">art you carry – emotion you own</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Philosophy Section -->
        <section class="philosophy-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="philosophy-image">
                            <img src="{{ asset('images/philosophy.jpg') }}" alt="Craftsmanship philosophy"
                                class="img-fluid">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="philosophy-content">
                            <h2 class="section-title">My Philosophy</h2>
                            <div class="philosophy-points">
                                <div class="philosophy-point">
                                    <div class="point-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="point-content">
                                        <h4>Passion-Driven Creation</h4>
                                        <p>Every piece is crafted with genuine love and dedication, ensuring that the
                                            passion
                                            behind the creation translates into the final product.</p>
                                    </div>
                                </div>
                                <div class="philosophy-point">
                                    <div class="point-icon">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <div class="point-content">
                                        <h4>Sustainable Practices</h4>
                                        <p>I believe in responsible creation, using sustainable materials and methods that
                                            honor both the craft and our environment.</p>
                                    </div>
                                </div>
                                <div class="philosophy-point">
                                    <div class="point-icon">
                                        <i class="fas fa-gem"></i>
                                    </div>
                                    <div class="point-content">
                                        <h4>Timeless Quality</h4>
                                        <p>Each creation is built to last, combining traditional craftsmanship with modern
                                            durability to create heirloom-quality pieces.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Process Section -->
        <section class="process-section">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-5">
                        <h2 class="section-title">The Creative Process</h2>
                        <p class="section-subtitle">From concept to completion, every step is carefully considered</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="step-number">01</div>
                            <h4 class="step-title">Inspiration</h4>
                            <p class="step-description">
                                Drawing inspiration from nature, architecture, and everyday moments to conceptualize unique
                                designs.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="step-number">02</div>
                            <h4 class="step-title">Design</h4>
                            <p class="step-description">
                                Sketching and refining ideas, considering both aesthetic appeal and functional requirements.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="step-number">03</div>
                            <h4 class="step-title">Creation</h4>
                            <p class="step-description">
                                Hand-crafting each piece with meticulous attention to detail and traditional techniques.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="step-number">04</div>
                            <h4 class="step-title">Perfection</h4>
                            <p class="step-description">
                                Final refinement and quality assurance to ensure each piece meets the highest standards.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 col-xl-8 mx-auto">
                        <div class="values-content">
                            <h2 class="values-title">WHAT DRIVES ME</h2>

                            <div class="value-block">
                                <h3 class="value-heading">Authenticity</h3>
                                <p class="value-description">Creating genuine, one-of-a-kind pieces that reflect true
                                    artisanal craftsmanship</p>
                            </div>

                            <div class="value-block">
                                <h3 class="value-heading">Innovation</h3>
                                <p class="value-description">Constantly exploring new techniques while respecting
                                    traditional methods</p>
                            </div>

                            <div class="value-block">
                                <h3 class="value-heading">Connection</h3>
                                <p class="value-description">Building meaningful relationships with clients through shared
                                    appreciation for handmade beauty</p>
                            </div>

                            <div class="value-block">
                                <h3 class="value-heading">Excellence</h3>
                                <p class="value-description">Maintaining the highest standards in every aspect of creation
                                    and service</p>
                            </div>

                            <div class="cta-block">
                                <h3 class="cta-heading">Let's Create Something Beautiful Together</h3>
                                <p class="cta-text">Whether you're looking for a unique piece from my existing collection or
                                    interested in commissioning a custom creation, I'd love to hear from you.</p>
                                <div class="cta-buttons-inline">
                                    <a href="{{ route('collections.index') }}" class="btn-collections">VIEW COLLECTIONS</a>
                                    <a href="{{ route('contact') }}" class="btn-contact">Get In Touch</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="final-cta-section">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="final-note">Ready to start your journey with handcrafted excellence?</p>
                    </div>
                </div>
            </div>
        </section>
        </a>
        </div>
        </div>
        </div>
        </div>
        </section>
    </main>

    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
        <style>
            .about-page {
                font-family: 'futura-pt', sans-serif;
            }

            /* Hero Section */
            .hero-section {
                padding: 4rem 0;
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            }

            .hero-title {
                font-weight: 200;
                font-size: 3.5rem;
                color: var(--primary-color);
                margin-bottom: 1rem;
                letter-spacing: 0.02em;
            }

            .hero-subtitle {
                font-weight: 200;
                font-size: 1.25rem;
                color: var(--text-muted);
                line-height: 1.6;
                margin-bottom: 0;
            }

            .hero-image {
                position: relative;
            }

            .hero-image img {
                border-radius: 8px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            }

            /* Story Section */
            .story-section {
                padding: 5rem 0;
                background: white;
            }

            .section-title {
                font-weight: 300;
                font-size: 2.5rem;
                color: var(--primary-color);
                margin-bottom: 2rem;
                text-align: center;
                letter-spacing: 0.02em;
            }

            .story-text p {
                font-weight: 200;
                font-size: 1.125rem;
                line-height: 1.8;
                color: var(--text-dark);
                margin-bottom: 1.5rem;
            }

            /* Philosophy Section */
            .philosophy-section {
                padding: 5rem 0;
                background: #f8f9fa;
            }

            .philosophy-image img {
                border-radius: 8px;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            }

            .philosophy-point {
                display: flex;
                align-items: flex-start;
                margin-bottom: 2rem;
            }

            .point-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-color), #333);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1.5rem;
                flex-shrink: 0;
            }

            .point-icon i {
                font-size: 1.5rem;
                color: white;
            }

            .point-content h4 {
                font-weight: 300;
                font-size: 1.25rem;
                color: var(--primary-color);
                margin-bottom: 0.5rem;
            }

            .point-content p {
                font-weight: 200;
                color: var(--text-muted);
                margin-bottom: 0;
                line-height: 1.6;
            }

            /* Process Section */
            .process-section {
                padding: 5rem 0;
                background: white;
            }

            .section-subtitle {
                font-weight: 200;
                font-size: 1.125rem;
                color: var(--text-muted);
                margin-bottom: 0;
            }

            .process-step {
                text-align: center;
                padding: 2rem 1rem;
            }

            .step-number {
                font-weight: 200;
                font-size: 3rem;
                color: var(--primary-color);
                margin-bottom: 1rem;
                opacity: 0.7;
            }

            .step-title {
                font-weight: 300;
                font-size: 1.25rem;
                color: var(--primary-color);
                margin-bottom: 1rem;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .step-description {
                font-weight: 200;
                color: var(--text-muted);
                line-height: 1.6;
                margin-bottom: 0;
            }

            /* Values Section */
            .values-section {
                padding: 6rem 0 4rem;
                background: white;
            }

            .values-content {
                text-align: center;
                max-width: 800px;
                margin: 0 auto;
            }

            .values-title {
                font-family: 'futura-pt', sans-serif;
                font-weight: 500;
                font-size: 2rem;
                color: #000;
                margin-bottom: 4rem;
                letter-spacing: 0.15em;
                text-transform: uppercase;
            }

            .value-block {
                margin-bottom: 3rem;
                padding: 0 2rem;
            }

            .value-heading {
                font-family: 'futura-pt', sans-serif;
                font-weight: 500;
                font-size: 1.25rem;
                color: #000;
                margin-bottom: 1rem;
                letter-spacing: 0.05em;
            }

            .value-description {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
                font-size: 1rem;
                line-height: 1.7;
                color: #333;
                margin-bottom: 0;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
            }

            .cta-block {
                margin-top: 4rem;
                padding: 3rem 2rem 2rem;
                border-top: 1px solid #eee;
            }

            .cta-heading {
                font-family: 'futura-pt', sans-serif;
                font-weight: 500;
                font-size: 1.5rem;
                color: #000;
                margin-bottom: 1.5rem;
                letter-spacing: 0.02em;
            }

            .cta-text {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
                font-size: 1rem;
                line-height: 1.6;
                color: #333;
                margin-bottom: 2rem;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }

            .cta-buttons-inline {
                display: flex;
                gap: 2rem;
                justify-content: center;
                align-items: center;
                flex-wrap: wrap;
            }

            .btn-collections {
                font-family: 'futura-pt', sans-serif;
                font-weight: 500;
                font-size: 0.9rem;
                color: white;
                background: #000;
                padding: 0.75rem 2rem;
                text-decoration: none;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                transition: all 0.3s ease;
                border: 2px solid #000;
            }

            .btn-collections:hover {
                background: white;
                color: #000;
                text-decoration: none;
            }

            .btn-contact {
                font-family: 'futura-pt', sans-serif;
                font-weight: 400;
                font-size: 0.9rem;
                color: #007bff;
                background: transparent;
                padding: 0.75rem 1.5rem;
                text-decoration: none;
                border: 2px solid #007bff;
                letter-spacing: 0.05em;
                transition: all 0.3s ease;
            }

            .btn-contact:hover {
                background: #007bff;
                color: white;
                text-decoration: none;
            }

            /* Final CTA Section */
            .final-cta-section {
                padding: 2rem 0;
                background: #f8f9fa;
            }

            .final-note {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 0.9rem;
                color: #666;
                margin-bottom: 0;
                font-style: italic;
            }

            /* Final CTA Section */
            .final-cta-section {
                padding: 2rem 0;
                background: #f8f9fa;
            }

            .final-note {
                font-family: 'futura-pt', sans-serif;
                font-weight: 300;
                font-size: 0.9rem;
                color: #666;
                margin-bottom: 0;
                font-style: italic;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .hero-title {
                    font-size: 2.5rem;
                }

                .section-title {
                    font-size: 2rem;
                }

                .philosophy-point {
                    flex-direction: column;
                    text-align: center;
                }

                .point-icon {
                    margin-right: 0;
                    margin-bottom: 1rem;
                }

                /* Values section mobile styles */
                .values-title {
                    font-size: 1.5rem;
                    margin-bottom: 3rem;
                }

                .value-block {
                    padding: 0 1rem;
                    margin-bottom: 2.5rem;
                }

                .value-heading {
                    font-size: 1.1rem;
                }

                .value-description {
                    font-size: 0.95rem;
                }

                .cta-block {
                    padding: 2rem 1rem 1rem;
                    margin-top: 3rem;
                }

                .cta-heading {
                    font-size: 1.25rem;
                }

                .cta-text {
                    font-size: 0.95rem;
                }

                .cta-buttons-inline {
                    flex-direction: column;
                    gap: 1rem;
                }

                .btn-collections,
                .btn-contact {
                    width: 100%;
                    max-width: 250px;
                    text-align: center;
                }
            }
        </style>
    @endpush
@endsection
