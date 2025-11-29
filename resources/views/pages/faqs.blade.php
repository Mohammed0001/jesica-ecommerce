@extends('layouts.app')

@section('title', 'FAQs')

@section('content')
<main class="page-faqs">
    <section class="container py-5">
        <h1 class="mb-4">Frequently Asked Questions</h1>
        <p>Find answers to common questions about ordering, shipping, returns, and more.</p>
        <div class="accordion" id="faqsAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        How do I place an order?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">Browse products, choose size/quantity, add items to your cart and proceed to checkout. You can review your order and apply any promo codes before finalizing payment.</div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        What payment methods do you accept?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">We accept the payment methods shown at checkout. For online card payments we use secure gateways. If you have a problem with payment, please contact support.</div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                        How long does shipping take?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">Shipping times depend on your location and selected shipping method. Most orders are processed within 1-3 business days; standard shipping typically arrives within 3-10 business days.</div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                        What is your returns policy?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">We accept returns within 14 days of delivery for eligible items in unused condition. To start a return, visit the 'Request Return' page or contact support with your order number.</div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                        How can I track my order?
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">After your order ships you'll receive a tracking number by email. You can also use the 'Track My Order' page to enter your order number and check status.</div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
