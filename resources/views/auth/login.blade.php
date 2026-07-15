<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link rel="stylesheet" href="{{ secure_asset('assets/css/login.css') }}">
</head>

<body class="auth-modern">

    <header>
        <h2 class="Flowlist">Flowlist</h2>

        <nav class="navigation">
            <a href="#" id="about-link">About</a>
            <a href="#" id="contact-link">Contact</a>
            <a href="#" id="terms-link">Tm & Cs</a>
            <button type="button" class="btnlogin-popup" id="top-register-btn">Register</button>
        </nav>
    </header>

    <div class="about-overlay" id="aboutOverlay">
        <div class="about-content">
            <span class="about-close" id="aboutClose">
                <ion-icon name="close"></ion-icon>
            </span>

            <h2>About Flowlist</h2>

            <p>
                Flowlist is a task management platform designed to help users organize responsibilities,
                manage priorities, and stay productive in a simple and structured way. It provides a clean
                digital workspace where tasks can be planned, monitored, and completed more efficiently,
                whether for academic, personal, or professional use.
            </p>

            <p>
                By combining clarity, focus, and usability, Flowlist helps reduce the stress of scattered
                schedules and unfinished plans. Users can keep track of what matters most, maintain a better
                workflow, and build consistent progress over time through a more organized daily routine.
            </p>

            <p class="about-quote">
                “With Flowlist, every task is a step closer to your goal.”
            </p>
        </div>
    </div>

    <div class="contact-overlay" id="contactOverlay">
        <div class="contact-content">
            <span class="contact-close" id="contactClose">
                <ion-icon name="close"></ion-icon>
            </span>

            <h2>Contact Our Developers</h2>
            <p class="contact-subtitle">
                For support, collaboration, or development inquiries, please reach out to our professional team below.
            </p>

            <div class="contact-list">
                <div class="contact-card">
                    <h3>MARCELL</h3>
                    <p class="role">Fullstack Developer</p>
                    <p>
                        Develops the Dashboard, Task List, and Create Task pages, focusing on
                        productivity workflows, task organization, and smooth user interaction.
                    </p>
                    <p>
                        Reach me via Instagram <a href="https://www.instagram.com/marcellwijya?igsh=aDF6Mm12cXNkNXNj" target="_blank">@marcellwijya</a>
                        for professional inquiries and collaboration.
                    </p>
                </div>

                <div class="contact-card">
                    <h3>REIFAN</h3>
                    <p class="role">Fullstack Developer</p>
                    <p>
                        Develops the Calendar, Statistics, and Profile pages, with a focus on
                        analytics, date-based task visualization, and user account experiences.
                    </p>
                    <p>
                        Reach me via Instagram <a href="https://www.instagram.com/rfnards_?igsh=MXFyM2x0aG54d3Z0Nw==" target="_blank">@rfnards_</a>
                        for professional inquiries and collaboration.
                    </p>
                </div>

                <div class="contact-card">
                    <h3>DAVID</h3>
                    <p class="role">Fullstack Developer</p>
                    <p>
                        Develops the Login, Register, and Forgot Password pages, focusing on secure
                        authentication flows and reliable account access features.
                    </p>
                    <p>
                        Reach me via Instagram <a href="https://www.instagram.com/davidla6_?igsh=MW5lYW8xN3NrYzg2ag==" target="_blank">@davidla6_</a>
                        for professional inquiries and collaboration.
                    </p>
                </div>
            </div>

            <p class="contact-footer">
                Flowlist development team is committed to building organized, reliable, and user-friendly digital solutions.
            </p>
        </div>
    </div>

    <div class="terms-overlay" id="termsOverlay">
        <div class="terms-content">
            <span class="terms-close" id="termsClose">
                <ion-icon name="close"></ion-icon>
            </span>

            <h2>Terms &amp; Conditions</h2>
            <p class="terms-subtitle">
                Please read these terms and conditions carefully before using Flowlist.
            </p>

            <div class="terms-section">
                <h3>1. Acceptance of Use</h3>
                <p>
                    By accessing and using Flowlist, you agree to use the platform responsibly
                    and in accordance with these terms. If you do not agree, you should discontinue
                    the use of the service.
                </p>
            </div>

            <div class="terms-section">
                <h3>2. User Responsibility</h3>
                <p>
                    Users are responsible for maintaining the accuracy of their task information,
                    protecting their account credentials, and ensuring that their use of the platform
                    does not violate any applicable laws or regulations.
                </p>
            </div>

            <div class="terms-section">
                <h3>3. Platform Usage</h3>
                <p>
                    Flowlist is intended to support task organization, productivity, and personal
                    workflow management. Users may not misuse the system for harmful, unlawful,
                    or disruptive activities.
                </p>
            </div>

            <div class="terms-section">
                <h3>4. Data and Privacy</h3>
                <p>
                    Information entered into Flowlist is used to provide task management features
                    and improve user experience. Users should avoid submitting sensitive information
                    unless the platform explicitly supports and protects such data.
                </p>
            </div>

            <div class="terms-section">
                <h3>5. Service Availability</h3>
                <p>
                    We aim to keep Flowlist available and reliable, but uninterrupted access cannot
                    be guaranteed at all times. Maintenance, updates, or technical issues may
                    temporarily affect service availability.
                </p>
            </div>

            <div class="terms-section">
                <h3>6. Changes to Terms</h3>
                <p>
                    These terms may be updated from time to time to reflect service improvements,
                    policy adjustments, or operational changes. Continued use of Flowlist after
                    updates indicates acceptance of the revised terms.
                </p>
            </div>

            <p class="terms-footer">
                By continuing to use Flowlist, you acknowledge that you have read, understood,
                and agreed to these Terms &amp; Conditions.
            </p>
        </div>
    </div>

    <div class="auth-layout">
        <div class="auth-left">
            <div class="auth-brand-badge">
                <div class="auth-brand-icon">
                    <ion-icon name="grid"></ion-icon>
                </div>
                <span>Flowlist</span>
            </div>

            <div class="auth-hero">
                <h1>
                    Organize tasks.<br>
                    Stay focused.<br>
                    <span>Get things done.</span>
                </h1>

                <p>
                    Flowlist helps you manage your tasks,
                    track progress, and boost productivity
                    every day.
                </p>
            </div>

            <div class="auth-visual">
                <div class="visual-card visual-main">
                    <div class="visual-line short"></div>
                    <div class="visual-line"></div>
                    <div class="visual-line"></div>
                    <div class="visual-line short"></div>
                </div>

                <div class="visual-card visual-side">
                    <div class="visual-check">
                        <ion-icon name="checkmark"></ion-icon>
                    </div>
                    <div class="visual-check">
                        <ion-icon name="checkmark"></ion-icon>
                    </div>
                    <div class="visual-check">
                        <ion-icon name="checkmark"></ion-icon>
                    </div>
                </div>

                <div class="visual-circle"></div>
                <div class="visual-dot-grid"></div>
            </div>
        </div>

        <div class="auth-right">
            <div class="wrapper">
                <div class="form-box login">
                    <div class="login-top-icon">
                        <ion-icon name="lock-closed"></ion-icon>
                    </div>

                    <h2>Welcome Back</h2>
                    <p class="login-subtitle">Login to your Flowlist account</p>

                    <form action="{{ route('login.process') }}" method="post" id="loginForm">
                        @csrf
                        <div class="input-box">
                            <span class="icon"><ion-icon name="mail"></ion-icon></span>
                            <input type="text" name="login" id="loginInput" required placeholder=" ">
                            <label>Email / Username</label>
                        </div>

                        <div class="input-box password-box">
                            <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                            <input type="password" name="password" id="passwordInput" required placeholder=" ">
                            <label>Password</label>

                            <!-- Tombol untuk melihat / menyembunyikan password -->
                            <button type="button" class="password-toggle-btn" id="passwordToggleBtn" aria-label="Toggle password visibility">
                                <ion-icon name="eye-off-outline" id="passwordToggleIcon"></ion-icon>
                            </button>
                        </div>

                        <div class="remember-forgot">
                            <label>
                                <input type="checkbox" id="rememberMeCheckbox" name="remember" value="1">
                                Remember Me
                            </label>
                            <a href="{{ route('password.request') }}" id="forgot-password-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn">Login</button>

                        <div class="login-register">
                            <p>Don't have an account? Click <a href="{{ route('register') }}" class="register-link" id="bottom-register-link">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="login-alert-overlay" id="loginAlertOverlay">
        <div class="login-alert-box" id="loginAlertBox">
            <div class="login-alert-icon">
                <ion-icon name="shield-checkmark-outline"></ion-icon>
            </div>

            <h3 id="loginAlertTitle">Login Status</h3>
            <p id="loginAlertMessage">Your login message will appear here.</p>

            <button type="button" class="login-alert-btn" id="loginAlertBtn">
                Continue
            </button>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        window.FlowlistRoutes = {
            loginProcess: @json(route('login.process')),
            register: @json(route('register')),
            forgotPassword: @json(route('password.request')),
            dashboard: @json(route('dashboard'))
        };
    </script>
    <script src="{{ asset('assets/js/login.js') }}"></script>

</body>
</html>