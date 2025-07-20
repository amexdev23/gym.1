<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: index.php');
    exit;
}

require '../config/config.php';

// Fetch member data and payment information
$stmt = $pdo->prepare("SELECT 
    m.full_name, 
    m.amount_paid, 
    m.amount_pending,
    m.membership_type,
    m.join_date,
    m.expiry_date,
    (SELECT COUNT(*) FROM payments WHERE member_id = m.id) as payment_count,
    (SELECT MAX(date_paid) FROM payments WHERE member_id = m.id) as last_payment_date
    FROM members m WHERE m.id = ?");
$stmt->execute([$_SESSION['member_id']]);
$member = $stmt->fetch();

// Format dates
$join_date = $member['join_date'] ? date('M d, Y', strtotime($member['join_date'])) : 'N/A';
$expiry_date = $member['expiry_date'] ? date('M d, Y', strtotime($member['expiry_date'])) : 'N/A';
$last_payment = $member['last_payment_date'] ? date('M d, Y', strtotime($member['last_payment_date'])) : 'Never';

// Fetch payment history
$paymentStmt = $pdo->prepare("SELECT 
    amount_paid, 
    date_paid, 
    payment_method, 
    payment_type,
    id
    FROM payments 
    WHERE member_id = ? 
    ORDER BY date_paid DESC");
$paymentStmt->execute([$_SESSION['member_id']]);
$payments = $paymentStmt->fetchAll();

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Format payment method
function formatPaymentMethod($method) {
    $methods = [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'card' => 'Card'
    ];
    return $methods[$method] ?? ucfirst($method);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment History | FitTrack</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .invoice-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .invoice-content {
      background-color: white;
      padding: 2rem;
      border-radius: 0.5rem;
      width: 90%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 pb-20">

  <!-- Header -->
  <header class="bg-gradient-to-r from-purple-700 to-blue-600 text-white p-4">
    <div class="flex justify-between items-center">
      <div class="font-bold text-lg">Payment History</div>
      <a href="dashboard.php" class="text-white">
        <i class="fa-solid fa-arrow-left text-xl"></i>
      </a>
    </div>
  </header>

  <main class="p-4 space-y-6">
    <!-- Payment Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h2 class="text-xl font-bold mb-4">Payment Summary</h2>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <p class="text-sm text-gray-500">Membership Type</p>
          <p class="font-medium"><?= htmlspecialchars($member['membership_type']) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Member Since</p>
          <p class="font-medium"><?= $join_date ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Total Payments</p>
          <p class="font-medium"><?= $member['payment_count'] ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Last Payment</p>
          <p class="font-medium"><?= $last_payment ?></p>
        </div>
      </div>
      
      <div class="border-t pt-4">
        <div class="flex justify-between mb-2">
          <span>Amount Paid:</span>
          <span class="font-bold text-green-600"><?= formatCurrency($member['amount_paid']) ?></span>
        </div>
        <div class="flex justify-between">
          <span>Pending Balance:</span>
          <span class="font-bold <?= $member['amount_pending'] > 0 ? 'text-orange-600' : 'text-green-600' ?>">
            <?= formatCurrency($member['amount_pending']) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="p-4 border-b">
        <h3 class="font-semibold text-lg">Payment Records</h3>
      </div>

      <?php if (count($payments) > 0): ?>
        <div class="divide-y">
          <?php foreach ($payments as $payment): ?>
            <div class="p-4">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium">
                    <?= $payment['payment_type'] === 'full' ? 'Full Payment' : 'Partial Payment' ?>
                  </p>
                  <p class="text-sm text-gray-500">
                    <?= date('M d, Y', strtotime($payment['date_paid'])) ?>
                    <span class="ml-2">
                      <?= formatPaymentMethod($payment['payment_method']) ?>
                    </span>
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    Invoice #<?= $payment['id'] ?>
                  </p>
                </div>
                <div class="flex items-center space-x-3">
                  <p class="font-bold text-green-600">
                    <?= formatCurrency($payment['amount_paid']) ?>
                  </p>
                  <button onclick="showInvoice(<?= $payment['id'] ?>)" 
                     class="bg-blue-100 text-blue-600 p-2 rounded-lg hover:bg-blue-200 transition"
                     title="View Invoice">
                    <i class="fas fa-file-invoice"></i>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="p-8 text-center text-gray-500">
          <i class="fas fa-receipt text-4xl mb-3"></i>
          <p>No payment records found</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Invoice Modal -->
  <div id="invoiceModal" class="invoice-modal">
    <div class="invoice-content">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">Invoice Details</h3>
        <button onclick="closeInvoice()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div id="invoiceDetails">
        <!-- Invoice content will be loaded here -->
      </div>
      <div class="mt-4 flex justify-end">
        <a id="downloadInvoice" href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          <i class="fas fa-download mr-2"></i> Download Invoice
        </a>
      </div>
    </div>
  </div>

  <!-- Bottom Navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md flex justify-around text-xs text-gray-600 z-10">
    <a href="dashboard.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-house text-lg"></i>
      Home
    </a>
    <a href="workouts.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-dumbbell text-lg"></i>
      Workouts
    </a>
    <a href="classes.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-calendar text-lg"></i>
      Classes
    </a>
    <a href="payments.php" class="flex flex-col items-center p-2 text-blue-600">
      <i class="fa-solid fa-credit-card text-lg"></i>
      Payments
    </a>
    <a href="profile.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-user text-lg"></i>
      Profile
    </a>
  </nav>

  <script>
    // Show invoice modal
    function showInvoice(paymentId) {
      // Fetch invoice details (in a real app, this would be an AJAX call)
      document.getElementById('invoiceDetails').innerHTML = `
        <div class="space-y-4">
          <div>
            <p class="text-sm text-gray-500">Invoice Number</p>
            <p class="font-medium">#${paymentId}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Payment Date</p>
            <p class="font-medium">${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Amount</p>
            <p class="font-bold text-green-600">₹${(Math.random() * 1000 + 500).toFixed(2)}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Payment Method</p>
            <p class="font-medium">UPI</p>
          </div>
          <div class="border-t pt-4">
            <p class="font-medium">Thank you for your payment!</p>
          </div>
        </div>
      `;
      
      // Set download link
      document.getElementById('downloadInvoice').href = `generate_invoice.php?id=${paymentId}`;
      
      // Show modal
      document.getElementById('invoiceModal').style.display = 'flex';
    }

    // Close invoice modal
    function closeInvoice() {
      document.getElementById('invoiceModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('invoiceModal');
      if (event.target === modal) {
        closeInvoice();
      }
    }
  </script>
</body>
</html>