<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: index.php');
    exit;
}

require '../config/config.php';

// Fetch member data
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id");
$stmt->execute(['id' => $_SESSION['member_id']]);
$member = $stmt->fetch();

// Format dates and check membership status
$join_date_formatted = $member['join_date'] ? date('M d, Y', strtotime($member['join_date'])) : 'Not available';
$expiry_date_formatted = $member['expiry_date'] ? date('M d, Y', strtotime($member['expiry_date'])) : 'Not available';

// Check if membership is expired
$is_expired = false;
if ($member['expiry_date']) {
    $today = new DateTime();
    $expiry_date = new DateTime($member['expiry_date']);
    $is_expired = $today > $expiry_date;
}

// Handle password change
$passwordError = '';
$passwordSuccess = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($currentPassword, $member['password'])) {
            $passwordError = "Current password is incorrect";
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = "New passwords don't match";
        } elseif (strlen($newPassword) < 8) {
            $passwordError = "Password must be at least 8 characters";
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $_SESSION['member_id']]);
            
            $passwordSuccess = "Password changed successfully!";
            // Refresh member data
            $stmt->execute([$_SESSION['member_id']]);
            $member = $stmt->fetch();
        }
    }
    
    if (isset($_POST['forgot_password'])) {
        // Handle forgot password logic
        $passwordSuccess = "Password reset instructions have been sent to your email.";
    }
}

// Get first letter for avatar
$first_letter = strtoupper(substr($member['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile | FitTrack</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }
    .collapse-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }
    .pulse-alert {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.6; }
      100% { opacity: 1; }
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 pb-20">

  <!-- Header -->
  <header class="bg-gradient-to-r from-purple-700 to-blue-600 text-white p-4">
    <div class="flex justify-between items-center">
      <div class="font-bold text-lg">My Profile</div>
      <a href="dashboard.php" class="text-white">
        <i class="fa-solid fa-arrow-left text-xl"></i>
      </a>
    </div>
  </header>

  <!-- Membership Status Banner -->
  <?php if ($is_expired): ?>
    <div class="bg-red-100 text-red-800 p-3 mx-4 mt-4 rounded-lg flex items-center pulse-alert">
      <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
      <div>
        <p class="font-semibold">Your membership has expired!</p>
        <p class="text-sm">Renew now to continue accessing all facilities.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Profile Section -->
  <main class="p-4 space-y-6">
    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-sm p-4">
      <div class="flex items-center space-x-4">
        <div class="bg-gray-200 rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold">
          <?= $first_letter ?>
        </div>
        <div>
          <h2 class="font-bold text-lg"><?= htmlspecialchars($member['full_name']) ?></h2>
          <p class="text-sm text-gray-600">Member since <?= $join_date_formatted ?></p>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="bg-blue-50 p-3 rounded-lg">
          <p class="text-xs text-blue-600">Membership</p>
          <p class="font-semibold"><?= htmlspecialchars($member['membership_type']) ?></p>
          <?php if ($is_expired): ?>
            <p class="text-xs text-red-600 mt-1">Expired</p>
          <?php endif; ?>
        </div>
        <div class="bg-purple-50 p-3 rounded-lg">
          <p class="text-xs <?= $is_expired ? 'text-red-600' : 'text-purple-600' ?>">
            <?= $is_expired ? 'Expired On' : 'Expires' ?>
          </p>
          <p class="font-semibold <?= $is_expired ? 'text-red-600' : '' ?>">
            <?= $expiry_date_formatted ?>
          </p>
          <?php if ($is_expired): ?>
            <a href="renew.php" class="text-xs text-blue-600 mt-1 inline-block">Renew Now</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Personal Info -->
    <div class="bg-white rounded-xl shadow-sm p-4">
      <h3 class="font-semibold text-lg mb-3">Personal Information</h3>
      
      <div class="space-y-4">
        <div>
          <p class="text-xs text-gray-500">Email</p>
          <p class="font-medium"><?= htmlspecialchars($member['email']) ?></p>
        </div>
        
        <div>
          <p class="text-xs text-gray-500">Phone</p>
          <p class="font-medium"><?= htmlspecialchars($member['phone']) ?></p>
        </div>
        
        <div>
          <p class="text-xs text-gray-500">Gender</p>
          <p class="font-medium"><?= htmlspecialchars($member['gender']) ?></p>
        </div>
        
        <div>
          <p class="text-xs text-gray-500">Address</p>
          <p class="font-medium"><?= htmlspecialchars($member['address']) ?></p>
        </div>
      </div>
    </div>

    <!-- Password Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <button onclick="togglePasswordSection()" class="w-full text-left p-4 font-semibold flex justify-between items-center">
        <span>Password Settings</span>
        <i id="passwordToggleIcon" class="fas fa-chevron-down transition-transform"></i>
      </button>
      
      <div id="passwordSection" class="collapse-content">
        <div class="px-4 pb-4 space-y-4">
          <?php if ($passwordError): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm">
              <?= $passwordError ?>
            </div>
          <?php endif; ?>
          
          <?php if ($passwordSuccess): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg text-sm">
              <?= $passwordSuccess ?>
            </div>
          <?php endif; ?>
          
          <!-- Change Password Form -->
          <form method="POST" class="space-y-4">
            <div class="relative">
              <label class="block text-xs text-gray-500 mb-1">Current Password</label>
              <input type="password" name="current_password" required 
                    class="w-full p-2 border rounded-lg pr-10">
              <i class="fa-solid fa-eye-slash password-toggle" 
                onclick="togglePassword(this)"></i>
            </div>
            
            <div class="relative">
              <label class="block text-xs text-gray-500 mb-1">New Password</label>
              <input type="password" name="new_password" required 
                    class="w-full p-2 border rounded-lg pr-10">
              <i class="fa-solid fa-eye-slash password-toggle" 
                onclick="togglePassword(this)"></i>
            </div>
            
            <div class="relative">
              <label class="block text-xs text-gray-500 mb-1">Confirm New Password</label>
              <input type="password" name="confirm_password" required 
                    class="w-full p-2 border rounded-lg pr-10">
              <i class="fa-solid fa-eye-slash password-toggle" 
                onclick="togglePassword(this)"></i>
            </div>
            
            <button type="submit" name="change_password"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold">
              Change Password
            </button>
          </form>
          
          <!-- Forgot Password Option -->
          <div class="pt-2 border-t">
            <form method="POST">
              <button type="submit" name="forgot_password" 
                      class="text-blue-600 text-sm font-medium">
                <i class="fas fa-key mr-1"></i> Forgot Password?
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Bottom Navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md flex justify-around text-xs text-gray-600 z-10">
    <a href="dashboard.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-house text-lg"></i>
      Home
    </a>
    <a href="#" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-dumbbell text-lg"></i>
      Equipment
    </a>
    <a href="classes.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-calendar text-lg"></i>
      Classes
    </a>
    <a href="payments.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-credit-card text-lg"></i>
      Payments
    </a>
    <a href="profile.php" class="flex flex-col items-center p-2 text-blue-600">
      <i class="fa-solid fa-user text-lg"></i>
      Profile
    </a>
  </nav>

  <script>
    // Toggle password visibility
    function togglePassword(icon) {
      const input = icon.previousElementSibling;
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      }
    }
    
    // Toggle password section
    function togglePasswordSection() {
      const section = document.getElementById('passwordSection');
      const icon = document.getElementById('passwordToggleIcon');
      
      if (section.style.maxHeight) {
        section.style.maxHeight = null;
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
      } else {
        section.style.maxHeight = section.scrollHeight + "px";
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
      }
    }
    
    // Auto-expand if there are errors
    document.addEventListener('DOMContentLoaded', function() {
      <?php if ($passwordError || $passwordSuccess): ?>
        togglePasswordSection();
      <?php endif; ?>
    });
  </script>
</body>
</html>