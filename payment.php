<?php
require_once 'config/config.php';
require 'vendor/autoload.php';
use Razorpay\Api\Api;

if(!isLoggedIn() || !isset($_SESSION['booking_data'])) {
    header('Location: /ram/booking.php');
    exit();
}

$booking_data = $_SESSION['booking_data'];
$razorpayOrderId = null;
$initError = null;

// Initialize Razorpay Order
try {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $orderData = [
        'receipt'         => 'rcpt_' . time(),
        'amount'          => (int)($booking_data['total_amount'] * 100), // Convert to paise
        'currency'        => 'INR',
        'payment_capture' => 1 // auto capture
    ];

    $razorpayOrder = $api->order->create($orderData);
    $razorpayOrderId = $razorpayOrder['id'];
    $_SESSION['razorpay_order_id'] = $razorpayOrderId;

} catch (Exception $e) {
    $initError = $e->getMessage();
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Booking Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Darshan Type:</strong>
                            <p><?php echo $booking_data['tour_name']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Time Slot:</strong>
                            <p><?php echo $booking_data['time_slot_text']; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong>
                            <p><?php echo date('D, M j, Y', strtotime($booking_data['booking_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Total People:</strong>
                            <p><?php echo $booking_data['total_people']; ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Visitor Details</h5>
                        <?php foreach($booking_data['visitors'] as $index => $visitor): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6>Visitor <?php echo $index + 1; ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Name:</strong> <?php echo $visitor['name']; ?></p>
                                            <p><strong>Age:</strong> <?php echo $visitor['age']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Gender:</strong> <?php echo ucfirst($visitor['gender']); ?></p>
                                            <p><strong>Aadhar:</strong> <?php echo $visitor['aadhar']; ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <?php if($visitor['disability']): ?>
                                            <span class="badge bg-info me-2">Person with Disability</span>
                                        <?php endif; ?>
                                        <?php if($visitor['wheelchair']): ?>
                                            <span class="badge bg-secondary me-2">Wheelchair Required</span>
                                        <?php endif; ?>
                                        <?php if($visitor['assistant']): ?>
                                            <span class="badge bg-warning me-2">Assistant Required</span>
                                        <?php endif; ?>
                                        <?php if($visitor['food']): ?>
                                            <span class="badge bg-success">Food Included</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5>Price Details</h5>
                            <!-- Base Price -->
                            <div class="row mb-2">
                                <div class="col-8">Base Price (₹<?php echo number_format($booking_data['base_price'], 2); ?> × <?php echo $booking_data['total_people']; ?>)</div>
                                <div class="col-4 text-end">₹<?php echo number_format($booking_data['base_price'] * $booking_data['total_people'], 2); ?></div>
                            </div>

                            <!-- Additional Services -->
                            <?php
                            $wheelchair_count = 0;
                            $assistant_count = 0;
                            $food_count = 0;
                            $wheelchair_price = $booking_data['wheelchair_price'] ?? 0;
                            $assistant_price = $booking_data['assistant_price'] ?? 0;
                            $food_price = $booking_data['food_price'] ?? 0;
                            
                            foreach($booking_data['visitors'] as $visitor) {
                                if($visitor['wheelchair']) $wheelchair_count++;
                                if($visitor['assistant']) $assistant_count++;
                                if($visitor['food']) $food_count++;
                            }
                            ?>

                            <?php if($wheelchair_count > 0): ?>
                            <div class="row mb-2">
                                <div class="col-8">Wheelchair (₹<?php echo number_format($wheelchair_price, 2); ?> × <?php echo $wheelchair_count; ?>)</div>
                                <div class="col-4 text-end">₹<?php echo number_format($wheelchair_price * $wheelchair_count, 2); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if($assistant_count > 0): ?>
                            <div class="row mb-2">
                                <div class="col-8">Assistant (₹<?php echo number_format($assistant_price, 2); ?> × <?php echo $assistant_count; ?>)</div>
                                <div class="col-4 text-end">₹<?php echo number_format($assistant_price * $assistant_count, 2); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if($food_count > 0): ?>
                            <div class="row mb-2">
                                <div class="col-8">Food - Veg Thali (₹<?php echo number_format($food_price, 2); ?> × <?php echo $food_count; ?>)</div>
                                <div class="col-4 text-end">₹<?php echo number_format($food_price * $food_count, 2); ?></div>
                            </div>
                            <?php endif; ?>

                            <!-- Total Amount -->
                            <div class="row fw-bold pt-2 border-top">
                                <div class="col-8">Total Amount</div>
                                <div class="col-4 text-end">₹<?php echo number_format($booking_data['total_amount'], 2); ?></div>
                            </div>

                            <!-- Payment Info -->
                            <div class="alert alert-info mt-3 mb-0">
                                <small class="d-block"><i class="fas fa-shield-alt"></i> Secure payment powered by Razorpay</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button id="payButton" class="btn btn-primary btn-lg">Pay Now</button>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
<?php if (!$initError): ?>
document.getElementById('payButton').addEventListener('click', function(e) {
    e.preventDefault();
    
    var options = {
        key: '<?php echo RAZORPAY_KEY_ID; ?>',
        amount: <?php echo (int)($booking_data['total_amount'] * 100); ?>,
        currency: 'INR',
        name: 'Shree Ram Janmabhoomi',
        description: 'Darshan Booking',
        image: '/ram/assets/images/logo.png',
        order_id: '<?php echo $razorpayOrderId; ?>',
        handler: function (response) {
            window.location.href = '/ram/verify_payment.php?' + 
                'razorpay_payment_id=' + response.razorpay_payment_id + 
                '&razorpay_order_id=' + response.razorpay_order_id + 
                '&razorpay_signature=' + response.razorpay_signature;
        },
        prefill: {
            name: '<?php echo addslashes(htmlspecialchars($_SESSION['user_name'] ?? '')); ?>',
            email: '<?php echo addslashes(htmlspecialchars($_SESSION['user_email'] ?? '')); ?>',
            contact: '<?php echo addslashes(htmlspecialchars($_SESSION['user_phone'] ?? '')); ?>'
        },
        theme: {
            color: '#F37254'
        }
    };
    
    try {
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response) {
            alert('Payment failed. Please try again.\nError: ' + response.error.description);
        });
        rzp1.open();
    } catch(err) {
        console.error('Razorpay error:', err);
        alert('Unable to initialize payment. Please try again.');
    }
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?> 