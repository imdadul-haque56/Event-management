    </main>

    <!-- Footer -->
    <footer class="footer py-5 mt-auto">
        <div class="container">
            <div class="row g-4">
                <!-- Info Column -->
                <div class="col-lg-4 col-md-6">
                    <h5 class=" mb-3 fw-bold">
                        <i class="fa-solid fa-calendar-days me-2 text-primary"></i> EVENTMAN
                    </h5>
                    <p class="text-muted small">
                        EventMan is a modern booking and management platform designed to help you book tickets for your favorite concerts, webinars, matches, and local events seamlessly.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-muted"><i class="fa-brands fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class=" mb-3 fw-bold">Quick Links</h6>
                    <ul class="list-unstyled text-muted small d-grid gap-2">
                        <li><a href="<?php echo $base_url; ?>index.php" class="text-decoration-none text-muted">Home</a></li>
                        <li><a href="<?php echo $base_url; ?>events.php" class="text-decoration-none text-muted">Events Catalog</a></li>
                        <li><a href="<?php echo $base_url; ?>services.php" class="text-decoration-none text-muted">Our Services</a></li>
                        <li><a href="<?php echo $base_url; ?>about.php" class="text-decoration-none text-muted">About Us</a></li>
                        <li><a href="<?php echo $base_url; ?>contact.php" class="text-decoration-none text-muted">Contact Support</a></li>
                    </ul>
                </div>

                <!-- Features Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class=" mb-3 fw-bold">Categories</h6>
                    <ul class="list-unstyled text-muted small d-grid gap-2">
                        <li><a href="<?php echo $base_url; ?>events.php" class="text-decoration-none text-muted">Concerts</a></li>
                        <li><a href="<?php echo $base_url; ?>events.php" class="text-decoration-none text-muted">Tech Conferences</a></li>
                        <li><a href="<?php echo $base_url; ?>events.php" class="text-decoration-none text-muted">Sports Matches</a></li>
                        <li><a href="<?php echo $base_url; ?>events.php" class="text-decoration-none text-muted">Art Galleries</a></li>
                    </ul>
                </div>

                <!-- Newsletter/Contact info -->
                <div class="col-lg-4 col-md-6">
                    <h6 class=" mb-3 fw-bold">Help & Support</h6>
                    <p class="text-muted small mb-2"><i class="fa-solid fa-envelope me-2 text-primary"></i> support@eventman.local</p>
                    <p class="text-muted small mb-2"><i class="fa-solid fa-phone me-2 text-primary"></i> +91 98765 43210</p>
                    <p class="text-muted small"><i class="fa-solid fa-location-dot me-2 text-primary"></i> Astra Towers, Action Area IIC, NewTown, West Bengal</p>
                </div>
            </div>
            
            <hr class="my-4 border-light opacity-25">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 small text-muted">&copy; <?php echo date('Y'); ?> EventMan. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <ul class="list-inline mb-0 small">
                        <li class="list-inline-item"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom validation and helper scripts -->
    <script src="<?php echo $base_url; ?>assets/js/validation.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
</body>
</html>
