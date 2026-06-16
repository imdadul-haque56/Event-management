<?php
$page_title = "Admin Dashboard";
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Guard: Require Admin Role
require_admin();

try {
    // 1. Fetch Metrics
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    
    $revenueSum = $pdo->query("
        SELECT SUM(total_price) 
        FROM bookings 
        WHERE booking_status = 'confirmed'
    ")->fetchColumn() ?: 0.00;

    // 2. Fetch Chart Data: Revenue per Event
    $revenueChartStmt = $pdo->query("
        SELECT e.title, SUM(b.total_price) as revenue 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE b.booking_status = 'confirmed' 
        GROUP BY b.event_id 
        LIMIT 6
    ");
    $revenue_chart_data = $revenueChartStmt->fetchAll();

    $rev_labels = [];
    $rev_values = [];
    foreach ($revenue_chart_data as $row) {
        $rev_labels[] = $row['title'];
        $rev_values[] = floatval($row['revenue']);
    }

    // 3. Fetch Chart Data: Occupancy per Event (booked seats vs available seats)
    $occupancyChartStmt = $pdo->query("
        SELECT title, (total_seats - available_seats) as booked_seats, available_seats 
        FROM events 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $occupancy_chart_data = $occupancyChartStmt->fetchAll();

    $occ_labels = [];
    $occ_booked = [];
    $occ_available = [];
    foreach ($occupancy_chart_data as $row) {
        $occ_labels[] = $row['title'];
        $occ_booked[] = intval($row['booked_seats']);
        $occ_available[] = intval($row['available_seats']);
    }

} catch (\PDOException $e) {
    die("Database access error. Code: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container-fluid my-4 ">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="glass-panel p-3">
                <div class="text-center py-3 border-bottom border-light mb-3">
                    <i class="fa-solid fa-user-shield fa-3x text-violet mb-2"></i>
                    <h5 class="fw-bold mb-0">Admin Portal</h5>
                    <span class="text-muted small"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                </div>
                <nav class="d-grid gap-2">
                    <a href="dashboard.php" class="sidebar-link active rounded">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                    <a href="events.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-calendar-days"></i> Manage Events
                    </a>
                    <a href="bookings.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-receipt"></i> Manage Bookings
                    </a>
                    <a href="users.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-users"></i> Manage Users
                    </a>
                </nav>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">System Overview</h2>
                    <p class="text-muted small mb-0">Real-time platform metrics and status logs</p>
                </div>
            </div>

            <!-- KPI Cards Row -->
            <div class="row g-3 mb-5">
                <div class="col-sm-6 col-lg-3">
                    <div class="kpi-card ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title mb-1">Total Users</div>
                                <div class="kpi-value"><?php echo $userCount; ?></div>
                            </div>
                            <div class="kpi-icon text-violet"><i class="fa-solid fa-users"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="kpi-card ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title mb-1">Events Listed</div>
                                <div class="kpi-value"><?php echo $eventCount; ?></div>
                            </div>
                            <div class="kpi-icon text-accent"><i class="fa-solid fa-calendar-plus"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="kpi-card ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title mb-1">Bookings Count</div>
                                <div class="kpi-value"><?php echo $bookingCount; ?></div>
                            </div>
                            <div class="kpi-icon text-info"><i class="fa-solid fa-receipt"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="kpi-card ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title mb-1">Total Revenue</div>
                                <div class="kpi-value text-success">₹<?php echo number_format($revenueSum, 2); ?></div>
                            </div>
                            <div class="kpi-icon text-success"><i class="fa-solid fa-wallet"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart reports container -->
            <div class="row g-4 mb-4">
                <!-- Revenue Bar Chart -->
                <div class="col-lg-7">
                    <div class="glass-panel p-4 h-100">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-column text-violet me-2"></i> Revenue by Event (₹)</h5>
                        <?php if(!empty($rev_labels)): ?>
                            <div style="height: 300px; position: relative;">
                                <canvas id="revenueBarChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-chart-bar fa-2x mb-3"></i>
                                <p class="mb-0">No booking transactions recorded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Seat Occupancy Chart -->
                <div class="col-lg-5">
                    <div class="glass-panel p-4 h-100">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie text-violet me-2"></i> Seat Status distribution</h5>
                        <?php if(!empty($occ_labels)): ?>
                            <div style="height: 300px; position: relative;">
                                <canvas id="occupancyPieChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-notch fa-2x mb-3"></i>
                                <p class="mb-0">No events listed in the catalog.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Revenue Chart configuration
    <?php if(!empty($rev_labels)): ?>
    const ctxRev = document.getElementById('revenueBarChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($rev_labels); ?>,
            datasets: [{
                label: 'Event Revenue ($)',
                data: <?php echo json_encode($rev_values); ?>,
                backgroundColor: 'rgba(124, 58, 237, 0.65)',
                borderColor: '#7c3aed',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.08)' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });
    <?php endif; ?>

    // 2. Occupancy Chart configuration
    <?php if(!empty($occ_labels)): ?>
    const ctxOcc = document.getElementById('occupancyPieChart').getContext('2d');
    new Chart(ctxOcc, {
        type: 'doughnut',
        data: {
            labels: ['Booked Seats', 'Available Seats'],
            datasets: [{
                data: [
                    <?php echo array_sum($occ_booked); ?>,
                    <?php echo array_sum($occ_available); ?>
                ],
                backgroundColor: [
                    'rgba(79, 70, 229, 0.85)',
                    'rgba(16, 185, 129, 0.85)'
                ],
                borderColor: [
                    '#4f46e5',
                    '#10b981'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94a3b8' }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>
