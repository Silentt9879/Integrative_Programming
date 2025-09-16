@extends('app')

@section('title', 'Contact Us - RentWheels')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ffffff;
        margin: 0;
        line-height: 1.6;
    }

    .contact-hero {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 4rem 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
    }

    .contact-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .contact-hero p {
        font-size: 1.2rem;
        margin-bottom: 0;
        position: relative;
        z-index: 2;
        opacity: 0.9;
    }

    .contact-content {
        padding: 4rem 0;
    }

    .contact-methods {
        background: #f8f9fa;
        padding: 4rem 0;
        margin: 3rem 0;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .contact-card {
        background: white;
        padding: 2.5rem 2rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 123, 255, 0.2);
    }

    .contact-icon {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.5rem;
    }

    .contact-card h4 {
        color: #333;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .contact-card p {
        color: #666;
        margin: 0.5rem 0;
        font-size: 0.95rem;
    }

    .contact-card a {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }

    .contact-card a:hover {
        color: #0056b3;
    }

    .section-title {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }

    .section-title p {
        font-size: 1.1rem;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    .faq-section {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .faq-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .faq-card h4 {
        color: #333;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .faq-card p {
        color: #666;
        margin: 0;
        font-size: 0.95rem;
    }

    .emergency-banner {
        background: #dc3545;
        color: white;
        padding: 1.5rem 0;
        text-align: center;
        margin-bottom: 2rem;
    }

    .emergency-banner h5 {
        margin: 0;
        font-weight: 700;
    }

    .emergency-banner p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .contact-hero h1 {
            font-size: 2.2rem;
        }
        
        .section-title h2 {
            font-size: 2rem;
        }
        
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<div class="contact-hero">
    <div class="container">
        <h1>Get in Touch</h1>
        <p>We're here to help with all your vehicle rental needs</p>
    </div>
</div>

<!-- Emergency Banner -->
<div class="emergency-banner">
    <div class="container">
        <h5>24/7 Emergency Roadside Assistance</h5>
        <p>Need immediate help? Call our emergency hotline: <strong>+60 11-1234 5678</strong></p>
    </div>
</div>

<!-- Contact Methods -->
<div class="contact-methods">
    <div class="container">
        <div class="section-title">
            <h2>How Can We Help You?</h2>
            <p>Choose the best way to reach us. We're committed to providing quick and helpful responses to all inquiries.</p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h4>Call Us</h4>
                <p><strong>General Inquiries</strong></p>
                <p><a href="tel:+60312345678">+60 3-1234 5678</a></p>
                <p><strong>Reservations</strong></p>
                <p><a href="tel:+60312345679">+60 3-1234 5679</a></p>
                <p>Mon-Sun: 8:00 AM - 10:00 PM</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h4>Email Us</h4>
                <p><strong>Customer Support</strong></p>
                <p><a href="mailto:support@rentwheels.com">support@rentwheels.com</a></p>
                <p><strong>Business Inquiries</strong></p>
                <p><a href="mailto:business@rentwheels.com">business@rentwheels.com</a></p>
                <p>We respond within 2 hours</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4>Visit Our Office</h4>
                <p><strong>Head Office</strong></p>
                <p>123 Jalan Bukit Bintang<br>
                50200 Kuala Lumpur<br>
                Malaysia</p>
                <p>Mon-Fri: 9:00 AM - 6:00 PM</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h4>Live Chat</h4>
                <p><strong>Instant Support</strong></p>
                <p>Chat with our customer service team in real-time</p>
                <p><strong>Available 24/7</strong></p>
                <p><a href="#" onclick="alert('Live chat feature coming soon!')">Start Chat</a></p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="faq-section">
    <div class="container">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p>Quick answers to common questions about RentWheels</p>
        </div>
        
        <div class="faq-grid">
            <div class="faq-card">
                <h4>How do I make a reservation?</h4>
                <p>You can book online through our website, call our reservation hotline, or visit any of our locations. Online booking is available 24/7.</p>
            </div>
            <div class="faq-card">
                <h4>What documents do I need to rent?</h4>
                <p>You'll need a valid driver's license, identification card, and a credit card for security deposit. International visitors need an International Driving Permit.</p>
            </div>
            <div class="faq-card">
                <h4>Can I modify or cancel my booking?</h4>
                <p>Yes, you can modify or cancel your booking up to 24 hours before pickup without charges. Contact our customer service for assistance.</p>
            </div>
            <div class="faq-card">
                <h4>Do you offer delivery service?</h4>
                <p>We provide vehicle delivery and pickup services within Klang Valley for an additional fee. Contact us to arrange delivery to your location.</p>
            </div>
        </div>
    </div>
</div>
@endsection