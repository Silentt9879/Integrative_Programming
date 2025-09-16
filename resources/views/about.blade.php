@extends('app')

@section('title', 'About Us - RentWheels')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ffffff;
        margin: 0;
        line-height: 1.6;
    }

    .about-hero {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 4rem 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .about-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
    }

    .about-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .about-hero p {
        font-size: 1.2rem;
        margin-bottom: 0;
        position: relative;
        z-index: 2;
        opacity: 0.9;
    }

    .about-content {
        padding: 4rem 0;
    }

    .mission-section {
        background: #f8f9fa;
        padding: 4rem 0;
        margin: 3rem 0;
    }

    .mission-card {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin-bottom: 2rem;
    }

    .mission-icon {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 2rem;
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

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .team-member {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .team-member:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 123, 255, 0.2);
    }

    .member-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #6c757d;
        border: 4px solid #f8f9fa;
        position: relative;
        overflow: hidden;
    }

    .member-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .member-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .member-role {
        font-size: 1rem;
        color: #007bff;
        font-weight: 600;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .member-description {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .stats-section {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 4rem 0;
        margin: 4rem 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-item p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .value-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #007bff;
    }

    .value-card h4 {
        color: #333;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .value-card p {
        color: #666;
        margin: 0;
    }

    @media (max-width: 768px) {
        .about-hero h1 {
            font-size: 2rem;
        }
        
        .section-title h2 {
            font-size: 2rem;
        }
        
        .team-grid {
            grid-template-columns: 1fr;
        }
        
        .mission-card {
            padding: 2rem;
        }
    }
</style>

<!-- Hero Section -->
<div class="about-hero">
    <div class="container">
        <h1>About RentWheels</h1>
        <p>Your trusted partner in premium vehicle rental services</p>
    </div>
</div>

<!-- Mission Section -->
<div class="mission-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-road"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>At RentWheels, we're dedicated to providing exceptional vehicle rental experiences that connect people with reliable, high-quality transportation solutions. We believe that mobility should be accessible, convenient, and worry-free for everyone.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- About Content -->
<div class="about-content">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="section-title">
                    <h2>Who We Are</h2>
                    <p>RentWheels is a modern vehicle rental platform built by a passionate team of developers who understand the importance of reliable transportation in today's fast-paced world.</p>
                </div>
                
                <div class="values-grid">
                    <div class="value-card">
                        <h4><i class="fas fa-shield-alt text-primary me-2"></i>Trust & Reliability</h4>
                        <p>Every vehicle in our fleet is thoroughly inspected and maintained to ensure your safety and peace of mind on every journey.</p>
                    </div>
                    <div class="value-card">
                        <h4><i class="fas fa-clock text-primary me-2"></i>24/7 Support</h4>
                        <p>Our dedicated support team is available around the clock to assist you with any questions or concerns during your rental experience.</p>
                    </div>
                    <div class="value-card">
                        <h4><i class="fas fa-dollar-sign text-primary me-2"></i>Competitive Pricing</h4>
                        <p>We offer transparent, fair pricing with no hidden fees, making quality vehicle rental accessible to everyone.</p>
                    </div>
                    <div class="value-card">
                        <h4><i class="fas fa-leaf text-primary me-2"></i>Eco-Friendly Options</h4>
                        <p>Our growing fleet includes hybrid and electric vehicles, supporting sustainable transportation choices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>500+</h3>
                <p>Vehicles Available</p>
            </div>
            <div class="stat-item">
                <h3>10,000+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-item">
                <h3>50+</h3>
                <p>Locations</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>Customer Support</p>
            </div>
        </div>
    </div>
</div>

<!-- Team Section -->
<div class="about-content">
    <div class="container">
        <div class="section-title">
            <h2>Meet Our Team</h2>
            <p>The passionate developers behind RentWheels who work tirelessly to bring you the best vehicle rental experience</p>
        </div>
        
        <div class="team-grid">
            <!-- Team Member 1 - Jayvian -->
            <div class="team-member">
                <div class="member-photo">
                    <img src="/images/team/jayvian.jpg" alt="Jayvian Lazarus">
                    
                </div>
                <h4 class="member-name">Jayvian Lazarus</h4>
                <p class="member-role">Project Lead & Full-Stack Developer</p>
                <p class="member-description">Leading the development of RentWheels with expertise in user authentication, password management, and overall system architecture. Passionate about creating seamless user experiences.</p>
            </div>
            
            <!-- Team Member 2 - Tan Xing Ye -->
            <div class="team-member">
                <div class="member-photo">
                    <img src="/images/team/xingye.jpg" alt="Tan Xing Ye">
                </div>
                <h4 class="member-name">Tan Xing Ye</h4>
                <p class="member-role">Vehicle Management Specialist</p>
                <p class="member-description">Responsible for developing the comprehensive vehicle management system, including inventory tracking, vehicle status management, and maintenance scheduling features.</p>
            </div>
            
            <!-- Team Member 3 - Chiew Chun Sheng -->
            <div class="team-member">
                <div class="member-photo">
                    <img src="/images/team/chunsheng.jpg" alt="Chiew Chun Sheng"> 
                </div>
                <h4 class="member-name">Chiew Chun Sheng</h4>
                <p class="member-role">Payment & Analytics Developer</p>
                <p class="member-description">Specializes in payment processing systems, financial reporting, and business analytics. Ensures secure transactions and provides valuable insights through comprehensive reporting tools.</p>
            </div>
            
            <!-- Team Member 4 - Chong Zheng Yao -->
            <div class="team-member">
                <div class="member-photo">
                    <img src="/images/team/zhengyao.jpg" alt="Chong Zheng Yao">
                </div>
                <h4 class="member-name">Chong Zheng Yao</h4>
                <p class="member-role">Backend Developer</p>
                <p class="member-description">Focuses on server-side development, database optimization, and API integration. Ensures the platform runs smoothly and efficiently to handle all user requests.</p>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="mission-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="mission-card">
                    <h3>Ready to Get Started?</h3>
                    <p>Join thousands of satisfied customers who trust RentWheels for their transportation needs. Book your perfect vehicle today!</p>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-car me-2"></i>Browse Vehicles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection