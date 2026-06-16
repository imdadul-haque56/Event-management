<?php
$page_title = "Login";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// If already logged in, redirect
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Fetch user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful, set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];

                // Determine redirect path
                if (isset($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect);
                } else {
                    if ($user['role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: user/dashboard.php");
                    }
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (\PDOException $e) {
            $error = "Authentication error. Please try again. Code: " . $e->getCode();
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
                    <i class="fa-solid fa-right-to-bracket fa-3x mb-3 text-violet"></i>
                    <h2 class="fw-bold">Welcome Back</h2>
                    <p class="text-muted">Sign in to book tickets and manage events</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="needs-validation" novalidate>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please enter your email.</div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-3">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-3 small">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="register.php" class="text-decoration-none text-violet ms-1 fw-semibold">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
