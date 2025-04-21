<?php
require_once '../includes/config.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        $success = true;
        
        if (!isset($_SESSION['contact_messages'])) {
            $_SESSION['contact_messages'] = [];
        }
        
        $_SESSION['contact_messages'][] = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'date' => date('Y-m-d H:i:s')
        ];
        
        $name = $email = $subject = $message = '';
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
            <p class="lead text-muted">
                Have questions or feedback? We'd love to hear from you!
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-5 bg-primary text-white">
                        <div class="p-5 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h2 class="mb-4">Get In Touch</h2>
                                <p>
                                    We're here to help with any questions about our platform or your travel plans.
                                </p>
                            </div>
                            
                            <div>
                                <div class="mb-4">
                                    <h3 class="h5 mb-3">Contact Information</h3>
                                    <div class="d-flex mb-3">
                                        <i class="fas fa-map-marker-alt mt-1 me-3"></i>
                                        <p class="mb-0">Cagayan de Oro, CDO</p>
                                    </div>
                                    <div class="d-flex mb-3">
                                        <i class="fas fa-envelope mt-1 me-3"></i>
                                        <p class="mb-0">ciervo.jenojohn@gmail.com</p>
                                    </div>
                                    <div class="d-flex">
                                        <i class="fas fa-phone-alt mt-1 me-3"></i>
                                        <p class="mb-0">09543993696</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 class="h5 mb-3">Follow Us</h3>
                                    <div class="social-links">
                                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                                        <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="p-5">
                            <h2 class="mb-4">Send Us a Message</h2>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Thank you for your message! We'll get back to you as soon as possible.
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Frequently Asked Questions</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3">
                            <h3 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    How do I create a new trip?
                                </button>
                            </h3>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    To create a new trip, log in to your account and navigate to the Dashboard. 
                                    Click on the "Create New Trip" button, fill in the details like destination, 
                                    dates, and activities, then save your trip. It will appear in your trip list 
                                    where you can view and edit it anytime.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 mb-3">
                            <h3 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Can I share my trip plans with others?
                                </button>
                            </h3>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Currently, trip sharing is being developed and will be available in a future update. 
                                    Soon, you'll be able to share your trips with friends and family via email or a 
                                    shareable link, allowing them to view your itinerary and even collaborate on planning.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 mb-3">
                            <h3 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Is my personal information secure?
                                </button>
                            </h3>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we take data security very seriously. All personal information is encrypted 
                                    and stored securely. We never share your data with third parties without your 
                                    explicit consent. Your passwords are hashed, and we implement industry-standard 
                                    security measures to protect your account.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0">
                            <h3 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    How can I get help with planning my trip?
                                </button>
                            </h3>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer several resources to help with trip planning. You can browse hotels and 
                                    tourist sites directly in our platform, check out our blog for travel tips, and 
                                    use our recommendation engine for personalized suggestions. If you need more 
                                    assistance, feel free to contact our support team through this form.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button:not(.collapsed) {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(13, 110, 253, 0.25);
}
</style>

<?php include '../includes/footer.php'; ?>
