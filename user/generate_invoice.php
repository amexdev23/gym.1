<?php
require '../config/config.php';
require '../fpdf/fpdf.php';

session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: index.php');
    exit;
}

$paymentId = $_GET['id'] ?? 0;

// Get payment details
$stmt = $pdo->prepare("SELECT p.*, m.full_name, m.email 
                      FROM payments p
                      JOIN members m ON p.member_id = m.id
                      WHERE p.id = ? AND p.member_id = ?");
$stmt->execute([$paymentId, $_SESSION['member_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    die('Invalid payment ID');
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'FitTrack Invoice',0,1,'C');
$pdf->Ln(10);

// Member Info
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Member: '.$payment['full_name'],0,1);
$pdf->Cell(0,10,'Email: '.$payment['email'],0,1);
$pdf->Cell(0,10,'Invoice #: '.$payment['id'],0,1);
$pdf->Cell(0,10,'Date: '.date('F j, Y', strtotime($payment['date_paid'])),0,1);
$pdf->Ln(10);

// Payment Details
$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,10,'Description',1,0);
$pdf->Cell(40,10,'Amount',1,0,'R');
$pdf->Cell(40,10,'Method',1,1,'C');

$pdf->SetFont('Arial','',12);
$description = $payment['payment_type'] === 'full' ? 'Membership Payment' : 'Partial Payment';
$pdf->Cell(100,10,$description,1,0);
$pdf->Cell(40,10,'₹'.$payment['amount_paid'],1,0,'R');
$pdf->Cell(40,10,formatPaymentMethod($payment['payment_method']),1,1,'C');
$pdf->Ln(15);

// Footer
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,'Thank you for your payment!',0,1,'C');

// Output PDF
$pdf->Output('D', 'FitTrack_Invoice_'.$payment['id'].'.pdf');

function formatPaymentMethod($method) {
    $methods = [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'card' => 'Card'
    ];
    return $methods[$method] ?? ucfirst($method);
}
?>