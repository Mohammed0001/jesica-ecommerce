<footer class="site-footer" role="contentinfo" aria-label="Site Footer">
    <div class="container">
        <div class="row g-4">
            <!-- Column 1: Customer Services -->
            <div class="col-6 col-md-3">
                <div class="footer-card">
                    <h6 class="footer-heading">CUSTOMER SERVICES</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ route('pages.faqs') }}">FAQs</a></li>
                        <li><a href="{{ route('special-orders.index') }}">SPECIAL ORDERS</a></li>
                        <li><a href="{{ route('orders.index') }}">MY ORDERS</a></li>
                        {{-- <li><a href="{{ route('pages.track-order') }}">Track my order</a></li> --}}
                        {{-- <li><a href="{{ route('pages.request-return') }}">Request Return</a></li> --}}
                        {{-- <li><a href="#">Store Locator</a></li> --}}
                        {{-- <li><a href="#">Request an appointment</a></li> --}}
                    </ul>
                </div>
            </div>

            <!-- Column 2: The Company -->
            <div class="col-6 col-md-3">
                <div class="footer-card">
                    <h6 class="footer-heading">THE LEGACY</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ route('pages.legal') }}">Legal Area</a></li>
                        <li><a href="{{ route('pages.privacy') }}">Privacy Policy & Cookies</a></li>
                    </ul>
                </div>
            </div>

            <!-- Column 3: Follow & Social -->
            <div class="col-6 col-md-3">
                <div class="footer-card">
                    <h6 class="footer-heading">FOLLOW</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#">Instagram</a></li>
                    </ul>
                    <div class="social-icons">
                        <a href="https://www.instagram.com/jessicariad._/" target="_blank"
                            class="social-box" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Column 4: Subscribe & Shipping -->
            <div class="col-6 col-md-3">
                <div class="footer-card">
                    <h6 class="footer-heading">SUBSCRIBE TO THE NEWSLETTER</h6>
                    <form id="footerSubscribeForm" class="subscribe-form" method="post" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <input type="email" name="email" id="footerSubscribeEmail" class="form-control" placeholder="Your email"
                            required aria-label="Email" />
                        <button type="submit" class="btn btn-subscribe" id="footerSubscribeButton">Subscribe</button>
                        <div id="footerSubscribeMessage" role="status" aria-live="polite" class="mt-2 text-white" style="display:none"></div>
                    </form>

                    <script>
                        (function(){
                            const form = document.getElementById('footerSubscribeForm');
                            if(!form) return;
                            const btn = document.getElementById('footerSubscribeButton');
                            const msg = document.getElementById('footerSubscribeMessage');
                            form.addEventListener('submit', function(e){
                                e.preventDefault();
                                btn.disabled = true;
                                msg.style.display = 'none';
                                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                                fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: new URLSearchParams(new FormData(form))
                                }).then(res=>{
                                    if(res.ok) return res.json().catch(()=>({success:true}));
                                    return res.json().then(data=>Promise.reject(data));
                                }).then(data=>{
                                    msg.textContent = data.message || 'Thank you for subscribing!';
                                    msg.style.display = 'block';
                                    msg.style.color = '#fff';
                                    form.reset();
                                }).catch(err=>{
                                    const message = err?.message || (err?.errors ? Object.values(err.errors).flat().join(' ') : 'Subscription failed');
                                    msg.textContent = message;
                                    msg.style.display = 'block';
                                    msg.style.color = '#ffdddd';
                                }).finally(()=>{ btn.disabled = false; });
                            });
                        })();
                    </script>
                    <div class="shipping-info">
                        {{-- <div class="shipping-label">Shipping to:</div>
                        <div class="shipping-location">United Kingdom • EN</div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="footer-brand">
                    <img src="{{ asset('images/signature-logo.png') }}" alt="Jesica Riad" class="footer-logo" />
                    <div class="copyright">© 2026 Jesica Riad</div>
                </div>
                <div class="footer-credits">Designed & crafted with care • All rights reserved</div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Reset and base styles */
    .site-footer,
    .site-footer * {
        box-sizing: border-box;
    }

    /* Footer with black background */
    .site-footer {
        background-color: #000 !important;
        color: #fff !important;
        padding: 3rem 0 2rem;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    /* Footer columns - no background, no boxes */
    .footer-card {
        background: transparent !important;
        padding: 0;
        min-height: auto;
    }

    /* Headings */
    .footer-heading {
        color: #fff !important;
        background: transparent !important;
        font-weight: 600;
        letter-spacing: 0.06em;
        font-size: 0.75rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    /* Links list */
    .footer-links {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 0.5rem;
        color: #fff !important;
        background: transparent !important;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.8) !important;
        background: transparent !important;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s ease;
    }

    .footer-links a:hover {
        color: #fff !important;
        text-decoration: underline;
    }

    /* Social icons */
    .social-icons {
        margin-top: 1rem;
        background: transparent !important;
    }

    .social-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: transparent !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .social-box i {
        color: #fff !important;
        background: transparent !important;
        font-size: 16px;
    }

    .social-box:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Subscribe form */
    .subscribe-form {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        background: transparent !important;
    }

    .subscribe-form .form-control {
        flex: 1;
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff !important;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 4px;
    }

    .subscribe-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    .subscribe-form .form-control:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.3);
        color: #fff !important;
        outline: none;
    }

    .btn-subscribe {
        background: #fff !important;
        border: 1px solid #fff;
        color: #000 !important;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .btn-subscribe:hover {
        background: rgba(255, 255, 255, 0.9) !important;
        color: #000 !important;
    }

    /* Shipping info */
    .shipping-info {
        background: transparent !important;
    }

    .shipping-label {
        color: rgba(255, 255, 255, 0.8) !important;
        background: transparent !important;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .shipping-location {
        color: rgba(255, 255, 255, 0.6) !important;
        background: transparent !important;
        font-size: 0.875rem;
    }

    /* Footer Bottom */
    .footer-bottom {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: transparent !important;
    }

    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        background: transparent !important;
    }

    .footer-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: transparent !important;
    }

    .footer-logo {
        height: 36px;
        background: transparent !important;
    }

    .copyright {
        color: rgba(255, 255, 255, 0.7) !important;
        background: transparent !important;
        font-size: 0.875rem;
    }

    .footer-credits {
        color: rgba(255, 255, 255, 0.6) !important;
        background: transparent !important;
        font-size: 0.875rem;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .site-footer {
            padding: 2rem 0 1.5rem;
        }

        .footer-heading {
            font-size: 0.7rem;
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            font-size: 0.8125rem;
        }

        .subscribe-form {
            flex-direction: column;
        }

        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }

        .footer-brand {
            flex-direction: column;
        }
    }
</style>
