<?php
$page_title = "Contact Support";
require_once 'includes/auth.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all form fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Simple success simulation (in production this would email or insert to a contact_logs table)
        $success = "Thank you, " . htmlspecialchars($name) . "! Your message has been received. Our team will contact you shortly.";
        $name = $email = $subject = $message = '';
    }
}

require_once 'includes/header.php';
?>

<div class="container my-5 ">
    <div class="glass-panel mb-5 text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-bold">SUPPORT</span>
        <h1 class="fw-bold mb-3">Get in Touch</h1>
        <p class="lead text-muted mx-auto" style="max-width: 800px;">
            Have questions about booking event tickets, hosting an event, or managing your dashboard? Fill out the form below, and we will get back to you within 24 hours.
        </p>
    </div>

    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-4">
            <div class="d-grid gap-4">
                <div class="glass-panel">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-phone text-violet me-2"></i> Call Us</h5>
                    <p class="text-muted small mb-1">General Inquiries: +91 98765 43210</p>
                    <p class="text-muted small">Organizer Support: +91 98765 43210</p>
                </div>

                <div class="glass-panel">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-envelope text-violet me-2"></i> Email Support</h5>
                    <p class="text-muted small mb-1">General Help: info@eventman.local</p>
                    <p class="text-muted small">Technical Issues: support@eventman.local</p>
                </div>

                <div class="glass-panel">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-location-dot text-violet me-2"></i> Head Office</h5>
                    <p class="text-muted small mb-0">Astra Towers, Action Area IIC,<br>NewTown, West Bengal 700161</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="glass-panel">
                <h3 class="fw-bold mb-4"><i class="fa-regular fa-paper-plane text-violet me-2"></i> Send us a Message</h3>

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

                <form action="contact.php" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3 mb-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                        <div class="invalid-feedback">Please enter a subject.</div>
                    </div>

                    <!-- Message -->
                    <div class="mb-4">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        <div class="invalid-feedback">Please write your message text.</div>
                    </div>

                    <button type="submit" class="btn btn-premium px-5 py-3">
                        <i class="fa-solid fa-paper-plane me-2"></i> Submit Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>