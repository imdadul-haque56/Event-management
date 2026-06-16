<?php
$page_title = "Manage Bookings";
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Guard: Require Admin Role
require_admin();

$error = '';
$success = '';

// ----------------------------------------------------
// Action Handler: Status overrides
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($booking_id > 0) {
        // A. CANCEL BOOKING
        if ($action === 'cancel') {
            try {
                $pdo->beginTransaction();

                // Lock booking & fetch quantity + event ID
                $stmt = $pdo->prepare("SELECT quantity, event_id, booking_status FROM bookings WHERE id = ? FOR UPDATE");
                $stmt->execute([$booking_id]);
                $booking = $stmt->fetch();

                if (!$booking) {
                    $pdo->rollBack();
                    $error = "Booking not found.";
                } elseif ($booking['booking_status'] === 'cancelled') {
                    $pdo->rollBack();
                    $error = "Booking is already cancelled.";
                } else {
                    // 1. Update status
                    $upStmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
                    $upStmt->execute([$booking_id]);

                    // 2. Refund seats
                    $refundStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats + ? WHERE id = ?");
                    $refundStmt->execute([$booking['quantity'], $booking['event_id']]);

                    $pdo->commit();
                    $success = "Booking #EM-" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . " has been cancelled.";
                }
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Transaction failed. Code: " . $e->getMessage();
            }
        }

        // B. CONFIRM (RE-CONFIRM) BOOKING
        elseif ($action === 'confirm') {
            try {
                $pdo->beginTransaction();

                // Lock booking & fetch quantity + event ID
                $stmt = $pdo->prepare("SELECT quantity, event_id, booking_status FROM bookings WHERE id = ? FOR UPDATE");
                $stmt->execute([$booking_id]);
                $booking = $stmt->fetch();

                if (!$booking) {
                    $pdo->rollBack();
                    $error = "Booking not found.";
                } elseif ($booking['booking_status'] === 'confirmed') {
                    $pdo->rollBack();
                    $error = "Booking is already confirmed.";
                } else {
                    // Check seat availability in locked event
                    $eventStmt = $pdo->prepare("SELECT available_seats FROM events WHERE id = ? FOR UPDATE");
                    $eventStmt->execute([$booking['event_id']]);
                    $avail_seats = $eventStmt->fetchColumn();

                    if ($avail_seats < $booking['quantity']) {
                        $pdo->rollBack();
                        $error = "Cannot confirm. Insufficient seats available in the event (only $avail_seats left).";
                    } else {
                        // 1. Update status
                        $upStmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE id = ?");
                        $upStmt->execute([$booking_id]);

                        // 2. Deduct seats
                        $deductStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
                        $deductStmt->execute([$booking['quantity'], $booking['event_id']]);

                        $pdo->commit();
                        $success = "Booking #EM-" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . " has been re-confirmed.";
                    }
                }
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Transaction failed. Code: " . $e->getMessage();
            }
        }
    }
}

// Fetch all bookings with user and event details
try {
    $bookings = $pdo->query("
        SELECT b.*, u.full_name, u.email, e.title, e.event_date 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN events e ON b.event_id = e.id 
        ORDER BY b.booking_date DESC
    ")->fetchAll();
} catch (\PDOException $e) {
    $bookings = [];
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
                    <a href="dashboard.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                    <a href="events.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-calendar-days"></i> Manage Events
                    </a>
                    <a href="bookings.php" class="sidebar-link active rounded">
                        <i class="fa-solid fa-receipt"></i> Manage Bookings
                    </a>
                    <a href="users.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-users"></i> Manage Users
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="col-lg-9 col-md-8">
            <!-- Notices -->
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

            <div class="glass-panel">
                <h4 class="fw-bold mb-4"><i class="fa-solid fa-file-invoice-dollar text-violet me-2"></i> Booking Reservations Registry</h4>

                <div class="table-responsive">
                    <table class="table  table-hover align-middle border-light mb-0">
                        <thead>
                            <tr class="text-muted">
                                <th>Ref ID</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th>Booking Date</th>
                                <th class="text-center">Tickets</th>
                                <th class="text-end">Total Price</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td class="fw-semibold">#EM-<?php echo str_pad($b['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($b['full_name']); ?></strong><br>
                                            <span class="small text-muted"><?php echo htmlspecialchars($b['email']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($b['title']); ?></strong><br>
                                            <span class="small text-muted">Event: <?php echo date('M d, Y', strtotime($b['event_date'])); ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($b['booking_date'])); ?></td>
                                        <td class="text-center"><?php echo $b['quantity']; ?></td>
                                        <td class="text-end text-success fw-bold">₹<?php echo number_format($b['total_price'], 2); ?></td>
                                        <td>
                                            <?php if ($b['booking_status'] === 'confirmed'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Confirmed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="bookings.php" method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <?php if ($b['booking_status'] === 'confirmed'): ?>
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fa-solid fa-ban"></i> Cancel
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                                        <i class="fa-solid fa-circle-check"></i> Reconfirm
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-receipt fa-3x mb-3"></i>
                                        <p class="mb-0">No booking registrations exist in the system registry.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>