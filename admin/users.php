<?php
$page_title = "Manage Users";
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Guard: Require Admin Role
require_admin();

$error = '';
$success = '';
$current_admin_id = $_SESSION['user_id'];

// ----------------------------------------------------
// Action Handler: Users adjustments
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id > 0) {
        // A. TOGGLE USER ROLE
        if ($action === 'toggle_role') {
            if ($user_id === $current_admin_id) {
                $error = "Self-action blocked. You cannot revoke your own administrator privileges.";
            } else {
                try {
                    // Fetch user's current role
                    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $role = $stmt->fetchColumn();

                    if ($role) {
                        $new_role = ($role === 'admin') ? 'user' : 'admin';
                        $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $updateStmt->execute([$new_role, $user_id]);
                        $success = "User privilege updated to " . strtoupper($new_role) . ".";
                    } else {
                        $error = "User not found.";
                    }
                } catch (\PDOException $e) {
                    $error = "Failed to update role. Code: " . $e->getMessage();
                }
            }
        }
        
        // B. DELETE USER
        elseif ($action === 'delete') {
            if ($user_id === $current_admin_id) {
                $error = "Self-action blocked. You cannot delete your own administrative account.";
            } else {
                try {
                    $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $delStmt->execute([$user_id]);
                    $success = "User account and all their active bookings have been deleted.";
                } catch (\PDOException $e) {
                    $error = "Failed to delete user. Code: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all registered users
try {
    $users = $pdo->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
} catch (\PDOException $e) {
    $users = [];
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
                    <a href="bookings.php" class="sidebar-link rounded">
                        <i class="fa-solid fa-receipt"></i> Manage Bookings
                    </a>
                    <a href="users.php" class="sidebar-link active rounded">
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
                <h4 class="fw-bold mb-4"><i class="fa-solid fa-users text-violet me-2"></i> Registered Accounts Directory</h4>

                <div class="table-responsive">
                    <table class="table  table-hover align-middle border-light mb-0">
                        <thead>
                            <tr class="text-muted">
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Registration Date</th>
                                <th class="text-center">Role Status</th>
                                <th class="text-center">Action Operations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>#USR-<?php echo str_pad($u['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($u['created_at'])); ?></td>
                                        <td class="text-center">
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-light-subtle px-3 py-1">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($u['id'] !== $current_admin_id): ?>
                                                <div class="btn-group gap-2">
                                                    <!-- Toggle Privilege Form -->
                                                    <form action="users.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <input type="hidden" name="action" value="toggle_role">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                            <i class="fa-solid fa-arrows-spin"></i> Toggle Role
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Trigger Delete user modal -->
                                                    <button class="btn btn-outline-danger btn-sm" onclick="triggerUserDelete(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['full_name'])); ?>')">
                                                        <i class="fa-solid fa-user-minus"></i> Delete
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small fw-semibold">Active Session (You)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-users fa-3x mb-3"></i>
                                        <p class="mb-0">No users found in database registry.</p>
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

<!-- Modal: CONFIRM DELETE USER -->
<div class="modal fade " id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="users.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="delete_user_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="deleteUserModalLabel">
                        <i class="fa-solid fa-user-minus text-danger me-2"></i> Delete User Profile
                    </h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the user profile of <strong class="text-violet" id="delete_user_name">this user</strong>?</p>
                    <p class="text-muted small">
                        Warning: This will permanently delete their account credentials, login permissions, and cascade-remove all ticket booking reservations they have registered.
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
    function triggerUserDelete(userId, userName) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_user_name').textContent = userName;
        
        const modalEl = document.getElementById('deleteUserModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>
