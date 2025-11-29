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
                        How long does shipping take?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqsAccordion">
                    <div class="accordion-body">Shipping times depend on destination; typical processing is 2-5 business days.</div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
