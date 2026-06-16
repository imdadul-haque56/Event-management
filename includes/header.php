<?php
require_once __DIR__ . '/auth.php';
$base_url = get_base_url();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage and book event tickets easily on our modern Event Booking and Management Platform.">
    <title><?php echo isset($page_title) ? $page_title . " | EventMan" : "EventMan | Event Booking & Management Platform"; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $base_url; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_url; ?>index.php">
                <i class="fa-solid fa-calendar-days me-2 text-violet"></i>
                <span>EVENT<span class="">MAN</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'events.php' || $current_page == 'event-details.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'services.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>contact.php">Contact</a>
                    </li>

                    <li class="nav-item mx-2 d-none d-lg-block">
                        <span class="text-muted">|</span>
                    </li>

                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="btn btn-outline-secondary btn-sm me-2 py-1 px-3" href="<?php echo $base_url; ?>admin/dashboard.php">
                                    <i class="fa-solid fa-gauge me-1"></i> Admin Portal
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="btn btn-outline-secondary btn-sm me-2 py-1 px-3" href="<?php echo $base_url; ?>user/dashboard.php">
                                    <i class="fa-solid fa-user me-1"></i> My Bookings
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item mt-2 mt-lg-0">
                            <a class="btn btn-premium btn-sm py-1 px-3" href="<?php echo $base_url; ?>logout.php">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mt-2 mt-lg-0">
                            <a class="nav-link <?php echo ($current_page == 'login.php') ? 'active' : ''; ?> me-2" href="<?php echo $base_url; ?>login.php">Login</a>
                        </li>
                        <li class="nav-item mt-2 mt-lg-0">
                            <a class="btn btn-premium btn-sm py-1 px-3" href="<?php echo $base_url; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content wrapper (closed in footer) -->
    <main class="py-4">
