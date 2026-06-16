<?php
$page_title = "Register";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// If already logged in, redirect
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic server-side validations
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "This email is already registered.";
            } else {
                // Determine user role (If it's the first user ever, make them admin for easy testing)
                $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $countStmt->fetchColumn();
                $role = ($userCount == 0) ? 'admin' : 'user';

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert into db
                $insertStmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$full_name, $email, $hashed_password, $role]);

                $success = "Registration successful! You can now <a href='login.php' class='alert-link'>Login</a>.";
                
                // Reset inputs
                $full_name = $email = '';
            }
        } catch (\PDOException $e) {
            $error = "Registration failed. Please try again later. Code: " . $e->getCode();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="glass-panel ">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-plus fa-3x mb-3 text-violet"></i>
                    <h2 class="fw-bold">Create Account</h2>
                    <p class="text-muted">Register to start booking event tickets</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="needs-validation" novalidate>
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-regular fa-user"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-solid fa-shield-halved"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">Please confirm your password.</div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-3">
                        <i class="fa-solid fa-user-plus me-2"></i> Register Account
                    </button>
                </form>

                <div class="text-center mt-3 small">
                    <span class="text-muted">Already have an account?</span>
                    <a href="login.php" class="text-decoration-none text-violet ms-1 fw-semibold">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
