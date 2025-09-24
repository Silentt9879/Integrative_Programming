@extends('app')

@section('title', 'Billing & Payments - RentWheels')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ffffff;
        margin: 0;
        line-height: 1.6;
    }

    .billing-hero {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 4rem 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .billing-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
    }

    .billing-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .billing-hero p {
        font-size: 1.2rem;
        margin-bottom: 0;
        position: relative;
        z-index: 2;
        opacity: 0.9;
    }

    .billing-content {
        padding: 4rem 0;
    }

    .billing-methods {
        background: #f8f9fa;
        padding: 4rem 0;
        margin: 3rem 0;
    }

    .billing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .billing-card {
        background: white;
        padding: 2.5rem 2rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .billing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 123, 255, 0.2);
    }

    .billing-icon {
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

    .billing-card h4 {
        color: #333;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .billing-card p {
        color: #666;
        margin: 0.5rem 0;
        font-size: 0.95rem;
    }

    .billing-card a {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }

    .billing-card a:hover {
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

    .payment-info-section {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .info-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .info-card h4 {
        color: #333;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .info-card h4 i {
        margin-right: 10px;
        color: #007bff;
    }

    .info-card p {
        color: #666;
        margin: 0;
        font-size: 0.95rem;
    }

    .info-card ul {
        color: #666;
        font-size: 0.95rem;
        padding-left: 1.2rem;
    }

    .secure-banner {
        background: #28a745;
        color: white;
        padding: 1.5rem 0;
        text-align: center;
        margin-bottom: 2rem;
    }

    .secure-banner h5 {
        margin: 0;
        font-weight: 700;
    }

    .secure-banner p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    .payment-methods {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .payment-logos {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .payment-logo {
        background: #f8f9fa;
        padding: 0.8rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        color: #333;
        border: 1px solid #e9ecef;
    }

    @media (max-width: 768px) {
        .billing-hero h1 {
            font-size: 2.2rem;
        }
        
        .section-title h2 {
            font-size: 2rem;
        }
        
        .billing-grid {
            grid-template-columns: 1fr;
        }

        .payment-logos {
            gap: 1rem;
        }

        .payment-logo {
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
        }
    }
</style>

<!-- Hero Section -->
<div class="billing-hero">
    <div class="container">
        <h1>Billing & Payments</h1>
        <p>Transparent pricing and secure payment options for all your rental needs</p>
    </div>
</div>

<!-- Secure Payment Banner -->
<div class="secure-banner">
    <div class="container">
        <h5><i class="fas fa-shield-alt me-2"></i>Secure Payment Processing</h5>
        <p>All transactions are protected with 256-bit SSL encryption and PCI DSS compliance</p>
    </div>
</div>

<!-- Billing Services -->
<div class="billing-methods">
    <div class="container">
        <div class="section-title">
            <h2>Billing Services</h2>
            <p>Manage your rental payments, view billing history, and understand our transparent pricing structure.</p>
        </div>
        
        <div class="billing-grid">
            @auth
            <div class="billing-card">
                <div class="billing-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h4>My Bills</h4>
                <p>View outstanding bills, payment history, and additional charges from your rental bookings.</p>
                <p><a href="{{ route('billing.index') }}">View My Bills</a></p>
                <small class="text-muted">Access your personal billing dashboard</small>
            </div>
            @else
            <div class="billing-card">
                <div class="billing-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h4>Customer Login</h4>
                <p>Login to access your billing dashboard, view outstanding bills, and manage payments.</p>
                <p><a href="{{ route('login') }}">Login to Account</a></p>
                <small class="text-muted">Secure access to your billing information</small>
            </div>
            @endauth
            
            <div class="billing-card">
                <div class="billing-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h4>Payment Methods</h4>
                <p>We accept major credit cards, debit cards, and bank transfers for your convenience.</p>
                <p><strong>Accepted Cards:</strong><br>Visa, MasterCard, American Express</p>
                <small class="text-muted">Secure and instant payment processing</small>
            </div>
            
            <div class="billing-card">
                <div class="billing-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <h4>Pricing Calculator</h4>
                <p>Get instant quotes and understand our transparent pricing before you book.</p>
                <p><a href="{{ route('booking.search-form') }}">Calculate Rental Cost</a></p>
                <small class="text-muted">No hidden fees, clear pricing</small>
            </div>
            
            <div class="billing-card">
                <div class="billing-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h4>Invoice & Receipts</h4>
                <p>Download official invoices and receipts for your business or personal records.</p>
                @auth
                <p><a href="{{ route('billing.export') }}">Download History</a></p>
                @else
                <p><a href="{{ route('login') }}">Login to Access</a></p>
                @endauth
                <small class="text-muted">PDF format available</small>
            </div>
        </div>

        <!-- Payment Methods Display -->
        <div class="payment-methods">
            <div class="text-center">
                <h4><i class="fas fa-lock me-2"></i>Accepted Payment Methods</h4>
                <p class="text-muted">All major payment methods accepted with secure processing</p>
            </div>
            <div class="payment-logos">
                <div class="payment-logo">
                    <i class="fab fa-cc-visa"></i> Visa
                </div>
                <div class="payment-logo">
                    <i class="fab fa-cc-mastercard"></i> MasterCard
                </div>
                <div class="payment-logo">
                    <i class="fab fa-cc-amex"></i> Amex
                </div>
                <div class="payment-logo">
                    <i class="fas fa-university"></i> Bank Transfer
                </div>
                <div class="payment-logo">
                    <i class="fas fa-mobile-alt"></i> Online Banking
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Information -->
<div class="payment-info-section">
    <div class="container">
        <div class="section-title">
            <h2>Payment Information</h2>
            <p>Everything you need to know about our billing and payment processes</p>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h4><i class="fas fa-clock"></i>Payment Terms</h4>
                <ul>
                    <li>Deposit required at booking confirmation</li>
                    <li>Full payment due at vehicle pickup</li>
                    <li>Additional charges billed after return</li>
                    <li>Late payment fees apply after 7 days</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h4><i class="fas fa-shield-alt"></i>Security & Privacy</h4>
                <p>Your payment information is protected with industry-standard security measures:</p>
                <ul>
                    <li>256-bit SSL encryption</li>
                    <li>PCI DSS compliance</li>
                    <li>Fraud detection systems</li>
                    <li>No storage of full card details</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h4><i class="fas fa-plus-circle"></i>Additional Charges</h4>
                <p>Transparent additional fees that may apply:</p>
                <ul>
                    <li>Damage assessment fees</li>
                    <li>Late return penalties</li>
                    <li>Cleaning charges (if required)</li>
                    <li>Fuel charges (if not refueled)</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h4><i class="fas fa-undo"></i>Refund Policy</h4>
                <p>Our fair and transparent refund policy:</p>
                <ul>
                    <li>Free cancellation 24+ hours before pickup</li>
                    <li>Partial refunds for early cancellation</li>
                    <li>Deposit refunds within 5-7 business days</li>
                    <li>Refunds processed to original payment method</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h4><i class="fas fa-headset"></i>Billing Support</h4>
                <p>Need help with billing or payments?</p>
                <ul>
                    <li><strong>Email:</strong> billing@rentwheels.com</li>
                    <li><strong>Phone:</strong> +60 3-1234 5680</li>
                    <li><strong>Hours:</strong> Mon-Sun 8AM-10PM</li>
                    <li><strong>Response:</strong> Within 2 hours</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h4><i class="fas fa-file-alt"></i>Billing Disputes</h4>
                <p>If you have concerns about charges:</p>
                <ul>
                    <li>Contact our billing team immediately</li>
                    <li>Provide booking reference number</li>
                    <li>Submit supporting documentation</li>
                    <li>Resolution within 3-5 business days</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection