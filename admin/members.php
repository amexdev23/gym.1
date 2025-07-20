<?php
require '../config/config.php';
$pageTitle = 'Members';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filters
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Query building
$sql = "SELECT * FROM members WHERE 1";
$params = [];

if ($status !== '') {
    $sql .= " AND status = :status";
    $params[':status'] = $status;
}
if ($type !== '') {
    $sql .= " AND membership_type = :type";
    $params[':type'] = $type;
}
if ($search !== '') {
    $sql .= " AND (full_name LIKE :search OR phone LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

$total = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $sql));
$total->execute($params);
$total_members = $total->fetchColumn();

$sql .= " ORDER BY membership_type ASC LIMIT $start, $limit";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

// Handle Add Member Form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    // Initialize variables for amounts
    $amount_paid = 0;
    $amount_pending = 0;

    // Get price and payment status
    $price = $_POST['price'];
    $payment_status = $_POST['payment_status'];

    // Determine amounts based on payment status
    if ($payment_status === 'Paid') {
        $amount_paid = $price;
        $amount_pending = 0;
    } elseif ($payment_status === 'Pending') {
        $amount_paid = 0;
        $amount_pending = $price;
    } elseif ($payment_status === 'Split') {
        $amount_paid = $_POST['amount_paid'] ?? 0;
        $amount_pending = $price - $amount_paid;
    }

    // Insert member data with calculated payment amounts
    $stmt = $pdo->prepare("INSERT INTO members 
    (full_name, phone, email, password, membership_type, price, gender, address, duration, join_date, expiry_date, payment_status, amount_paid, amount_pending) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['password'],
        $_POST['membership_type'],
        $price,
        $_POST['gender'],
        $_POST['address'],
        $_POST['duration'],
        $_POST['join_date'],
        $_POST['expiry_date'],
        $payment_status,
        $amount_paid,
        $amount_pending
    ]);

    // Redirect to refresh the page
    header("Location: members.php");
    exit;
}

// Handle Edit Member Form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_member'])) {
    $id = $_POST['id'];
    
    // Initialize variables for amounts
    $amount_paid = 0;
    $amount_pending = 0;
    $price = $_POST['price'];
    $payment_status = $_POST['payment_status'];

    // Determine amounts based on payment status
    if ($payment_status === 'Paid') {
        $amount_paid = $price;
        $amount_pending = 0;
    } elseif ($payment_status === 'Pending') {
        $amount_paid = 0;
        $amount_pending = $price;
    } elseif ($payment_status === 'Split') {
        $amount_paid = $_POST['amount_paid'] ?? 0;
        $amount_pending = $price - $amount_paid;
    }

    $stmt = $pdo->prepare("UPDATE members SET 
        full_name = ?, 
        phone = ?, 
        email = ?, 
        password = ?,
        membership_type = ?, 
        price = ?, 
        gender = ?, 
        address = ?, 
        duration = ?, 
        join_date = ?, 
        expiry_date = ?, 
        payment_status = ?, 
        amount_paid = ?, 
        amount_pending = ?
        WHERE id = ?");

    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['password'],
        $_POST['membership_type'],
        $price,
        $_POST['gender'],
        $_POST['address'],
        $_POST['duration'],
        $_POST['join_date'],
        $_POST['expiry_date'],
        $payment_status,
        $amount_paid,
        $amount_pending,
        $id
    ]);

    // Redirect to refresh the page
    header("Location: members.php");
    exit;
}

// Handle Delete Member
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);
    
    // Redirect to refresh the page
    header("Location: members.php");
    exit;
}

// Get member data for editing if edit_id is set
$edit_member = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_member = $stmt->fetch();
}
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

<main class="flex-1 p-6 lg:p-0 ml-[250px]">
  <div class="flex-1 p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">All Members</h2>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-4 mb-6">
      <input type="text" name="search" placeholder="Search members..." value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 border rounded-lg">
      <select name="status" class="px-4 py-2 border rounded-lg">
        <option value="">All Statuses</option>
        <option value="Active" <?= $status == 'Active' ? 'selected' : '' ?>>Active</option>
        <option value="Inactive" <?= $status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
      <select name="type" class="px-4 py-2 border rounded-lg">
        <option value="">All Types</option>
        <option value="Premium" <?= $type == 'Premium' ? 'selected' : '' ?>>Premium</option>
        <option value="Basic" <?= $type == 'Basic' ? 'selected' : '' ?>>Basic</option>
      </select>
      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Filter</button>
      <button type="button" onclick="openModal('add')" class="ml-auto bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Member</button>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-gray-200 text-gray-700">
            <th class="p-4">#</th>
            <th class="p-4">Member</th>
            <th class="p-4">Contact</th>
            <th class="p-4">Plan</th>
            <th class="p-4">Status</th>
            <th class="p-4">Joined</th>
            <th class="p-4">Expires</th>
            <th class="p-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $i => $member): ?>
            <tr class="border-b">
              <td class="p-4"><?= $start + $i + 1 ?></td>
              <td class="p-4"><?= htmlspecialchars($member['full_name']) ?></td>
              <td class="p-4"><?= htmlspecialchars($member['phone']) ?></td>
              <td class="p-4"><?= htmlspecialchars($member['membership_type']) ?><br>₹<?= $member['price'] ?>/month</td>
              <td class="p-4">
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm"><?= htmlspecialchars($member['status']) ?></span>
              </td>
              <td class="p-4"><?= date("M d, Y", strtotime($member['join_date'])) ?></td>
              <td class="p-4"><?= date("M d, Y", strtotime($member['expiry_date'])) ?></td>
              <td class="p-4 space-x-2">
                <button onclick="openModal('edit', <?= $member['id'] ?>)" class="text-blue-600 hover:underline">Edit</button>
                <a href="?delete=<?= $member['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this member?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($members) == 0): ?>
            <tr><td colspan="8" class="p-4 text-center text-gray-500">No members found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex items-center flex-wrap gap-2">
      <span class="text-sm text-gray-600">Showing <?= $start + 1 ?> to <?= min($start + $limit, $total_members) ?> of <?= $total_members ?> members</span>
      <?php
      $total_pages = ceil($total_members / $limit);
      for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="px-3 py-1 bg-green-500 text-white rounded-md text-sm"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i ?>&status=<?= $status ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border rounded-md text-sm hover:bg-gray-100"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</main>

<!-- Add/Edit Modal -->
<div id="memberModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center hidden">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-8 relative">
    
    <!-- Close Button -->
    <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-3xl">&times;</button>
    
    <!-- Title -->
    <h3 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2" id="modalTitle">Add New Member</h3>
    
    <!-- Form -->
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <input type="hidden" name="id" id="edit_id" value="<?= $edit_member['id'] ?? '' ?>">
      
      <input type="text" name="full_name" id="full_name" placeholder="Full Name" required 
             value="<?= $edit_member['full_name'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <input type="text" name="phone" id="phone" placeholder="Phone" required 
             value="<?= $edit_member['phone'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <input type="email" name="email" id="email" placeholder="Email" required 
             value="<?= $edit_member['email'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <input type="password" name="password" id="password" placeholder="Password" required 
             value="<?= $edit_member['password'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <select name="gender" id="gender" required class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Select Gender</option>
        <option value="Male" <?= ($edit_member['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= ($edit_member['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
        <option value="Other" <?= ($edit_member['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
      </select>
      
      <input type="text" name="address" id="address" placeholder="Address" required 
             value="<?= $edit_member['address'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <select name="membership_type" id="membership_type" required class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Select Plan</option>
        <option value="Premium" <?= ($edit_member['membership_type'] ?? '') == 'Premium' ? 'selected' : '' ?>>Premium</option>
        <option value="Basic" <?= ($edit_member['membership_type'] ?? '') == 'Basic' ? 'selected' : '' ?>>Basic</option>
      </select>
      
      <input type="number" name="price" id="price" placeholder="Plan Price (₹)" required 
             value="<?= $edit_member['price'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <select name="duration" id="duration" required class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Select Duration</option>
        <option value="1 Month" <?= ($edit_member['duration'] ?? '') == '1 Month' ? 'selected' : '' ?>>1 Month</option>
        <option value="3 Months" <?= ($edit_member['duration'] ?? '') == '3 Months' ? 'selected' : '' ?>>3 Months</option>
        <option value="6 Months" <?= ($edit_member['duration'] ?? '') == '6 Months' ? 'selected' : '' ?>>6 Months</option>
        <option value="1 Year" <?= ($edit_member['duration'] ?? '') == '1 Year' ? 'selected' : '' ?>>1 Year</option>
      </select>
      
      <input type="date" name="join_date" id="join_date" required 
             value="<?= $edit_member['join_date'] ?? date('Y-m-d') ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      
      <input type="date" name="expiry_date" id="expiry_date" required 
             value="<?= $edit_member['expiry_date'] ?? '' ?>" 
             class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">

      <!-- Payment Status -->
      <select name="payment_status" id="payment_status" required class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Select Payment Status</option>
        <option value="Paid" <?= ($edit_member['payment_status'] ?? '') == 'Paid' ? 'selected' : '' ?>>Paid</option>
        <option value="Pending" <?= ($edit_member['payment_status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Split" <?= ($edit_member['payment_status'] ?? '') == 'Split' ? 'selected' : '' ?>>Split</option>
      </select>

      <!-- Payment Method (for Paid) -->
      <div id="paid_method" class="hidden col-span-full">
        <label class="block text-gray-600 font-medium">Payment Method (Paid)</label>
        <select name="paid_method" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
          <option value="Cash">Cash</option>
          <option value="UPI">UPI</option>
        </select>
      </div>

      <!-- Payment Inputs (Only for Split) -->
      <div id="split_payment_fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 col-span-full">
        <input type="number" name="amount_paid" id="amount_paid" placeholder="Amount Paid (₹)" 
               value="<?= $edit_member['amount_paid'] ?? '' ?>" 
               class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        <input type="text" name="amount_pending" id="amount_pending" placeholder="Pending Amount (₹)" readonly 
               value="<?= isset($edit_member['amount_pending']) ? $edit_member['amount_pending'] : '' ?>" 
               class="border border-gray-300 p-3 rounded-lg bg-gray-100 text-gray-700">
        
        <div class="col-span-full">
          <label class="block text-gray-600 font-medium">Payment Method (Split)</label>
          <select name="split_method" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="Cash">Cash</option>
            <option value="UPI">UPI</option>
          </select>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="col-span-full flex justify-end">
        <?php if (isset($edit_member)): ?>
          <button type="submit" name="edit_member" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300">
            Update Member
          </button>
        <?php else: ?>
          <button type="submit" name="add_member" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300">
            Add Member
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(action, id = null) {
  const modal = document.getElementById("memberModal");
  const title = document.getElementById("modalTitle");
  
  if (action === 'edit' && id) {
    title.textContent = "Edit Member";
    // Here you would typically fetch the member data via AJAX and populate the form
    // For now, we'll just open the modal and let PHP handle the form population
    window.location.href = `?edit_id=${id}`;
    return;
  } else {
    title.textContent = "Add New Member";
    // Reset form if adding new member
    if (window.location.href.includes('edit_id')) {
      window.location.href = 'members.php';
    } else {
      modal.classList.remove('hidden');
    }
  }
}

function closeModal() {
  document.getElementById('memberModal').classList.add('hidden');
  // Remove edit_id from URL if present
  if (window.location.href.includes('edit_id')) {
    window.location.href = 'members.php';
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const joinDateInput = document.getElementById("join_date");
  const durationSelect = document.getElementById("duration");
  const expiryDateInput = document.getElementById("expiry_date");
  const paymentStatus = document.getElementById("payment_status");
  const splitFields = document.getElementById("split_payment_fields");
  const paidMethod = document.getElementById("paid_method");
  const amountPaidInput = document.getElementById("amount_paid");
  const amountPendingInput = document.getElementById("amount_pending");
  const priceInput = document.getElementById("price");

  function updateExpiryDate() {
    const duration = durationSelect.value;
    const joinDate = new Date(joinDateInput.value);
    if (isNaN(joinDate)) return;

    let expiryDate = new Date(joinDate);
    switch (duration) {
      case "1 Month": expiryDate.setMonth(expiryDate.getMonth() + 1); break;
      case "3 Months": expiryDate.setMonth(expiryDate.getMonth() + 3); break;
      case "6 Months": expiryDate.setMonth(expiryDate.getMonth() + 6); break;
      case "1 Year": expiryDate.setFullYear(expiryDate.getFullYear() + 1); break;
    }

    expiryDateInput.value = expiryDate.toISOString().split('T')[0];
  }

  function handlePaymentChange() {
    const status = paymentStatus.value;
    splitFields.classList.add("hidden");
    paidMethod.classList.add("hidden");

    if (status === "Split") {
      splitFields.classList.remove("hidden");
    } else if (status === "Paid") {
      paidMethod.classList.remove("hidden");
    }
  }

  function updatePendingAmount() {
    const price = parseFloat(priceInput.value) || 0;
    const paid = parseFloat(amountPaidInput.value) || 0;
    const pending = price - paid;
    amountPendingInput.value = pending > 0 ? pending.toFixed(2) : "0.00";
  }

  durationSelect.addEventListener("change", updateExpiryDate);
  joinDateInput.addEventListener("change", updateExpiryDate);
  paymentStatus.addEventListener("change", handlePaymentChange);
  amountPaidInput.addEventListener("input", updatePendingAmount);
  priceInput.addEventListener("input", updatePendingAmount);

  // Initialize if editing
  <?php if (isset($edit_member)): ?>
    document.getElementById('memberModal').classList.remove('hidden');
    handlePaymentChange();
    updatePendingAmount();
  <?php endif; ?>
});
</script>
</body>
</html>