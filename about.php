<?php
$page_title = "About Us";
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container my-5 ">
    <!-- About Intro Banner -->
    <div class="glass-panel mb-5 text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-bold">OUR STORY</span>
        <h1 class="fw-bold mb-3">Connecting People with Experiences</h1>
        <p class="lead text-muted mx-auto" style="max-width: 800px;">
            Founded in 2026, EventMan was built to remove friction from booking live experiences. We make listing events, tracking capacities, and ticketing reliable and easy.
        </p>
    </div>

    <!-- Vision & Mission Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-eye service-icon"></i>
                <h3 class="fw-bold mb-3 ">Our Vision</h3>
                <p class="text-muted">
                    To be the leading global platform for discoverable and secure ticket purchasing, bridging local organizers and enthusiastic event-goers through bleeding-edge web tech.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-bullseye service-icon"></i>
                <h3 class="fw-bold mb-3 ">Our Mission</h3>
                <p class="text-muted">
                    Provide real-time booking confidence, safeguard customer transactions, support developers with easy event structures, and keep capacities fully balanced for maximum event success.
                </p>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <h2 class="fw-bold text-center mb-4 ">Why Event Goers Choose Us</h2>
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="glass-panel text-center h-100 p-4">
                <i class="fa-solid fa-shield-halved fa-2x mb-3 text-violet"></i>
                <h5 class="fw-bold  mb-2">100% Secure</h5>
                <p class="text-muted small mb-0">Hashed credentials, SSL ready, and transaction protection.</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="glass-panel text-center h-100 p-4">
                <i class="fa-solid fa-bolt fa-2x mb-3 text-violet"></i>
                <h5 class="fw-bold  mb-2">Instant Booking</h5>
                <p class="text-muted small mb-0">Book a seat in under 30 seconds with instant email and PDF slip generation.</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="glass-panel text-center h-100 p-4">
                <i class="fa-solid fa-clock-rotate-left fa-2x mb-3 text-violet"></i>
                <h5 class="fw-bold  mb-2">Easy Cancellation</h5>
                <p class="text-muted small mb-0">Cancel anytime before the scheduled start directly from your panel.</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="glass-panel text-center h-100 p-4">
                <i class="fa-solid fa-headphones fa-2x mb-3 text-violet"></i>
                <h5 class="fw-bold  mb-2">24/7 Support</h5>
                <p class="text-muted small mb-0">Our dedicated support desk is available day or night to assist you.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>