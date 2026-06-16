<?php
$page_title = "Events Directory";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Server-side filtering logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['date']) ? trim($_GET['date']) : '';
$price_filter = isset($_GET['category']) ? trim($_GET['category']) : 'all';

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR venue LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($filter_date)) {
    $sql .= " AND event_date = ?";
    $params[] = $filter_date;
}

if ($price_filter !== 'all') {
    if ($price_filter === 'free') {
        $sql .= " AND ticket_price = 0";
    } elseif ($price_filter === 'paid') {
        $sql .= " AND ticket_price > 0";
    } elseif ($price_filter === 'under-50') {
        $sql .= " AND ticket_price < 50";
    } elseif ($price_filter === 'above-50') {
        $sql .= " AND ticket_price >= 50";
    }
}

$sql .= " ORDER BY event_date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    
    // Fetch all active event dates to feed the calendar sidebar
    $datesStmt = $pdo->query("SELECT DISTINCT event_date FROM events WHERE event_date >= CURDATE()");
    $all_event_dates = $datesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    $events = [];
    $all_event_dates = [];
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">
        
        <!-- Filters Sidebar & Calendar -->
        <div class="col-lg-4">
            <div class="d-grid gap-4 position-sticky" style="top: 80px;">
                <!-- Search & Filters Panel -->
                <div class="glass-panel ">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-filter text-violet me-2"></i> Filter Catalog</h5>
                    
                    <form action="events.php" method="GET">
                        <!-- Keep date if active -->
                        <?php if(!empty($filter_date)): ?>
                            <input type="hidden" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
                        <?php endif; ?>

                        <!-- Text Search -->
                        <div class="mb-3">
                            <label for="event-search" class="form-label">Search Query</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" class="form-control" id="event-search" name="search" placeholder="Type title, venue..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>

                        <!-- Price filter -->
                        <div class="mb-4">
                            <label for="event-filter-category" class="form-label">Ticket Cost Category</label>
                            <select class="form-select" id="event-filter-category" name="category">
                                <option value="all" <?php echo $price_filter === 'all' ? 'selected' : ''; ?>>All Pricing</option>
                                <option value="free" <?php echo $price_filter === 'free' ? 'selected' : ''; ?>>Free Entry</option>
                                <option value="paid" <?php echo $price_filter === 'paid' ? 'selected' : ''; ?>>Paid Tickets</option>
                                <option value="under-50" <?php echo $price_filter === 'under-50' ? 'selected' : ''; ?>>Under $50</option>
                                <option value="above-50" <?php echo $price_filter === 'above-50' ? 'selected' : ''; ?>>$50 and above</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-premium w-100 mb-2">
                            <i class="fa-solid fa-filter me-2"></i> Apply Filter
                        </button>
                        
                        <?php if (!empty($search) || !empty($filter_date) || $price_filter !== 'all'): ?>
                            <a href="events.php" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fa-solid fa-rotate-left me-1"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Interactive Calendar Card -->
                <div class="glass-panel ">
                    <h5 class="fw-bold mb-3"><i class="fa-regular fa-calendar-days text-violet me-2"></i> Event Calendar</h5>
                    <script>
                        window.activeEventDates = <?php echo json_encode($all_event_dates); ?>;
                    </script>
                    <div id="interactive-calendar" class="calendar-container"></div>
                    <?php if (!empty($filter_date)): ?>
                        <div class="text-center mt-3">
                            <span class="badge bg-secondary p-2">
                                Filtering: <?php echo date('M d, Y', strtotime($filter_date)); ?>
                                <a href="events.php" class=" ms-2 text-decoration-none"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Events Catalog Listing -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4 ">
                <div>
                    <h2 class="fw-bold mb-0">Events Listing</h2>
                    <p class="text-muted small mb-0">Showing <?php echo count($events); ?> available sessions</p>
                </div>
            </div>

            <div class="row g-4">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="col-md-6 event-card-item" 
                             data-title="<?php echo htmlspecialchars($event['title']); ?>"
                             data-venue="<?php echo htmlspecialchars($event['venue']); ?>"
                             data-desc="<?php echo htmlspecialchars($event['description']); ?>"
                             data-price="<?php echo $event['ticket_price']; ?>">
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
                                <div class="p-4  d-flex flex-column justify-content-between" style="min-height: 250px;">
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
                                            <?php echo htmlspecialchars(substr($event['description'], 0, 120)) . (strlen($event['description']) > 120 ? '...' : ''); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span class="text-truncate" style="max-width: 260px;"><?php echo htmlspecialchars($event['venue']); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge <?php echo $event['available_seats'] > 10 ? 'bg-light border border-light text-dark' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?>">
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
                            <h4>No matching events found</h4>
                            <p class="text-muted">Try clearing your filters or testing with a different search phrase.</p>
                            <a href="events.php" class="btn btn-premium btn-sm mt-2">
                                <i class="fa-solid fa-rotate-left"></i> View All Events
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
