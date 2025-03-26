<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

// Make sure no output before PDF generation
if(!isLoggedIn()) {
    header('Location: /ram/login.php');
    exit();
}

// Accept both 'id' and 'booking_id' parameters for compatibility
$booking_id = $_GET['booking_id'] ?? $_GET['id'] ?? 0;

// Fetch booking details
$sql = "SELECT b.*, dt.name as tour_name, 
        u.name as user_name, u.email as user_email, u.phone as user_phone,
        b.time_slot, b.time_slot_text
        FROM bookings b 
        JOIN darshan_tours dt ON b.tour_id = dt.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if(!$booking) {
    header('Location: /ram/dashboard.php');
    exit();
}

// Fetch visitors - updated to match your database structure
$visitors_sql = "SELECT * FROM visitors WHERE booking_id = ?";
$visitors_stmt = $conn->prepare($visitors_sql);
$visitors_stmt->bind_param("i", $booking_id);
$visitors_stmt->execute();
$visitors = $visitors_stmt->get_result();

// First, fetch the total number of visitors
$visitors_count_sql = "SELECT COUNT(*) as total FROM visitors WHERE booking_id = ?";
$visitors_count_stmt = $conn->prepare($visitors_count_sql);
$visitors_count_stmt->bind_param("i", $booking_id);
$visitors_count_stmt->execute();
$visitors_count = $visitors_count_stmt->get_result()->fetch_assoc()['total'];

// Create new PDF document
$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Shree Ram Janmabhoomi');
$pdf->SetAuthor('Shree Ram Janmabhoomi');
$pdf->SetTitle('Booking Receipt #' . $booking_id);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page (removing the orange background)
$pdf->AddPage();

// Add border box at the start of each page - orange color, no rounded corners
$pdf->SetLineStyle(array('width' => 0.5, 'color' => array(255, 127, 39))); // Orange border
$pdf->Rect(10, 10, 190, $pdf->GetPageHeight()-20);

// Add decorative header border
$pdf->SetLineStyle(array('width' => 0.5, 'color' => array(255, 127, 39)));
$pdf->Line(15, 12, 195, 12);
$pdf->Line(15, 50, 195, 50);

// Add logo and organization details with enhanced styling
$pdf->Image('assets/logo.png', 15, 15, 25);
$pdf->SetTextColor(255, 127, 39); // Orange color for title
$pdf->SetFont('helvetica', 'B', 24);
$pdf->Cell(30);
$pdf->Cell(120, 10, 'Shree Ram Janmabhoomi', 0, 0, 'L');

// Add QR Code back after organization name
$style = array(
    'border' => false,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(255, 127, 39), // Orange QR code
    'bgcolor' => false
);
$pdf->write2DBarcode('Booking ID: ' . $booking['id'] . "\nPayment ID: " . $booking['payment_id'], 
    'QRCODE,L', 170, 15, 25, 25, $style);

// Organization details with improved typography
$pdf->Ln(12);
$pdf->SetTextColor(68, 68, 68); // Dark gray for text
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(30);
$pdf->Cell(0, 5, 'Ram Path, Ayodhya, Uttar Pradesh 224123', 0, 1, 'L');
$pdf->Cell(30);
$pdf->Cell(0, 5, 'Email: info@ramjanmabhoomi.com | Phone: +91-XXXXXXXXXX', 0, 1, 'L');

// Receipt Title with decorative elements
$pdf->Ln(15);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(255, 127, 39);
$pdf->Cell(0, 10, 'DARSHAN BOOKING RECEIPT', 0, 1, 'C');

// Add ornamental divider
$pdf->SetLineStyle(array('width' => 0.3, 'color' => array(255, 127, 39)));
$pdf->Line(50, $pdf->GetY(), 160, $pdf->GetY());

// Booking Information Section with enhanced styling
$pdf->Ln(10);
$pdf->SetFillColor(255, 127, 39); // Orange background for headers
$pdf->SetTextColor(255, 255, 255); // White text for headers
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, ' Booking Information', 0, 1, 'L', true);

// Content styling
$pdf->SetTextColor(68, 68, 68);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetFillColor(255, 252, 248); // Very light orange for alternating rows

// Left column
$leftWidth = 95;
$rightWidth = 85;

// Create two columns for booking details
$pdf->Cell($leftWidth, 10, 'Booking ID: #' . $booking['id'], 1, 0, 'L');
$pdf->Cell($rightWidth, 10, 'Date: ' . date('d M Y', strtotime($booking['booking_date'])), 1, 1, 'L');

$pdf->Cell($leftWidth, 10, 'Tour: ' . $booking['tour_name'], 1, 0, 'L');

// Display time slot with proper formatting
$time_display = !empty($booking['time_slot_text']) 
    ? $booking['time_slot_text'] 
    : (!empty($booking['time_slot']) 
        ? str_replace('-', ' - ', $booking['time_slot']) 
        : 'Not specified');

$pdf->Cell($rightWidth, 10, 'Time: ' . $time_display, 1, 1, 'L');

$pdf->Cell($leftWidth, 10, 'Status: ' . ucfirst($booking['status']), 1, 0, 'L');
$pdf->Cell($rightWidth, 10, 'Total Visitors: ' . $visitors_count, 1, 1, 'L');

// Payment Details Section
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Payment Details', 1, 1, 'L', true);

$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($leftWidth, 10, 'Payment ID: ' . $booking['payment_id'], 1, 0, 'L');
$pdf->Cell($rightWidth, 10, 'Amount: Rs. ' . number_format($booking['total_amount'], 2), 1, 1, 'L');

// Customer Details
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Customer Details', 1, 1, 'L', true);

$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 10, 'Name: ' . $booking['user_name'], 1, 1, 'L');
$pdf->Cell(0, 10, 'Email: ' . $booking['user_email'], 1, 1, 'L');
$pdf->Cell(0, 10, 'Phone: ' . $booking['user_phone'], 1, 1, 'L');

// Visitor Details Section with Table Structure
$pdf->Ln(5);
$pdf->SetFillColor(255, 127, 39);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, ' VISITOR DETAILS', 0, 1, 'L', true);

// Create table header
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(68, 68, 68);
$pdf->SetFont('helvetica', 'B', 9);  // Slightly smaller font for header

// Define column widths - adjusted to fit the page better
$colWidth1 = 10;  // Sl. No.
$colWidth2 = 35;  // Name
$colWidth3 = 15;  // Gender
$colWidth4 = 25;  // ID Proof Type
$colWidth5 = 25;  // ID Proof No.
$colWidth6 = 20;  // Disability
$colWidth7 = 18;  // Wheelchair
$colWidth8 = 18;  // Assistant
$colWidth9 = 15;  // Food

// Header row with adjusted cell padding and alignment
$pdf->Cell($colWidth1, 8, 'Sl. No.', 1, 0, 'C', true);
$pdf->Cell($colWidth2, 8, 'Devotee Name', 1, 0, 'C', true);
$pdf->Cell($colWidth3, 8, 'Gender', 1, 0, 'C', true);
$pdf->Cell($colWidth4, 8, 'ID Proof Type', 1, 0, 'C', true);
$pdf->Cell($colWidth5, 8, 'ID Proof No.', 1, 0, 'C', true);
$pdf->Cell($colWidth6, 8, 'Disability', 1, 0, 'C', true);
$pdf->Cell($colWidth7, 8, 'Wheel Chair', 1, 0, 'C', true);
$pdf->Cell($colWidth8, 8, 'Assistant', 1, 0, 'C', true);
$pdf->Cell($colWidth9, 8, 'Food', 1, 1, 'C', true);

// Data rows with adjusted font size
$pdf->SetFont('helvetica', '', 9);  // Smaller font for data rows
$counter = 1;
while($visitor = $visitors->fetch_assoc()) {
    $pdf->Cell($colWidth1, 8, $counter, 1, 0, 'C');
    $pdf->Cell($colWidth2, 8, ' ' . $visitor['full_name'], 1, 0, 'L');
    $pdf->Cell($colWidth3, 8, ucfirst($visitor['gender']), 1, 0, 'C');
    $pdf->Cell($colWidth4, 8, 'Aadhar Card', 1, 0, 'C');
    $masked_aadhar = 'XXXXXXXX' . substr($visitor['aadhar_number'], -4);
    $pdf->Cell($colWidth5, 8, $masked_aadhar, 1, 0, 'C');
    $pdf->Cell($colWidth6, 8, $visitor['has_disability'] ? 'Yes' : 'No', 1, 0, 'C');
    $pdf->Cell($colWidth7, 8, $visitor['needs_wheelchair'] ? 'Yes' : 'No', 1, 0, 'C');
    $pdf->Cell($colWidth8, 8, $visitor['needs_assistant'] ? 'Yes' : 'No', 1, 0, 'C');
    $pdf->Cell($colWidth9, 8, $visitor['needs_food'] ? 'Yes' : 'No', 1, 1, 'C');
    $counter++;
}

// Add decorative footer
$pdf->Ln(10);
$pdf->SetDrawColor(255, 127, 39);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Important Information with icon
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(255, 127, 39);
$pdf->Cell(0, 10, '  Important Information:', 0, 1);

$pdf->SetTextColor(68, 68, 68);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 5, '1. Please arrive 30 minutes before your scheduled darshan time.
2. Bring this receipt and valid government ID proof during your visit.
3. Outside food and electronic devices are not permitted in the temple premises.
4. Dress code: Traditional/Modest Indian attire is recommended.
5. For any queries or assistance, please contact: info@ramjanmabhoomi.com', 0, 'L');

// Add final decorative border
$pdf->SetLineStyle(array('width' => 0.5, 'color' => array(255, 127, 39)));
$pdf->Rect(10, 10, 190, $pdf->GetPageHeight()-20);

// Output PDF
$pdf->Output('Booking_Receipt_' . $booking_id . '.pdf', 'I'); 