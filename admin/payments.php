<?php
require '../config/config.php';
$pageTitle = 'Payments';

// Fetch all members
$stmt = $pdo->query("SELECT id, full_name, phone, membership_type, expiry_date, duration, price FROM members ORDER BY full_name ASC");
$members = $stmt->fetchAll();

// Fetch payment statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_payments,
        SUM(amount_paid) as total_revenue,
        (SELECT COUNT(*) FROM payments WHERE DATE(date_paid) = CURDATE()) as today_payments,
        (SELECT SUM(amount_paid) FROM payments WHERE DATE(date_paid) = CURDATE()) as today_revenue
    FROM payments
");
$paymentStats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-inter">

<div class="flex">
  <?php include('sidebar.php'); ?>
  <?php include('header.php'); ?>
</div>

<main class="flex-1 p-6 lg:p-8 ml-[250px]">
  <div class="mb-6">
    <h2 class="text-3xl font-semibold text-gray-800">Member Payments</h2>
  </div>

  <!-- Payment Statistics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Payments -->
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full">
          <i class="fas fa-receipt text-lg"></i>
        </div>
        <div>
          <h3 class="text-gray-500 text-sm">Total Payments</h3>
          <h1 class="text-2xl font-bold text-gray-800"><?= $paymentStats['total_payments'] ?></h1>
          <p class="text-xs text-gray-500 mt-1"><?= $paymentStats['today_payments'] ?> today</p>
        </div>
      </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 flex items-center justify-center bg-green-100 text-green-600 rounded-full">
          <i class="fas fa-rupee-sign text-lg"></i>
        </div>
        <div>
          <h3 class="text-gray-500 text-sm">Total Revenue</h3>
          <h1 class="text-2xl font-bold text-gray-800">₹<?= number_format($paymentStats['total_revenue'], 2) ?></h1>
          <p class="text-xs text-gray-500 mt-1">₹<?= number_format($paymentStats['today_revenue'], 2) ?> today</p>
        </div>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full">
          <i class="fas fa-credit-card text-lg"></i>
        </div>
        <div>
          <h3 class="text-gray-500 text-sm">Payment Methods</h3>
          <h1 class="text-2xl font-bold text-gray-800">2</h1>
          <p class="text-xs text-gray-500 mt-1">Cash,UPI</p>
        </div>
      </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-yellow-500">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded-full">
          <i class="fas fa-clock text-lg"></i>
        </div>
        <div>
          <h3 class="text-gray-500 text-sm">Recent Payments</h3>
          <h1 class="text-2xl font-bold text-gray-800">3</h1>
          <p class="text-xs text-gray-500 mt-1">Last 24 hours</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Members Table -->
  <div class="overflow-x-auto bg-white shadow-lg rounded-lg mb-6">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-100 text-gray-700 text-left text-sm uppercase">
        <tr>
          <th class="px-6 py-3">#</th>
          <th class="px-6 py-3">Name</th>
          <th class="px-6 py-3">Phone</th>
          <th class="px-6 py-3">Plan</th>
          <th class="px-6 py-3">Expiry</th>
          <th class="px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200 text-sm">
        <?php foreach ($members as $i => $member): ?>
        <tr>
          <td class="px-6 py-4"><?= $i + 1 ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($member['full_name']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($member['phone']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($member['membership_type']) ?></td>
          <td class="px-6 py-4"><?= date("M d, Y", strtotime($member['expiry_date'])) ?></td>
          <td class="px-6 py-4 flex space-x-2">
            <button onclick="openPaymentModal(<?= $member['id'] ?>, '<?= htmlspecialchars(addslashes($member['full_name'])) ?>', '<?= htmlspecialchars($member['membership_type']) ?>', <?= $member['price'] ?>, <?= $member['duration'] ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Pay</button>
            <button onclick="sendPaymentReminder(<?= $member['id'] ?>, '<?= htmlspecialchars(addslashes($member['full_name'])) ?>')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm">Alert</button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($members) === 0): ?>
        <tr>
          <td colspan="6" class="text-center px-6 py-4 text-gray-500">No members found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
    
  </div>
</main>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center hidden">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg relative">
    <button onclick="closePaymentModal()" class="absolute top-3 right-4 text-gray-500 hover:text-black text-xl">&times;</button>
    <h3 class="text-2xl font-semibold text-gray-800 mb-6">Process Payment</h3>
    <form method="POST" action="process_payment.php" class="space-y-4">
      <input type="hidden" name="member_id" id="payment_member_id">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Member Name</label>
        <input type="text" id="payment_member_name" class="w-full bg-gray-100 border p-2 rounded-md" readonly>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Plan Type</label>
          <input type="text" id="payment_plan_type" class="w-full bg-gray-100 border p-2 rounded-md" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
          <input type="text" id="payment_price" class="w-full bg-gray-100 border p-2 rounded-md" readonly>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Duration (Months)</label>
          <input type="text" id="payment_duration" class="w-full bg-gray-100 border p-2 rounded-md" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹)</label>
          <input type="number" name="amount" required class="w-full border p-2 rounded-md">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
          <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="w-full border p-2 rounded-md">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
          <select name="payment_method" required class="w-full border p-2 rounded-md">
            <option value="">Select Method</option>
            <option value="Cash">Cash</option>
            <option value="UPI">UPI</option>
          </select>
        </div>
      </div>

      <div class="text-right mt-4">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md">Submit Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openPaymentModal(id, name, planType, price, duration) {
    document.getElementById('payment_member_id').value = id;
    document.getElementById('payment_member_name').value = name;
    document.getElementById('payment_plan_type').value = planType;
    document.getElementById('payment_price').value = price;
    document.getElementById('payment_duration').value = duration;
    document.getElementById('paymentModal').classList.remove('hidden');
  }

  function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
  }

  function sendPaymentReminder(memberId, memberName) {
    if (confirm(`Do you want to send a reminder to ${memberName}?`)) {
      alert(`Reminder sent to ${memberName}.`);
    }
  }
</script>

</body>
</html>