<?php
$page_title = "Home";
require_once 'includes/db.php';
require_once 'includes/auth.php';

try {
    // Fetch 3 upcoming events with seats available
    $stmt = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() AND available_seats > 0 ORDER BY event_date ASC LIMIT 3");
    $featured_events = $stmt->fetchAll();
} catch (\PDOException $e) {
    $featured_events = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section  py-5 mb-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-bold">STREAMLINED BOOKING</span>
                <h1 class="hero-title mb-4">Discover & Book Amazing Events Around You</h1>
                <p class="lead text-muted mb-4">
                    Book tickets for the most anticipated tech workshops, live music concerts, sports matches, and local seminars. Simple. Safe. Seamless.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="events.php" class="btn btn-premium btn-lg px-4 py-3">
                        <i class="fa-solid fa-compass me-2"></i> Explore Events
                    </a>
                    <?php if (!is_logged_in()): ?>
                        <a href="register.php" class="btn btn-outline-premium btn-lg px-4 py-3">
                            Get Started <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="user/dashboard.php" class="btn btn-outline-premium btn-lg px-4 py-3">
                            My Dashboard <i class="fa-solid fa-user ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-panel  p-4">
                    <h4 class="fw-bold mb-3"><i class="fa-regular fa-clock me-2 text-violet"></i> Upcoming Events Calendar</h4>
                    <p class="text-muted small">Select highlighted dates in our active scheduler to view events instantly.</p>
                    
                    <!-- We'll populate this with active event dates -->
                    <?php
                        $datesArray = [];
                        try {
                            $datesStmt = $pdo->query("SELECT DISTINCT event_date FROM events WHERE event_date >= CURDATE()");
                            $datesArray = $datesStmt->fetchAll(PDO::FETCH_COLUMN);
                        } catch (\Exception $ex) {}
                    ?>
                    <script>
                        window.activeEventDates = <?php echo json_encode($datesArray); ?>;
                    </script>
                    
                    <div id="interactive-calendar" class="calendar-container"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Metrics Section -->
<section class="container py-5 mb-5">
    <div class="glass-panel bg-light border-light">
        <div class="row text-center g-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-number">50+</div>
                <div class="text-muted small fw-semibold uppercase">Active Events</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-number">12k+</div>
                <div class="text-muted small fw-semibold uppercase">Tickets Booked</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-number">40+</div>
                <div class="text-muted small fw-semibold uppercase">Popular Venues</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-number">99%</div>
                <div class="text-muted small fw-semibold uppercase">Happy Customers</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works / Platform Features Section -->
<section class="container py-5 mb-5">
    <div class="row align-items-center g-4 mb-4">
        <div class="col-lg-6">
            <div class="feature-intro">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-bold">FAST & EASY</span>
                <h2 class="fw-bold">Event booking made simple with a modern front-end.</h2>
                <p class="text-muted mb-4">Browse upcoming events, filter by date and price, and book your tickets instantly from a responsive Bootstrap interface.</p>
                <a href="events.php" class="btn btn-premium btn-lg px-4 py-3">
                    Browse Events <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h5 class="fw-bold mb-2">Explore Quickly</h5>
                        <p class="text-muted small">Search events by keyword, venue, or date with streamlined results.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3"><i class="fa-solid fa-calendar-check"></i></div>
                        <h5 class="fw-bold mb-2">Smart Calendar</h5>
                        <p class="text-muted small">Highlight active event dates and jump straight into the schedule.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3"><i class="fa-solid fa-ticket-simple"></i></div>
                        <h5 class="fw-bold mb-2">Ticket Booking</h5>
                        <p class="text-muted small">View pricing, seat availability, and complete your booking in seconds.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3"><i class="fa-solid fa-users-line"></i></div>
                        <h5 class="fw-bold mb-2">Manage Events</h5>
                        <p class="text-muted small">Users and admins get dedicated dashboards for bookings and event control.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events Grid Section -->
<section class="container py-5 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold  mb-1"><i class="fa-regular fa-star me-2 text-violet"></i> Featured Events</h2>
            <p class="text-muted mb-0">Handpicked premium sessions you shouldn't miss</p>
        </div>
        <a href="events.php" class="btn btn-outline-premium">
            View All <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php if (!empty($featured_events)): ?>
            <?php foreach ($featured_events as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-card-img-wrapper">
                            <?php if (!empty($event['event_image']) && file_exists('uploads/events/' . $event['event_image'])): ?>
                                <img src="uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" class="event-card-img" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <?php else: ?>
                                <div class="w-100 h-100 bg-secondary d-flex align-items-center justify-content-center -50">
                                    <i class="fa-solid fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <span class="event-badge">$<?php echo number_format($event['ticket_price'], 2); ?></span>
                        </div>
                        <div class="p-4  d-flex flex-column h-100 justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2 text-violet font-semibold small">
                                    <i class="fa-regular fa-calendar-days"></i>
                                    <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                    <span>&bull;</span>
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <h4 class="fw-bold mb-2 h5"><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?>
                                </p>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($event['venue']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light border border-light text-dark">
                                        <?php echo $event['available_seats']; ?> seats left
                                    </span>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-premium btn-sm">
                                        Book Now <i class="fa-solid fa-angle-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="glass-panel text-center  py-5">
                    <i class="fa-regular fa-calendar-times fa-3x mb-3 text-muted"></i>
                    <h4>No Upcoming Events Available</h4>
                    <p class="text-muted">Check back later or register to stay notified of upcoming workshops.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
