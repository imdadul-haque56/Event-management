<?php
$page_title = "User Dashboard";
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Force login check
require_login();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle Booking Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($booking_id <= 0) {
        $error = "Invalid booking reference.";
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Fetch booking detail and join event info to check ownership and date
            $stmt = $pdo->prepare("
                SELECT b.*, e.event_date, e.id as event_id 
                FROM bookings b 
                JOIN events e ON b.event_id = e.id 
                WHERE b.id = ? AND b.user_id = ? FOR UPDATE
            ");
            $stmt->execute([$booking_id, $user_id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $pdo->rollBack();
                $error = "Booking record not found or access denied.";
            } elseif ($booking['booking_status'] === 'cancelled') {
                $pdo->rollBack();
                $error = "This booking has already been cancelled.";
            } elseif (strtotime($booking['event_date']) < time()) {
                // Cannot cancel past events
                $pdo->rollBack();
                $error = "Cannot cancel bookings for past events.";
            } else {
                // 1. Mark booking as cancelled
                $cancelStmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
                $cancelStmt->execute([$booking_id]);

                // 2. Return seats back to event pool
                $seatsStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats + ? WHERE id = ?");
                $seatsStmt->execute([$booking['quantity'], $booking['event_id']]);

                $pdo->commit();
                $success = "Booking #EM-" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . " was successfully cancelled.";
            }

        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Cancellation failed. Please try again. Code: " . $e->getMessage();
        }
    }
}

// Fetch dashboard KPIs
try {
    // Total Bookings
    $bookingsCountStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $bookingsCountStmt->execute([$user_id]);
    $total_bookings = $bookingsCountStmt->fetchColumn();

    // Active bookings (confirmed and event is in the future)
    $activeCountStmt = $pdo->prepare("
        SELECT SUM(b.quantity) 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE b.user_id = ? AND b.booking_status = 'confirmed' AND e.event_date >= CURDATE()
    ");
    $activeCountStmt->execute([$user_id]);
    $active_tickets = $activeCountStmt->fetchColumn() ?: 0;

    // Total spent (sum of total_price for confirmed bookings)
    $spentStmt = $pdo->prepare("SELECT SUM(total_price) FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
    $spentStmt->execute([$user_id]);
    $total_spent = $spentStmt->fetchColumn() ?: 0.00;

    // Fetch booking list
    $listStmt = $pdo->prepare("
        SELECT b.*, e.title, e.event_date, e.event_time, e.venue 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC
    ");
    $listStmt->execute([$user_id]);
    $user_bookings = $listStmt->fetchAll();

} catch (\PDOException $e) {
    die("Database error. Code: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container my-5 ">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-gauge text-violet me-2"></i> User Dashboard</h2>
            <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
        </div>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Panels -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="kpi-card ">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title mb-1">Total Reservations</div>
                        <div class="kpi-value "><?php echo $total_bookings; ?></div>
                    </div>
                    <div class="kpi-icon text-violet"><i class="fa-solid fa-receipt"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="kpi-card ">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title mb-1">Active Ticket(s)</div>
                        <div class="kpi-value "><?php echo $active_tickets; ?></div>
                    </div>
                    <div class="kpi-icon text-accent"><i class="fa-solid fa-ticket"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="kpi-card ">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title mb-1">Total Expenditure</div>
                        <div class="kpi-value text-success">₹<?php echo number_format($total_spent, 2); ?></div>
                    </div>
                    <div class="kpi-icon text-success"><i class="fa-solid fa-wallet"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table Panel -->
    <div class="glass-panel">
        <h4 class="fw-bold mb-4"><i class="fa-solid fa-list-check text-violet me-2"></i> Booking History</h4>

        <div class="table-responsive">
            <table class="table  table-hover align-middle border-light mb-0">
                <thead>
                    <tr class="text-muted">
                        <th>Ref ID</th>
                        <th>Event</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th class="text-center">Tickets</th>
                        <th class="text-end">Paid Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($user_bookings)): ?>
                        <?php foreach ($user_bookings as $booking): ?>
                            <tr>
                                <td class="fw-semibold">#EM-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($booking['title']); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($booking['event_date'])); ?><br>
                                    <span class="small text-muted"><?php echo date('h:i A', strtotime($booking['event_time'])); ?></span>
                                </td>
                                <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($booking['venue']); ?></td>
                                <td class="text-center"><?php echo $booking['quantity']; ?></td>
                                <td class="text-end text-success fw-bold">₹<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $is_upcoming = strtotime($booking['event_date']) >= time();
                                    if ($booking['booking_status'] === 'confirmed' && $is_upcoming): 
                                    ?>
                                        <!-- Trigger Cancel Modal -->
                                        <button class="btn btn-outline-danger btn-sm" onclick="triggerCancelModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars(addslashes($booking['title'])); ?>')">
                                            <i class="fa-solid fa-trash-can"></i> Cancel
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fa-solid fa-receipt fa-2x mb-3"></i>
                                <p class="mb-0">You have no reservation bookings listed.</p>
                                <a href="../events.php" class="btn btn-premium btn-sm mt-3">Book Your First Event</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Reservation Modal -->
<div class="modal fade " id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="booking_id" id="cancel_booking_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="cancelBookingModalLabel">
                        <i class="fa-solid fa-circle-exclamation text-danger me-2"></i> Cancel Reservation
                    </h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel your booking for <strong class="text-violet" id="cancel_event_title">this event</strong>?</p>
                    <p class="text-muted small">
                        This action will cancel your tickets, refund the seats back to the public pool, and is irreversible.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Yes, Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function triggerCancelModal(bookingId, eventTitle) {
        document.getElementById('cancel_booking_id').value = bookingId;
        document.getElementById('cancel_event_title').textContent = eventTitle;
        
        const modalEl = document.getElementById('cancelBookingModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>
