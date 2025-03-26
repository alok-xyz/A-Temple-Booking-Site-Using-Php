<?php
require_once 'config/config.php';

if(!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/ram/booking.php';
    header('Location: /ram/login.php');
    exit();
}

// Fetch active tours
$tours_sql = "SELECT * FROM darshan_tours WHERE status = 'active'";
$tours_result = $conn->query($tours_sql);

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Book Your Darshan</h2>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form id="bookingForm" method="POST">
                        <!-- Step 1: Tour Selection -->
                        <div class="mb-4">
                            <h5>Step 1: Select Darshan Type</h5>
                            <select class="form-select" id="tour_id" name="tour_id" required>
                                <option value="">Select Darshan Type</option>
                                <?php while($tour = $tours_result->fetch_assoc()): ?>
                                    <?php 
                                    // Ensure timetable is valid JSON
                                    $timetable = $tour['timetable'];
                                    if (!empty($timetable)) {
                                        // If it's already a JSON string, use it as is
                                        if (is_string($timetable)) {
                                            json_decode($timetable);
                                            if (json_last_error() !== JSON_ERROR_NONE) {
                                                $timetable = '[]';
                                            }
                                        } else {
                                            // If it's an array/object, encode it
                                            $timetable = json_encode($timetable);
                                        }
                                    } else {
                                        $timetable = '[]';
                                    }
                                    ?>
                                    <option value="<?php echo $tour['id']; ?>" 
                                            data-price="<?php echo $tour['base_price']; ?>"
                                            data-wheelchair="<?php echo $tour['wheelchair_price']; ?>"
                                            data-assistant="<?php echo $tour['assistant_price']; ?>"
                                            data-food="<?php echo $tour['food_price']; ?>"
                                            data-timetable='<?php echo htmlspecialchars($timetable, ENT_QUOTES); ?>'>
                                        <?php echo $tour['name']; ?> - Base Price: ₹<?php echo $tour['base_price']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- After the tour selection dropdown, add this div -->
                        <div class="mb-3" id="tourTimetable" style="display: none;">
                            <h6 class="mb-2">Darshan Time Schedule</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Time Slot</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody id="timetableBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add this after Step 1: Select Darshan Type -->
                        <div class="mb-4" id="timeSlotSelection" style="display: none;">
                            <h5>Step 2: Select Time Slot</h5>
                            <select class="form-select" id="time_slot" name="time_slot" required disabled>
                                <option value="">Select time slot</option>
                            </select>
                        </div>

                        <!-- Step 3: Date Selection -->
                        <div class="mb-4">
                            <h5>Step 3: Select Date</h5>
                            <select class="form-select" id="booking_date" name="booking_date" required disabled>
                                <option value="">First select darshan type</option>
                            </select>
                        </div>

                        <!-- Step 4: Number of People -->
                        <div class="mb-4">
                            <h5>Step 4: Number of People</h5>
                            <select class="form-select" id="total_people" name="total_people" required disabled>
                                <option value="">Select number of people</option>
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Person<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Step 5: Visitor Forms Container -->
                        <div id="visitorForms">
                            <!-- Visitor forms will be dynamically added here -->
                        </div>

                        <!-- Price Summary -->
                        <div class="card mb-4" id="priceSummary" style="display: none;">
                            <div class="card-header">
                                <h5 class="mb-0">Price Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-8">Base Price (₹<span id="basePrice">0</span> × <span id="totalPeople">0</span>)</div>
                                    <div class="col-4 text-end">₹<span id="totalBasePrice">0.00</span></div>
                                </div>
                                <div id="additionalCharges"></div>
                                <hr>
                                <div class="row fw-bold">
                                    <div class="col-8">Total Amount</div>
                                    <div class="col-4 text-end">₹<span id="totalAmount">0.00</span></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" disabled>Proceed to Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Tour selection change handler
    $('#tour_id').change(function() {
        const tourId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        
        // Reset dependent fields
        $('#time_slot').prop('disabled', true).html('<option value="">Select time slot</option>');
        $('#booking_date').prop('disabled', true).html('<option value="">First select time slot</option>');
        $('#total_people').prop('disabled', true).val('');
        $('#visitorForms').empty();
        $('#timeSlotSelection').hide();
        $('#tourTimetable').hide();
        
        if(tourId) {
            try {
                let timetableData = selectedOption.data('timetable');
                
                // If timetableData is already an object, convert it to string
                if (typeof timetableData === 'object' && timetableData !== null) {
                    timetableData = JSON.stringify(timetableData);
                }
                
                // Parse the timetable data
                const timetable = JSON.parse(timetableData || '[]');
                console.log('Parsed timetable:', timetable); // For debugging
                
                if(timetable && timetable.length > 0) {
                    // Populate time slot dropdown
                    timetable.forEach((slot, index) => {
                        const timeText = `${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}`;
                        const description = slot.description ? ` (${slot.description})` : '';
                        $('#time_slot').append(`
                            <option value="${index}" 
                                    data-start="${slot.start_time}" 
                                    data-end="${slot.end_time}"
                                    data-description="${slot.description || ''}">
                                ${timeText}${description}
                            </option>
                        `);
                    });
                    
                    // Show and enable time slot selection
                    $('#timeSlotSelection').show();
                    $('#time_slot').prop('disabled', false);
                    
                    // Also show timetable for reference
                    const timetableBody = $('#timetableBody');
                    timetableBody.empty();
                    timetable.forEach(slot => {
                        timetableBody.append(`
                            <tr>
                                <td>${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}</td>
                                <td>${slot.description || ''}</td>
                            </tr>
                        `);
                    });
                    $('#tourTimetable').slideDown();
                }
            } catch(e) {
                console.error('Error parsing timetable:', e);
                console.log('Raw timetable data:', selectedOption.data('timetable'));
            }
        }
    });

    // Add time slot change handler
    $('#time_slot').change(function() {
        const timeSlotSelected = $(this).val() !== '';
        $('#booking_date').prop('disabled', !timeSlotSelected);
        
        if(timeSlotSelected) {
            const tourId = $('#tour_id').val();
            $('#booking_date')
                .prop('disabled', true)
                .html('<option value="">Loading dates...</option>');
            
            $.ajax({
                url: '/ram/get_available_dates.php',
                method: 'POST',
                data: { 
                    tour_id: tourId,
                    time_slot: $(this).val()
                },
                dataType: 'json',
                success: function(response) {
                    $('#booking_date').prop('disabled', false).html('<option value="">Select date</option>');
                    
                    if(response.status === 'success' && response.dates.length > 0) {
                        response.dates.forEach(function(date) {
                            $('#booking_date').append(
                                `<option value="${date.date}">
                                    ${date.formatted_date} (${date.slots} slots available)
                                </option>`
                            );
                        });
                    } else {
                        $('#booking_date')
                            .html('<option value="">No dates available currently</option>')
                            .prop('disabled', true);
                    }
                },
                error: function() {
                    $('#booking_date')
                        .html('<option value="">Error loading dates</option>')
                        .prop('disabled', true);
                }
            });
        } else {
            $('#booking_date')
                .prop('disabled', true)
                .html('<option value="">First select time slot</option>');
        }
    });

    // Date selection change handler
    $('#booking_date').change(function() {
        $('#total_people').prop('disabled', !$(this).val());
        if(!$(this).val()) {
            $('#total_people').val('');
            $('#visitorForms').empty();
        }
        updatePriceSummary();
    });

    // Number of people change handler
    $('#total_people').change(function() {
        const count = parseInt($(this).val()) || 0;
        const container = $('#visitorForms');
        container.empty();

        if(count > 0) {
            container.append('<h5 class="mb-4">Step 5: Visitor Details</h5>');
            
            for(let i = 0; i < count; i++) {
                container.append(`
                    <div class="card mb-4 visitor-form">
                        <div class="card-header">
                            <h6 class="mb-0">Visitor ${i + 1}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name*</label>
                                    <input type="text" class="form-control" name="visitors[${i}][name]" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Age*</label>
                                    <input type="number" class="form-control" name="visitors[${i}][age]" min="1" max="120" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender*</label>
                                    <select class="form-select" name="visitors[${i}][gender]" required>
                                        <option value="">Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Aadhar Number*</label>
                                    <input type="text" class="form-control" name="visitors[${i}][aadhar]" 
                                           pattern="[0-9]{12}" maxlength="12" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" 
                                               name="visitors[${i}][disability]" id="disability_${i}">
                                        <label class="form-check-label" for="disability_${i}">Person with disability?</label>
                                    </div>
                                    
                                    <div class="disability-options-${i}" style="display: none; padding-left: 20px;">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input price-factor" 
                                                   name="visitors[${i}][wheelchair]" id="wheelchair_${i}">
                                            <label class="form-check-label" for="wheelchair_${i}">
                                                Need wheelchair? (₹<span class="wheelchair-price">0</span>)
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input price-factor" 
                                                   name="visitors[${i}][assistant]" id="assistant_${i}">
                                            <label class="form-check-label" for="assistant_${i}">
                                                Need assistant? (₹<span class="assistant-price">0</span>)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input price-factor" 
                                               name="visitors[${i}][food]" id="food_${i}">
                                        <label class="form-check-label" for="food_${i}">
                                            Need food? (Veg Thali - ₹<span class="food-price">0</span>)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                // Add event listener for disability checkbox
                $(`#disability_${i}`).change(function() {
                    $(`.disability-options-${i}`).slideToggle(this.checked);
                    if (!this.checked) {
                        // Uncheck wheelchair and assistant when disability is unchecked
                        $(`#wheelchair_${i}, #assistant_${i}`).prop('checked', false);
                    }
                    updatePriceSummary();
                });
            }
            
            updatePriceSummary();
            $('button[type="submit"]').prop('disabled', false);
        } else {
            resetForm();
        }
    });

    // Form submission handler
    $('#bookingForm').submit(function(e) {
        e.preventDefault();
        
        // Get selected time slot details
        const selectedTimeSlot = $('#time_slot option:selected');
        const timeSlotData = selectedTimeSlot.data();
        const timeSlotText = `${timeSlotData.start} - ${timeSlotData.end}`;
        
        // Collect all form data
        const formData = {
            tour_id: $('#tour_id').val(),
            tour_name: $('#tour_id option:selected').text().split(' - ')[0],
            booking_date: $('#booking_date').val(),
            time_slot: `${timeSlotData.start}-${timeSlotData.end}`, // Format: "09:00-10:00"
            time_slot_text: timeSlotText, // Format: "09:00 - 10:00"
            total_people: parseInt($('#total_people').val()),
            base_price: parseFloat($('#tour_id option:selected').data('price')),
            wheelchair_price: parseFloat($('#tour_id option:selected').data('wheelchair')),
            assistant_price: parseFloat($('#tour_id option:selected').data('assistant')),
            food_price: parseFloat($('#tour_id option:selected').data('food')),
            total_amount: parseFloat($('#totalAmount').text()),
            visitors: []
        };

        // Collect visitor data
        for(let i = 0; i < formData.total_people; i++) {
            formData.visitors.push({
                name: $(`input[name="visitors[${i}][name]"]`).val(),
                age: parseInt($(`input[name="visitors[${i}][age]"]`).val()),
                gender: $(`select[name="visitors[${i}][gender]"]`).val(),
                aadhar: $(`input[name="visitors[${i}][aadhar]"]`).val(),
                disability: $(`input[name="visitors[${i}][disability]"]`).is(':checked') ? 1 : 0,
                wheelchair: $(`input[name="visitors[${i}][wheelchair]"]`).is(':checked') ? 1 : 0,
                assistant: $(`input[name="visitors[${i}][assistant]"]`).is(':checked') ? 1 : 0,
                food: $(`input[name="visitors[${i}][food]"]`).is(':checked') ? 1 : 0
            });
        }

        // Store booking data in session and redirect to payment page
        $.ajax({
            url: '/ram/store_booking_session.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    window.location.href = '/ram/payment.php';
                } else {
                    alert(response.message || 'Failed to process booking. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Failed to process booking. Please try again.');
            }
        });
    });

    // Price summary update function
    function updatePriceSummary() {
        const tour = $('#tour_id option:selected');
        const count = parseInt($('#total_people').val()) || 0;

        if(tour.length && count > 0) {
            const basePrice = parseFloat(tour.data('price'));
            const wheelchairPrice = parseFloat(tour.data('wheelchair'));
            const assistantPrice = parseFloat(tour.data('assistant'));
            const foodPrice = parseFloat(tour.data('food'));

            let totalBase = basePrice * count;
            let total = totalBase;

            $('#basePrice').text(basePrice);
            $('#totalPeople').text(count);
            $('#totalBasePrice').text(totalBase.toFixed(2));

            $('.wheelchair-price').text(wheelchairPrice);
            $('.assistant-price').text(assistantPrice);
            $('.food-price').text(foodPrice);

            let additionalChargesHtml = '';

            $('input[name$="[wheelchair]"]:checked').each(function() {
                total += wheelchairPrice;
                additionalChargesHtml += `
                    <div class="row">
                        <div class="col-8">Wheelchair</div>
                        <div class="col-4 text-end">₹${wheelchairPrice.toFixed(2)}</div>
                    </div>`;
            });

            $('input[name$="[assistant]"]:checked').each(function() {
                total += assistantPrice;
                additionalChargesHtml += `
                    <div class="row">
                        <div class="col-8">Assistant</div>
                        <div class="col-4 text-end">₹${assistantPrice.toFixed(2)}</div>
                    </div>`;
            });

            $('input[name$="[food]"]:checked').each(function() {
                total += foodPrice;
                additionalChargesHtml += `
                    <div class="row">
                        <div class="col-8">Food (Veg Thali)</div>
                        <div class="col-4 text-end">₹${foodPrice.toFixed(2)}</div>
                    </div>`;
            });

            $('#additionalCharges').html(additionalChargesHtml);
            $('#totalAmount').text(total.toFixed(2));
            $('#priceSummary').show();
        } else {
            $('#priceSummary').hide();
        }
    }

    // Form reset function
    function resetForm() {
        $('#total_people').prop('disabled', true).val('');
        $('#visitorForms').empty();
        $('#priceSummary').hide();
        $('button[type="submit"]').prop('disabled', true);
    }

    // Update price when any checkbox changes
    $(document).on('change', '.price-factor', updatePriceSummary);

    // Time formatting function
    function formatTime(time) {
        if(!time) return '';
        try {
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        } catch(e) {
            return time;
        }
    }
});
</script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', function(e) {
    e.preventDefault();
    
    var options = {
        key: '<?php echo RAZORPAY_KEY_ID; ?>',
        amount: <?php echo round($total_amount * 100); ?>,
        currency: 'INR',
        name: 'Shree Ram Janmabhoomi',
        description: 'Darshan Booking',
        image: '/ram/assets/logo.png',
        handler: function(response) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/ram/verify_payment.php';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'razorpay_payment_id';
            input.value = response.razorpay_payment_id;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        },
        prefill: {
            name: '<?php echo str_replace("'", "\\'", $_SESSION['user_name']); ?>',
            email: '<?php echo str_replace("'", "\\'", $_SESSION['user_email']); ?>',
            contact: '<?php echo str_replace("'", "\\'", $_SESSION['user_phone']); ?>'
        },
        theme: {
            color: '#F37254'
        }
    };

    try {
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            alert('Payment failed. Please try again.');
        });
        rzp1.open();
    } catch(err) {
        console.error('Razorpay error:', err);
        alert('Unable to initialize payment. Please try again.');
    }
});
</script>

<style>
#tourTimetable {
    margin: 15px 0;
    border-left: 4px solid #ff7f27;
}
#tourTimetable h6 {
    color: #444;
    margin-left: 10px;
}
#tourTimetable .table {
    margin-bottom: 0;
}
#tourTimetable .table td, 
#tourTimetable .table th {
    padding: 12px 15px;
    vertical-align: middle;
}
#tourTimetable .table thead th {
    background-color: #fff5e6;
    color: #ff7f27;
    font-weight: 600;
    border-bottom: 2px solid #ffdbb7;
}
#tourTimetable .table td {
    color: #555;
}

#timeSlotSelection {
    margin-top: 1rem;
    margin-bottom: 2rem;
}

#time_slot {
    border-color: #ddd;
    background-color: #fff;
}

#time_slot:disabled {
    background-color: #f8f9fa;
}

#time_slot option {
    padding: 8px;
}
</style>

<?php include 'includes/footer.php'; ?> 