<?php
require '../config/config.php';
$pageTitle = 'Pending Payments';

session_start();

// Fetch members with pending amount
$stmt = $pdo->prepare("SELECT id, full_name, membership_type, price, amount_paid, amount_pending FROM members WHERE amount_pending > 0");
$stmt->execute();
$pendingMembers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-inter">

<div class="flex">
  <?php include('sidebar.php'); ?>
  <?php include('header.php'); ?>
</div>

<main class="flex-1 p-6 lg:p-8 ml-[250px]">
  <div class="mb-6">
    <h2 class="text-3xl font-semibold text-gray-800">Pending Payments</h2>
  </div>

  <?php if (count($pendingMembers) > 0): ?>
  <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-100 text-gray-700 text-left text-sm uppercase">
        <tr>
          <th class="px-6 py-3">#</th>
          <th class="px-6 py-3">Member Name</th>
          <th class="px-6 py-3">Membership</th>
          <th class="px-6 py-3">Total Amount (₹)</th>
          <th class="px-6 py-3">Amount Paid (₹)</th>
          <th class="px-6 py-3">Pending Amount (₹)</th>
          <th class="px-6 py-3">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200 text-sm">
        <?php foreach ($pendingMembers as $index => $member): ?>
        <tr>
          <td class="px-6 py-4"><?= $index + 1 ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($member['full_name']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($member['membership_type']) ?></td>
          <td class="px-6 py-4">₹<?= number_format($member['price'], 2) ?></td>
          <td class="px-6 py-4">₹<?= number_format($member['amount_paid'], 2) ?></td>
          <td class="px-6 py-4 text-red-600 font-semibold">₹<?= number_format($member['amount_pending'], 2) ?></td>
          <td class="px-6 py-4">
            <button onclick="openPayModal(<?= $member['id'] ?>, <?= $member['amount_pending'] ?>)" class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 text-sm">Pay</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="mt-6 bg-white shadow-md p-6 rounded-md text-center text-gray-500 text-lg">
    🎉 All members have cleared their payments.
  </div>
  <?php endif; ?>
</main>

<!-- Payment Modal -->
<div id="payModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
  <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
    <h3 class="text-xl font-semibold mb-4">Record Payment</h3>
    <form id="paymentForm" method="POST" action="record_payment.php">
      <input type="hidden" name="member_id" id="member_id">
      <input type="hidden" name="max_amount" id="max_amount">

      <div class="mb-4 text-center">
        <label class="block mb-2 font-semibold text-lg">Payment Type</label>
        <div class="flex justify-center gap-8">
          <label class="flex items-center space-x-2">
            <input type="radio" name="payment_type" value="full" class="payment_type_radio" required>
            <span>Full</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" name="payment_type" value="split" class="payment_type_radio">
            <span>Split</span>
          </label>
        </div>
      </div>

      <div class="mb-4 hidden" id="splitAmountDiv">
        <label class="block mb-1 font-medium">Enter Amount</label>
        <input type="number" step="0.01" name="split_amount" id="split_amount" class="w-full border rounded px-3 py-2" placeholder="Enter amount" />
        <p class="text-xs text-gray-500">Must be ≤ pending amount</p>
      </div>

      <div class="mb-4 hidden" id="paymentMethodDiv">
        <label class="block mb-1 font-medium">Payment Method</label>
        <select name="payment_method" id="payment_method" class="w-full border rounded px-3 py-2">
          <option value="upi">UPI</option>
          <option value="cash">Cash</option>
        </select>
      </div>

      <div class="flex justify-center gap-4 mt-4">
        <button type="button" onclick="closePayModal()" class="bg-gray-300 text-black px-4 py-2 rounded">Cancel</button>
        <button type="submit" id="submitPaymentBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Submit</button>
      </div>
    </form>
  </div>
</div>

<?php if (isset($_SESSION['payment_success'])): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Payment Recorded',
    text: 'Payment successfully recorded!',
    confirmButtonColor: '#3085d6'
  });
</script>
<?php unset($_SESSION['payment_success']); endif; ?>

<script>
function openPayModal(memberId, pendingAmount) {
  document.getElementById('payModal').classList.remove('hidden');
  document.getElementById('member_id').value = memberId;
  document.getElementById('max_amount').value = pendingAmount;
  document.querySelectorAll('input[name="payment_type"]').forEach(r => r.checked = false);
  document.getElementById('splitAmountDiv').classList.add('hidden');
  document.getElementById('paymentMethodDiv').classList.add('hidden');
  document.getElementById('split_amount').value = "";
}

function closePayModal() {
  document.getElementById('payModal').classList.add('hidden');
}

document.querySelectorAll('.payment_type_radio').forEach(radio => {
  radio.addEventListener('change', function () {
    const type = this.value;
    if (type === 'split') {
      document.getElementById('splitAmountDiv').classList.remove('hidden');
      document.getElementById('paymentMethodDiv').classList.add('hidden');
    } else if (type === 'full') {
      document.getElementById('splitAmountDiv').classList.add('hidden');
      document.getElementById('paymentMethodDiv').classList.remove('hidden');
    }
  });
});

document.getElementById('split_amount').addEventListener('input', function () {
  const maxAmount = parseFloat(document.getElementById('max_amount').value);
  const enteredAmount = parseFloat(this.value);
  if (!isNaN(enteredAmount) && enteredAmount > 0 && enteredAmount <= maxAmount) {
    document.getElementById('paymentMethodDiv').classList.remove('hidden');
  } else {
    document.getElementById('paymentMethodDiv').classList.add('hidden');
  }
});

document.getElementById('paymentForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const type = document.querySelector('input[name="payment_type"]:checked')?.value;
  const max = parseFloat(document.getElementById('max_amount').value);

  if (!type) {
    Swal.fire('Select Payment Type', '', 'warning');
    return;
  }

  if (type === 'split') {
    const amt = parseFloat(document.getElementById('split_amount').value);
    if (isNaN(amt) || amt <= 0 || amt > max) {
      Swal.fire('Invalid Amount', 'Please enter a valid split amount.', 'error');
      return;
    }
  }

  Swal.fire({
    title: 'Confirm Payment',
    text: 'Are you sure you want to record this payment?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, record it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('paymentForm').submit();
    }
  });
});
</script>

</body>
</html>
