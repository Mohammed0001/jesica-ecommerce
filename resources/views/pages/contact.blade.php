@extends('layouts.app')

@section('title', 'Contact Jesica Riad')

@section('content')
<main class="contact-page">
    <!-- Header Section -->
    <section class="contact-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="page-title">Get In Touch</h1>
                    <p class="page-subtitle">
                        I'd love to hear from you. Whether you have questions about existing pieces,
                        want to discuss a custom commission, or simply want to connect, don't hesitate to reach out.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="contact-content">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-form-wrapper">
                        <h2 class="form-title">Send a Message</h2>

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="subject" class="form-label">Subject</label>
                                <select class="form-select @error('subject') is-invalid @enderror"
                                        id="subject" name="subject" required>
                                    <option value="">Please select a subject</option>
                                    <option value="General Inquiry" {{ old('subject') === 'General Inquiry' ? 'selected' : '' }}>
                                        General Inquiry
                                    </option>
                                    <option value="Custom Commission" {{ old('subject') === 'Custom Commission' ? 'selected' : '' }}>
                                        Custom Commission
                                    </option>
                                    <option value="Existing Order" {{ old('subject') === 'Existing Order' ? 'selected' : '' }}>
                                        Existing Order
                                    </option>
                                    <option value="Collaboration" {{ old('subject') === 'Collaboration' ? 'selected' : '' }}>
                                        Collaboration Opportunity
                                    </option>
                                    <option value="Press Inquiry" {{ old('subject') === 'Press Inquiry' ? 'selected' : '' }}>
                                        Press Inquiry
                                    </option>
                                    <option value="Other" {{ old('subject') === 'Other' ? 'selected' : '' }}>
                                        Other
                                    </option>
                                </select>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                          id="message" name="message" rows="6" required
                                          placeholder="Please tell me about your project, questions, or how I can help you...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="contact-info">
                        <h3 class="info-title">Contact Information</h3>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email</h4>
                                <p><a href="mailto:hello@jesicariad.com">hello@jesicariad.com</a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Phone</h4>
                                <p><a href="tel:+1234567890">+1 (234) 567-890</a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Studio Location</h4>
                                <p>Available by appointment<br>
                                Please contact to arrange a visit</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Response Time</h4>
                                <p>I typically respond within<br>
                                24-48 hours</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="social-media">
                            <h4>Follow My Work</h4>
                            <div class="social-links">
                                <a href="#" target="_blank" rel="noopener" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" target="_blank" rel="noopener" aria-label="Pinterest">
                                    <i class="fab fa-pinterest"></i>
                                </a>
                                <a href="#" target="_blank" rel="noopener" aria-label="Facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="#" target="_blank" rel="noopener" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title">Frequently Asked Questions</h2>

                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    How do I commission a custom piece?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show"
                                 aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To commission a custom piece, please use the contact form above with the subject
                                    "Custom Commission" and describe your vision, preferred materials, size requirements,
                                    and timeline. I'll respond with questions about your project and provide a detailed
                                    proposal including pricing and timeline.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    What is the typical timeline for custom work?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse"
                                 aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Custom pieces typically take 4-8 weeks from design approval to completion, depending
                                    on the complexity and current project queue. I'll provide a specific timeline with
                                    your project proposal. Rush orders may be possible for an additional fee.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    Do you ship internationally?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse"
                                 aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Yes, I ship worldwide. Shipping costs vary by destination and piece size. All pieces
                                    are carefully packaged to ensure safe arrival. International orders may be subject
                                    to customs duties and taxes, which are the responsibility of the buyer.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    What materials do you work with?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse"
                                 aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>I work with a variety of materials including ceramics, wood, metal, and textiles.
                                    I'm always exploring new materials and techniques. If you have a specific material
                                    in mind for your project, please mention it in your inquiry and I'll let you know
                                    if it's something I can work with.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    Can I visit your studio?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse"
                                 aria-labelledby="faq5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Studio visits are available by appointment only. I love sharing my creative process
                                    with interested clients and collectors. Please contact me to schedule a visit.
                                    Studio tours are particularly recommended for custom commission discussions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@push('styles')
<style>
.contact-page {
    font-family: 'futura-pt', sans-serif;
}

/* Header Section */
.contact-header {
    padding: 4rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.page-title {
    font-weight: 200;
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    letter-spacing: 0.02em;
}

.page-subtitle {
    font-weight: 200;
    font-size: 1.125rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 0;
}

/* Contact Content */
.contact-content {
    padding: 4rem 0;
}

.contact-form-wrapper {
    background: white;
    padding: 3rem;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.form-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    letter-spacing: 0.02em;
}

.form-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

.btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 2rem;
    border-radius: 0;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary-color);
    border: 2px solid var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: transparent;
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.alert {
    border-radius: 4px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    margin-bottom: 2rem;
}

.invalid-feedback {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
}

/* Contact Info */
.contact-info {
    padding: 2rem;
}

.info-title {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    letter-spacing: 0.02em;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.contact-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.contact-icon i {
    font-size: 1.25rem;
    color: white;
}

.contact-details h4 {
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.contact-details p {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 0;
    line-height: 1.5;
}

.contact-details a {
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-details a:hover {
    color: var(--primary-color);
}

/* Social Media */
.social-media {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-light);
}

.social-media h4 {
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-links a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--border-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

/* FAQ Section */
.faq-section {
    padding: 6rem 0;
    background: #ffffff;
}

.section-title {
    font-family: 'Futura PT', Arial, sans-serif;
    font-weight: 300;
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 3rem;
    text-align: center;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

.accordion {
    --bs-accordion-border-color: none;
    --bs-accordion-border-radius: 0;
}

.accordion-item {
    border: none;
    margin-bottom: 0;
    border-radius: 0;
    overflow: hidden;
    background: transparent;
    border-bottom: 1px solid #f0f0f0;
}

.accordion-item:last-child {
    border-bottom: none;
}

.accordion-button {
    font-family: 'Futura PT', Arial, sans-serif;
    font-weight: 500;
    font-size: 1.1rem;
    color: #333;
    background: transparent;
    border: none;
    padding: 2rem 0;
    border-radius: 0;
    box-shadow: none;
    transition: all 0.3s ease;
    letter-spacing: 0.02em;
    text-align: left;
    position: relative;
}

.accordion-button:not(.collapsed) {
    background: transparent;
    color: #333;
    box-shadow: none;
    border-bottom: none;
}

.accordion-button:focus {
    border-color: transparent;
    box-shadow: none;
}

.accordion-button:hover {
    color: #666;
}

.accordion-button::after {
    width: 1.2rem;
    height: 1.2rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23333333'%3e%3cpath fill-rule='evenodd' d='M8 1a.5.5 0 0 1 .5.5v6h6a.5.5 0 0 1 0 1h-6v6a.5.5 0 0 1-1 0v-6h-6a.5.5 0 0 1 0-1h6v-6A.5.5 0 0 1 8 1z'/%3e%3c/svg%3e");
    transition: transform 0.3s ease;
}

.accordion-button:not(.collapsed)::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23333333'%3e%3cpath fill-rule='evenodd' d='M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8z'/%3e%3c/svg%3e");
    transform: rotate(0deg);
}

.accordion-collapse {
    border: none;
}

.accordion-body {
    font-family: 'Futura PT', Arial, sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: #666;
    line-height: 1.7;
    padding: 0 0 2rem 0;
    background: transparent;
    border: none;
}

.accordion-body p {
    margin-bottom: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2.5rem;
    }

    .contact-form-wrapper {
        padding: 2rem 1.5rem;
    }

    .contact-info {
        margin-top: 3rem;
        padding: 1.5rem;
    }

    .contact-item {
        flex-direction: column;
        text-align: center;
    }

    .contact-icon {
        margin-right: 0;
        margin-bottom: 1rem;
        align-self: center;
    }

    .social-links {
        justify-content: center;
    }
}
</style>
@endpush
@endsection
