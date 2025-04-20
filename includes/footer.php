    </div>
    
    <footer class="footer mt-auto py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo/logo.svg" alt="Trip Planner Logo" width="50" height="50" class="me-2">
                        <h5 class="mb-0 text-white">Trip Planner</h5>
                    </div>
                    <p class="text-light small">TURN YOUR TRAVEL DREAMS INTO PLANS</p>
                    <p class="text-light small mb-0">Your ultimate companion for planning memorable trips and adventures around the world.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/index.php" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/pages/about.php" class="text-light text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/pages/contact.php" class="text-light text-decoration-none">Contact</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="text-light text-decoration-none">Dashboard</a></li>
                        <?php else: ?>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/pages/register.php" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Contact Us</h5>
                    <ul class="list-unstyled text-light">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Cagayan de Oro, CDO</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> ciervo.jenojohn@gmail.com</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> 09543993696</li>
                    </ul>
                    <div class="mt-3">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> Trip Planner. All rights reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="text-muted small">Developed by: Jeno John Ciervo, Byron James Abarabar, Luigi Balibay Sabellina</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>

    <?php
    require_once __DIR__ . '/alerts.php';
    showAlertIfExists();
    ?>
</body>
</html>
