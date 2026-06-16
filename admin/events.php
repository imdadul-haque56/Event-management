<?php
$page_title = "Manage Events";
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Guard: Require Admin Role
require_admin();

$error = '';
$success = '';

// Setup uploads path
$upload_dir = '../uploads/events/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// ----------------------------------------------------
// Action Handler: POST operations
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // A. ADD EVENT
    if ($action === 'add') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $venue = trim($_POST['venue']);
        $ticket_price = floatval($_POST['ticket_price']);
        $total_seats = intval($_POST['total_seats']);
        
        // Basic inputs check
        if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($venue) || $total_seats <= 0) {
            $error = "Please fill in all required fields and enter positive seat counts.";
        } else {
            $image_name = null;
            
            // Image Upload check
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['event_image']['tmp_name'];
                $file_name = basename($_FILES['event_image']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($file_ext, $allowed)) {
                    $error = "Invalid image extension. Only JPG, PNG, and WEBP allowed.";
                } else {
                    $image_name = uniqid('event_', true) . '.' . $file_ext;
                    if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                        $error = "Failed to upload the image file.";
                        $image_name = null;
                    }
                }
            }

            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO events (title, description, event_date, event_time, venue, ticket_price, total_seats, available_seats, event_image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $description, $event_date, $event_time, $venue, $ticket_price, $total_seats, $total_seats, $image_name]);
                    $success = "Event successfully added.";
                } catch (\PDOException $e) {
                    $error = "Failed to insert event. Code: " . $e->getMessage();
                }
            }
        }
    }

    // B. EDIT EVENT
    elseif ($action === 'edit') {
        $event_id = intval($_POST['event_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $venue = trim($_POST['venue']);
        $ticket_price = floatval($_POST['ticket_price']);
        $total_seats = intval($_POST['total_seats']);

        if ($event_id <= 0 || empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($venue) || $total_seats <= 0) {
            $error = "Please fill in all fields correctly.";
        } else {
            try {
                // Fetch original event to calculate seats adjustment
                $stmt = $pdo->prepare("SELECT total_seats, available_seats, event_image FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                $orig = $stmt->fetch();

                if (!$orig) {
                    $error = "Event not found.";
                } else {
                    $booked_seats = $orig['total_seats'] - $orig['available_seats'];
                    
                    if ($total_seats < $booked_seats) {
                        $error = "Cannot decrease total capacity to $total_seats because $booked_seats seats are already booked.";
                    } else {
                        $new_available_seats = $total_seats - $booked_seats;
                        $image_name = $orig['event_image'];

                        // If a new image file is uploaded
                        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                            $file_tmp = $_FILES['event_image']['tmp_name'];
                            $file_name = basename($_FILES['event_image']['name']);
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                            if (!in_array($file_ext, $allowed)) {
                                $error = "Invalid image extension. Only JPG, PNG, and WEBP allowed.";
                            } else {
                                // Delete old image file
                                if (!empty($orig['event_image']) && file_exists($upload_dir . $orig['event_image'])) {
                                    @unlink($upload_dir . $orig['event_image']);
                                }

                                $image_name = uniqid('event_', true) . '.' . $file_ext;
                                move_uploaded_file($file_tmp, $upload_dir . $image_name);
                            }
                        }

                        if (empty($error)) {
                            $updateStmt = $pdo->prepare("
                                UPDATE events 
                                SET title = ?, description = ?, event_date = ?, event_time = ?, venue = ?, ticket_price = ?, total_seats = ?, available_seats = ?, event_image = ? 
                                WHERE id = ?
                            ");
                            $updateStmt->execute([$title, $description, $event_date, $event_time, $venue, $ticket_price, $total_seats, $new_available_seats, $image_name, $event_id]);
                            $success = "Event successfully updated.";
                            
                            // Redirect to normal view after editing
                            header("Location: events.php?success=" . urlencode($success));
                            exit();
                        }
                    }
                }
            } catch (\PDOException $e) {
                $error = "Failed to update event. Code: " . $e->getMessage();
            }
        }
    }

    // C. DELETE EVENT
    elseif ($action === 'delete') {
        $event_id = intval($_POST['event_id']);
        if ($event_id > 0) {
            try {
                // Fetch image to delete
                $stmt = $pdo->prepare("SELECT event_image FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                $img = $stmt->fetchColumn();
                
                if ($img && file_exists($upload_dir . $img)) {
                    @unlink($upload_dir . $img);
                }

                // Delete event (foreign keys cascade deletes bookings)
                $delStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $delStmt->execute([$event_id]);
                $success = "Event successfully deleted.";
            } catch (\PDOException $e) {
                $error = "Failed to delete event. Code: " . $e->getMessage();
            }
        }
    }
}

// Redirect Success bind
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// ----------------------------------------------------
// Render Helpers: Query Data
// ----------------------------------------------------
$edit_event = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_event = $stmt->fetch();
}

// Fetch all events for listing
try {
    $events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
} catch (\PDOException $e) {
    $events = [];
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
                    <a href="events.php" class="sidebar-link active rounded">
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

        <!-- Main Workspace -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Alert Panels -->
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

            <!-- EDIT EVENT SUB-PANEL -->
            <?php if ($edit_event): ?>
                <div class="glass-panel mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square text-violet me-2"></i> Edit Event Details</h4>
                        <a href="events.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Cancel Edit</a>
                    </div>
                    
                    <form action="events.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_event['title']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="venue" class="form-label">Venue / Location</label>
                                <input type="text" class="form-control" id="venue" name="venue" value="<?php echo htmlspecialchars($edit_event['venue']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="event_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $edit_event['event_date']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="event_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" value="<?php echo $edit_event['event_time']; ?>" required>
                            </div>

                            <div class="col-md-2">
                                <label for="ticket_price" class="form-label">Price (₹)</label>
                                <input type="number" class="form-control" id="ticket_price" name="ticket_price" step="0.01" min="0" value="<?php echo $edit_event['ticket_price']; ?>" required>
                            </div>

                            <div class="col-md-2">
                                <label for="total_seats" class="form-label">Capacity (Seats)</label>
                                <input type="number" class="form-control" id="total_seats" name="total_seats" min="1" value="<?php echo $edit_event['total_seats']; ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($edit_event['description']); ?></textarea>
                            </div>

                            <div class="col-md-8">
                                <label for="event_image" class="form-label">Replace Event Image (Optional)</label>
                                <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                                <span class="text-muted small">Allowed: JPG, PNG, WEBP. Leave blank to retain active file.</span>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <p class="small text-muted mb-1">Active Image Cover:</p>
                                <?php if ($edit_event['event_image'] && file_exists($upload_dir . $edit_event['event_image'])): ?>
                                    <img src="../uploads/events/<?php echo $edit_event['event_image']; ?>" class="rounded border border-light" style="height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="badge bg-secondary">No cover</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-premium mt-4 px-4 py-2">
                            <i class="fa-solid fa-circle-check me-2"></i> Save Event Changes
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- LISTING & CREATION WORKSPACE -->
            <div class="glass-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-list text-violet me-2"></i> Events Catalog Directory</h4>
                    <button class="btn btn-premium btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fa-solid fa-calendar-plus me-1"></i> Create New Event
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table  table-hover align-middle border-light mb-0">
                        <thead>
                            <tr class="text-muted">
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Schedule</th>
                                <th>Location</th>
                                <th class="text-end">Ticket price</th>
                                <th class="text-center">Seat Capacity</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['event_image'] && file_exists($upload_dir . $item['event_image'])): ?>
                                                <img src="../uploads/events/<?php echo $item['event_image']; ?>" class="rounded border border-light" style="width: 50px; height: 35px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded -50 text-center" style="width: 50px; height: 35px; line-height: 35px; font-size: 0.8rem;">
                                                    <i class="fa-solid fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($item['event_date'])); ?><br>
                                            <span class="small text-muted"><?php echo date('h:i A', strtotime($item['event_time'])); ?></span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($item['venue']); ?></td>
                                        <td class="text-end text-success fw-bold">₹<?php echo number_format($item['ticket_price'], 2); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark fw-bold"><?php echo $item['available_seats']; ?> / <?php echo $item['total_seats']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group gap-1">
                                                <a href="events.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm" onclick="triggerDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['title'])); ?>')">
                                                    <i class="fa-solid fa-trash-can"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fa-regular fa-calendar-times fa-3x mb-3"></i>
                                        <p class="mb-0">No events found in the database directory.</p>
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

<!-- Modal: ADD NEW EVENT -->
<div class="modal fade " id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="events.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addEventModalLabel">
                        <i class="fa-solid fa-calendar-plus text-violet me-2"></i> Create New Event
                    </h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="add_title" name="title" required>
                            <div class="invalid-feedback">Please provide a title.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_venue" class="form-label">Venue / Location</label>
                            <input type="text" class="form-control" id="add_venue" name="venue" required>
                            <div class="invalid-feedback">Please provide a venue.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="add_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="add_date" name="event_date" required>
                        </div>

                        <div class="col-md-4">
                            <label for="add_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="add_time" name="event_time" required>
                        </div>

                        <div class="col-md-2">
                            <label for="add_price" class="form-label">Ticket Price (₹)</label>
                            <input type="number" class="form-control" id="add_price" name="ticket_price" step="0.01" min="0" value="0.00" required>
                        </div>

                        <div class="col-md-2">
                            <label for="add_seats" class="form-label">Total Seats</label>
                            <input type="number" class="form-control" id="add_seats" name="total_seats" min="1" value="100" required>
                        </div>

                        <div class="col-12">
                            <label for="add_description" class="form-label">Event Description</label>
                            <textarea class="form-control" id="add_description" name="description" rows="4" required></textarea>
                            <div class="invalid-feedback">Please write a detailed event description.</div>
                        </div>

                        <div class="col-12">
                            <label for="add_image" class="form-label">Upload Event Image</label>
                            <input type="file" class="form-control" id="add_image" name="event_image" accept="image/*">
                            <span class="text-muted small">Allowed extensions: JPG, JPEG, PNG, WEBP. Max size: 2MB.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium btn-sm px-4">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: CONFIRM DELETE EVENT -->
<div class="modal fade " id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="events.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" id="delete_event_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="deleteEventModalLabel">
                        <i class="fa-solid fa-trash-can text-danger me-2"></i> Delete Event
                    </h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong class="text-violet" id="delete_event_title">this event</strong>?</p>
                    <p class="text-muted small">
                        Warning: This will permanently remove the event, its uploaded cover photo, and all bookings registered for this session from the system.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function triggerDeleteModal(eventId, eventTitle) {
        document.getElementById('delete_event_id').value = eventId;
        document.getElementById('delete_event_title').textContent = eventTitle;
        
        const modalEl = document.getElementById('deleteEventModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>
