<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Modern Banking') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #4f46e5;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-700: #334155;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Header */
        header {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 50;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        
        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--gray-300);
        }
        
        .btn-outline:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-300);
        }
        
        /* Hero Section */
        .hero {
            padding: 8rem 0 6rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            margin-top: 4rem;
        }
        
        .hero-content {
            max-width: 48rem;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .hero p {
            font-size: 1.25rem;
            color: var(--gray-700);
            margin-bottom: 2.5rem;
            max-width: 36rem;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
        }
        
        .section-header {
            text-align: center;
            max-width: 48rem;
            margin: 0 auto 4rem;
        }
        
        .section-header h2 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .section-header p {
            font-size: 1.125rem;
            color: var(--gray-700);
            line-height: 1.6;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .feature-icon {
            width: 3rem;
            height: 3rem;
            background-color: #dbeafe;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .feature-card p {
            color: var(--gray-700);
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta {
            background-color: var(--primary);
            color: white;
            padding: 6rem 0;
            text-align: center;
            margin-bottom: 0;
        }
        
        .cta h2 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        
        .cta p {
            font-size: 1.25rem;
            max-width: 42rem;
            margin: 0 auto 2.5rem;
            opacity: 0.9;
        }
        
        .cta .btn {
            background-color: white;
            color: var(--primary);
            font-weight: 600;
            padding: 0.75rem 2rem;
            font-size: 1.125rem;
        }
        
        .cta .btn:hover {
            background-color: var(--gray-100);
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .footer-about p {
            color: var(--gray-300);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            color: white;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        
        .social-links a:hover {
            background-color: var(--primary);
        }
        
        .footer-links h3 {
            color: white;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: var(--gray-300);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: var(--gray-400);
            font-size: 0.875rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p, .cta p {
                font-size: 1.125rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-container">
                <a href="{{ url('/') }}" class="logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <path d="M12 12h.01"></path>
                        <path d="M17 12h.01"></path>
                        <path d="M7 12h.01"></path>
                    </svg>
                    ModernBank
                </a>
                
                <nav class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#contact">Contact</a>
                </nav>
                
                <div class="auth-buttons">
                    <a href="{{ route('login') }}" class="btn btn-outline">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Banking Made Simple, Fast, and Secure</h1>
                <p>Experience the future of banking with our intuitive platform. Manage your finances, make payments, and grow your wealth—all in one place.</p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started for Free</a>
                    <a href="#features" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything You Need to Manage Your Money</h2>
                <p>Our platform provides all the tools you need to take control of your financial life.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                            <path d="M18 12a2 2 0 0 0 0 4h4v-4z"></path>
                        </svg>
                    </div>
                    <h3>Easy Transfers</h3>
                    <p>Send and receive money instantly between accounts or to other users with just a few clicks.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our customer support team is available around the clock to assist you with any questions or issues.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4"></path>
                            <path d="m16.24 7.76 2.83-2.83"></path>
                            <path d="M17.66 15c.23.27.38.59.44.94.1.7-.09 1.4-.52 1.94-.43.54-1.04.86-1.72.86-.47 0-.93-.14-1.33-.4"></path>
                            <path d="M12 22c-2.76 0-5-2.24-5-5 0-1.45.63-2.76 1.62-3.66.12-.11.24-.21.36-.31"></path>
                            <path d="M12 22c2.76 0 5-2.24 5-5 0-1.45-.63-2.76-1.62-3.66-.12-.11-.24-.21-.36-.31"></path>
                            <path d="M6.34 15c-.23.27-.38.59-.44.94-.1.7.09 1.4.52 1.94.43.54 1.04.86 1.72.86.47 0 .93-.14 1.33-.4"></path>
                            <path d="M2 12h4"></path>
                            <path d="M4.93 19.07 7.76 16.24"></path>
                            <path d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"></path>
                        </svg>
                    </div>
                    <h3>Smart Budgeting</h3>
                    <p>Track your spending, set budgets, and get personalized insights to help you save more.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Bank-Level Security</h3>
                    <p>Your data is protected with enterprise-grade security and 256-bit encryption.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                    </div>
                    <h3>Instant Notifications</h3>
                    <p>Get real-time alerts for all account activity, so you're always in the know.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4"></path>
                            <path d="m16.24 7.76 2.83-2.83"></path>
                            <path d="M17.66 15c.23.27.38.59.44.94.1.7-.09 1.4-.52 1.94-.43.54-1.04.86-1.72.86-.47 0-.93-.14-1.33-.4"></path>
                            <path d="M12 22c-2.76 0-5-2.24-5-5 0-1.45.63-2.76 1.62-3.66.12-.11.24-.21.36-.31"></path>
                            <path d="M12 22c2.76 0 5-2.24 5-5 0-1.45-.63-2.76-1.62-3.66-.12-.11-.24-.21-.36-.31"></path>
                            <path d="M6.34 15c-.23.27-.38.59-.44.94-.1.7.09 1.4.52 1.94.43.54 1.04.86 1.72.86.47 0 .93-.14 1.33-.4"></path>
                            <path d="M2 12h4"></path>
                            <path d="M4.93 19.07 7.76 16.24"></path>
                            <path d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"></path>
                        </svg>
                    </div>
                    <h3>Mobile Banking</h3>
                    <p>Bank on the go with our mobile app, available for both iOS and Android devices.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Simplify Your Banking?</h2>
            <p>Join thousands of customers who trust us with their financial needs. Sign up today and experience the difference.</p>
            <a href="{{ route('register') }}" class="btn">Get Started Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="{{ url('/') }}" class="footer-logo">ModernBank</a>
                    <p>Making banking simple, fast, and secure for everyone. Join us on our mission to revolutionize personal finance.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Personal Banking</a></li>
                        <li><a href="#">Business Banking</a></li>
                        <li><a href="#">Investments</a></li>
                        <li><a href="#">Loans</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Security</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} ModernBank. All rights reserved. | <a href="#" style="color: var(--gray-300);">Privacy Policy</a> | <a href="#" style="color: var(--gray-300);">Terms of Service</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
