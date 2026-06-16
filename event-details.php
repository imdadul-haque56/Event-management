<?php
$page_title = "Event Details";
require_once 'includes/db.php';
require_once 'includes/auth.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($event_id <= 0) {
    header("Location: events.php");
    exit();
}

$error = '';
$success = '';
$booking_info = null;

// Fetch Event Details
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    if (!$event) {
        header("Location: events.php");
        exit();
    }
} catch (\PDOException $e) {
    die("Database error. Please try again later.");
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    // 1. Force Authentication
    require_login();
    
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($quantity <= 0) {
        $error = "Please choose a valid number of tickets.";
    } elseif ($quantity > $event['available_seats']) {
        $error = "Sorry, only " . $event['available_seats'] . " tickets are available.";
    } else {
        try {
            // Begin safe transaction to lock tables and update seat counts
            $pdo->beginTransaction();

            // Re-fetch event with lock to verify seats under load
            $lockStmt = $pdo->prepare("SELECT available_seats, ticket_price FROM events WHERE id = ? FOR UPDATE");
            $lockStmt->execute([$event_id]);
            $lockedEvent = $lockStmt->fetch();

            if ($lockedEvent['available_seats'] < $quantity) {
                $pdo->rollBack();
                $error = "Transaction failed. Only " . $lockedEvent['available_seats'] . " seats left.";
            } else {
                // Calculate actual price server-side (for security, don't trust post input values)
                $calculated_total = $lockedEvent['ticket_price'] * $quantity;

                // 1. Decrement Seats
                $updateStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
                $updateStmt->execute([$quantity, $event_id]);

                // 2. Insert Booking Record
                $insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, quantity, total_price, booking_status) VALUES (?, ?, ?, ?, 'confirmed')");
                $insertStmt->execute([$user_id, $event_id, $quantity, $calculated_total]);
                
                $booking_id = $pdo->lastInsertId();

                $pdo->commit();

                // Store state for visual summary card
                $success = "Tickets booked successfully!";
                $booking_info = [
                    'id' => $booking_id,
                    'quantity' => $quantity,
                    'total' => $calculated_total,
                    'title' => $event['title'],
                    'date' => $event['event_date'],
                    'time' => $event['event_time'],
                    'venue' => $event['venue']
                ];
                
                // Re-fetch event details to update active page UI
                $stmt->execute([$event_id]);
                $event = $stmt->fetch();
            }

        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "An error occurred while booking. Please try again. Code: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container my-5 ">
    
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="events.php" class="text-decoration-none text-muted">Events</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page"><?php echo htmlspecialchars($event['title']); ?></li>
        </ol>
    </nav>

    <!-- Error/Success callouts -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success) && $booking_info): ?>
        <div class="glass-panel border-success mb-5">
            <div class="text-center py-3">
                <i class="fa-solid fa-circle-check fa-4x text-success mb-3 animate-bounce"></i>
                <h3 class="fw-bold text-success">Booking Confirmed!</h3>
                <p class="text-muted">Your seats have been reserved successfully. Here is your receipt:</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="bg-light p-4 rounded-3 border border-light">
                        <div class="d-flex justify-content-between border-bottom border-light pb-3 mb-3">
                            <span class="fw-bold text-violet">Booking Reference ID:</span>
                            <span class="fw-bold">#EM-<?php echo str_pad($booking_info['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">EVENT:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($booking_info['title']); ?></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">VENUE:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($booking_info['venue']); ?></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">DATE & TIME:</span>
                                <span class="fw-bold">
                                    <?php echo date('M d, Y', strtotime($booking_info['date'])); ?> @ 
                                    <?php echo date('h:i A', strtotime($booking_info['time'])); ?>
                                </span>
                            </div>
                            <div class="col-sm-3 col-6">
                                <span class="text-muted small d-block">TICKETS:</span>
                                <span class="fw-bold"><?php echo $booking_info['quantity']; ?> ticket(s)</span>
                            </div>
                            <div class="col-sm-3 col-6">
                                <span class="text-muted small d-block">TOTAL PRICE:</span>
                                <span class="fw-bold text-success">₹<?php echo number_format($booking_info['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="user/dashboard.php" class="btn btn-premium px-4">
                            <i class="fa-solid fa-ticket me-2"></i> Go to My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Event Detail Layout -->
    <div class="row g-5">
        <!-- Visual Column -->
        <div class="col-lg-7">
            <div class="rounded-4 overflow-hidden mb-4 border border-light shadow-lg" style="max-height: 450px;">
                <?php if (!empty($event['event_image']) && file_exists('uploads/events/' . $event['event_image'])): ?>
                    <img src="uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" class="w-100 h-100 object-fit-cover" style="object-fit: cover; width: 100%; height: 100%;" alt="<?php echo htmlspecialchars($event['title']); ?>">
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center -50" style="height: 380px;">
                        <i class="fa-solid fa-image fa-5x"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($event['title']); ?></h2>
            <hr class="border-light mb-4">
            
            <h5 class="fw-bold text-violet mb-3">About this Event</h5>
            <p class="text-muted" style="line-height: 1.8; white-space: pre-line;">
                <?php echo htmlspecialchars($event['description']); ?>
            </p>
        </div>

        <!-- Meta Details & Booking Action Sidebar -->
        <div class="col-lg-5">
            <div class="d-grid gap-4">
                <!-- Info Summary Card -->
                <div class="glass-panel">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-info text-violet me-2"></i> Event Schedule</h5>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light p-3 rounded border border-light text-center text-violet" style="min-width: 60px;">
                            <i class="fa-regular fa-calendar-days fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">DATE:</span>
                            <span class="fw-bold"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light p-3 rounded border border-light text-center text-violet" style="min-width: 60px;">
                            <i class="fa-regular fa-clock fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">TIME:</span>
                            <span class="fw-bold"><?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light p-3 rounded border border-light text-center text-violet" style="min-width: 60px;">
                            <i class="fa-solid fa-location-dot fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">VENUE:</span>
                            <span class="fw-bold"><?php echo htmlspecialchars($event['venue']); ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-3 rounded border border-light text-center text-violet" style="min-width: 60px;">
                            <i class="fa-solid fa-dollar-sign fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">TICKET PRICE:</span>
                            <span class="fw-bold text-success">₹<?php echo number_format($event['ticket_price'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Booking Action Card -->
                <div class="glass-panel border-violet shadow">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-ticket text-violet me-2"></i> Book Tickets</h5>
                    
                    <div class="d-flex justify-content-between mb-3 text-muted small">
                        <span>Availability Status:</span>
                        <?php if ($event['available_seats'] > 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                                <?php echo $event['available_seats']; ?> / <?php echo $event['total_seats']; ?> Seats Free
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Sold Out</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($event['available_seats'] > 0): ?>
                        <?php if (is_logged_in()): ?>
                            <!-- Numeric Helper Values for calculation script -->
                            <input type="hidden" id="ticket_price_value" value="<?php echo $event['ticket_price']; ?>">

                            <form id="booking-form" action="event-details.php?id=<?php echo $event['id']; ?>" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="book">
                                
                                <div class="mb-4">
                                    <label for="ticket_quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="ticket_quantity" name="quantity" min="1" max="<?php echo $event['available_seats']; ?>" value="1" required>
                                    <div class="invalid-feedback">Please choose between 1 and <?php echo $event['available_seats']; ?>.</div>
                                    <span class="text-muted small mt-1 d-block">Max available tickets: <?php echo $event['available_seats']; ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded border border-light">
                                    <span class="fw-semibold">Total Price:</span>
                                    <span class="fs-4 fw-bold text-success" id="total_price_display">$0.00</span>
                                </div>

                                <!-- Trigger Confirm Modal -->
                                <button type="button" class="btn btn-premium w-100 py-3" data-bs-toggle="modal" data-bs-target="#confirmBookingModal">
                                    <i class="fa-solid fa-cart-shopping me-2"></i> Reserve Ticket(s)
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Guest Prompt -->
                            <div class="text-center py-3">
                                <p class="text-muted small mb-3">You must be logged in to book ticket reservations.</p>
                                <a href="login.php" class="btn btn-premium w-100 py-3">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> Login to Book
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Sold Out Notice -->
                        <button class="btn btn-secondary w-100 py-3" disabled>
                            <i class="fa-solid fa-ban me-2"></i> Booking Unavailable (Sold Out)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Confirmation Modal -->
<?php if (is_logged_in() && $event['available_seats'] > 0): ?>
    <div class="modal fade " id="confirmBookingModal" tabindex="-1" aria-labelledby="confirmBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="confirmBookingModalLabel">
                        <i class="fa-solid fa-circle-question text-violet me-2"></i> Confirm Ticket Purchase
                    </h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Are you sure you want to proceed with reserving these tickets?</p>
                    <div class="bg-light p-3 rounded mb-3 border border-light text-start">
                        <p class="mb-1 small text-muted">EVENT:</p>
                        <p class="fw-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></p>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="small text-muted d-block">UNIT PRICE:</span>
                                <span class="fw-bold text-success">$<?php echo number_format($event['ticket_price'], 2); ?></span>
                            </div>
                            <div class="col-6">
                                <span class="small text-muted d-block">TOTAL:</span>
                                <span class="fw-bold text-success" id="modal_total_display">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-premium btn-sm px-4" onclick="submitBookingForm()">Confirm Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal Price Binding Script Helper -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const confirmModal = document.getElementById('confirmBookingModal');
            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', () => {
                    const totalDisplay = document.getElementById('total_price_display').textContent;
                    document.getElementById('modal_total_display').textContent = totalDisplay;
                });
            }
        });

        function submitBookingForm() {
            // Dismiss modal
            const modalEl = document.getElementById('confirmBookingModal');
            const modalInst = bootstrap.Modal.getInstance(modalEl);
            if (modalInst) modalInst.hide();
            
            // Submit form
            document.getElementById('booking-form').submit();
        }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
