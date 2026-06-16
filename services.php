<?php
$page_title = "Our Services";
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container my-5 ">
    <div class="glass-panel mb-5 text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-bold">WHAT WE OFFER</span>
        <h1 class="fw-bold mb-3">Comprehensive Ticketing & Event Management</h1>
        <p class="lead text-muted mx-auto" style="max-width: 800px;">
            Whether you are booking a single local seat or managing a global arena concert, EventMan provides the specialized services to handle everything smoothly.
        </p>
    </div>

    <!-- Services Cards Grid -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-ticket service-icon"></i>
                <h4 class="fw-bold mb-2">Real-Time Ticketing</h4>
                <p class="text-muted small">
                    Dynamically allocates seats, updates available capacity instantaneously, and prevents duplicate sales using isolated SQL database transactions.
                </p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-chart-line service-icon"></i>
                <h4 class="fw-bold mb-2">Organizer Analytics</h4>
                <p class="text-muted small">
                    Admin access to comprehensive revenue distributions, capacity trends, registration counts, and graphical charts built on Chart.js.
                </p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-qrcode service-icon"></i>
                <h4 class="fw-bold mb-2">Digital Check-in</h4>
                <p class="text-muted small">
                    Generate scannable codes and booking slips for participants, making attendance logging fast and contactless at the venue gate.
                </p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-calendar-plus service-icon"></i>
                <h4 class="fw-bold mb-2">Seamless Event Creation</h4>
                <p class="text-muted small">
                    Upload images, set variable prices, define venue boundaries, schedule dates and times, and target local audiences in minutes.
                </p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-bell service-icon"></i>
                <h4 class="fw-bold mb-2">Notification System</h4>
                <p class="text-muted small">
                    Keep your attendees updated on rescheduling, capacity extensions, or venue shifts with instant alert communications.
                </p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="service-card h-100">
                <i class="fa-solid fa-lock service-icon"></i>
                <h4 class="fw-bold mb-2">Secure Transactions</h4>
                <p class="text-muted small">
                    Anti-fraud checks, credential hashing, session isolation, and standard security implementations protect customer and organizer profiles.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
