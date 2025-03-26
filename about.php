<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="text-center mb-4">About Ram Janmabhoomi Temple</h1>
            
            <!-- Introduction Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="h4 text-danger mb-3">Introduction</h2>
                    <p>The Ram Janmabhoomi Temple in Ayodhya is a historic and sacred temple dedicated to Lord Ram. Located in Ayodhya, Uttar Pradesh, it marks the birthplace of Lord Ram, one of the most revered deities in Hinduism.</p>
                    <p>The temple's construction represents the culmination of centuries of devotion and marks a significant milestone in Indian cultural and spiritual heritage.</p>
                </div>
            </div>

            <!-- Temple Architecture -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="h4 text-danger mb-3">Temple Architecture</h2>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <img src="/ram/assets/temple.jpg" alt="Temple Architecture" class="img-fluid rounded">
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Traditional Nagara style architecture</li>
                                <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Height: 161 feet</li>
                                <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Length: 235 feet</li>
                                <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Width: 135 feet</li>
                                <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Three floors</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visiting Hours -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="h4 text-danger mb-3">Visiting Hours</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Morning</th>
                                    <th>Evening</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Monday - Sunday</td>
                                    <td>7:00 AM - 11:30 AM</td>
                                    <td>2:00 PM - 7:00 PM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add this after the Visiting Hours section -->
            <div class="visiting-hours mb-5">
                <div class="disclaimer mt-4 alert alert-info">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Disclaimer</h6>
                    <p class="mb-0">
                        Please note that the visiting hours are subject to change. For the most accurate and up-to-date schedule, kindly refer to the booking page at the time of reservation.
                    </p>
                </div>
            </div>

            <!-- Guidelines for Visitors -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="h4 text-danger mb-3">Guidelines for Visitors</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="h5 mb-3">Dress Code</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-circle-check text-success me-2"></i>Traditional/Modest attire</li>
                                <li class="mb-2"><i class="fas fa-circle-check text-success me-2"></i>Clean clothes</li>
                                <li class="mb-2"><i class="fas fa-circle-check text-success me-2"></i>No sleeveless tops</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h5 mb-3">Prohibited Items</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-circle-xmark text-danger me-2"></i>Cameras</li>
                                <li class="mb-2"><i class="fas fa-circle-xmark text-danger me-2"></i>Food items</li>
                                <li class="mb-2"><i class="fas fa-circle-xmark text-danger me-2"></i>Electronic devices</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Facilities -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="h4 text-danger mb-3">Special Facilities</h2>
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-wheelchair fa-2x text-danger mb-2"></i>
                            <h3 class="h6">Wheelchair Access</h3>
                            <p class="small text-muted">Available for elderly and disabled visitors</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-hands-helping fa-2x text-danger mb-2"></i>
                            <h3 class="h6">Assistant Support</h3>
                            <p class="small text-muted">Helpers available on request</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-utensils fa-2x text-danger mb-2"></i>
                            <h3 class="h6">Prasad Distribution</h3>
                            <p class="small text-muted">Available during darshan hours</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center mt-5">
                <p class="lead mb-4">Experience the divine presence of Lord Ram at this sacred temple</p>
                <a href="/ram/booking.php" class="btn btn-danger btn-lg">
                    <i class="fas fa-ticket-alt me-2"></i>Book Your Darshan Now
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 