<?php
// record_payment.php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../config/config.php';

require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['member_id'];
    $paymentType = $_POST['payment_type'];
    $paymentMethod = $_POST['payment_method'];
    $maxAmount = floatval($_POST['max_amount']);
    $splitAmount = isset($_POST['split_amount']) ? floatval($_POST['split_amount']) : 0;

    // Fetch member details
    $stmt = $pdo->prepare("SELECT full_name, email, membership_type, price, amount_paid, amount_pending FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();

    if (!$member) {
        $_SESSION['error'] = "Member not found.";
        header("Location: pending_payments.php");
        exit;
    }

    // Calculate new payment
    $paidNow = ($paymentType === 'full') ? $member['amount_pending'] : $splitAmount;
    $newPaid = $member['amount_paid'] + $paidNow;
    $newPending = $member['amount_pending'] - $paidNow;

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Update member's payment info
        $update = $pdo->prepare("UPDATE members SET amount_paid = ?, amount_pending = ? WHERE id = ?");
        $update->execute([$newPaid, $newPending, $memberId]);

        // Insert into payments table (modified to match your structure)
        $insertPayment = $pdo->prepare("
            INSERT INTO payments 
            (member_id, amount_paid, payment_method, payment_type, date_paid) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertPayment->execute([
            $memberId,
            $paidNow,
            $paymentMethod,
            $paymentType
        ]);

        // Commit transaction
        $pdo->commit();

        // Create invoice (simple HTML)
        $invoice = "
        <h2>Payment Invoice</h2>
        <p><strong>Member Name:</strong> {$member['full_name']}</p>
        <p><strong>Membership Type:</strong> {$member['membership_type']}</p>
        <p><strong>Total Amount:</strong> ₹" . number_format($member['price'], 2) . "</p>
        <p><strong>Paid Now:</strong> ₹" . number_format($paidNow, 2) . "</p>
        <p><strong>Total Paid:</strong> ₹" . number_format($newPaid, 2) . "</p>
        <p><strong>Remaining Amount:</strong> ₹" . number_format($newPending, 2) . "</p>
        <p><strong>Payment Method:</strong> " . ucfirst($paymentMethod) . "</p>
        <p><strong>Date:</strong> " . date("Y-m-d H:i:s") . "</p>
        ";

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'amitmac2334@gmail.com';
            $mail->Password = 'amitmachhi2334'; // Use App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('amitmac2334@gmail.com', 'Your Name');
            $mail->addAddress($member['email'], $member['full_name']);
            $mail->addBCC('amitmac2334@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = 'Payment Confirmation';
            $mail->Body = $invoice;

            $mail->send();
        } catch (Exception $e) {
            // Log email error but don't fail the transaction
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        $_SESSION['payment_success'] = true;
        header("Location: pending_payments.php");
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "An error occurred while processing your payment: " . $e->getMessage();
        header("Location: pending_payments.php");
        exit;
    }
}