<?php
session_start();
require '../config/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = :email OR id = :id");
    $stmt->execute([
        'email' => $email,
        'id' => $email
    ]);

    $user = $stmt->fetch();

    if ($user && $user['password'] === $password) {
        // Set session and redirect to dashboard
        $_SESSION['member_id'] = $user['id']; // Store user id in session
        $_SESSION['full_name'] = $user['full_name']; // Store full name or any other detail
        header("Location: dashboard.php"); // Redirect to dashboard
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FitTrack Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-500 to-purple-600 min-h-screen flex items-center justify-center px-4">

  <div class="relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
    <div class="text-center mb-6">
      <svg class="mx-auto w-12 h-12 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2z" />
      </svg>
      <h1 class="text-3xl font-bold text-gray-800">FitTrack</h1>
      <p class="text-sm text-gray-500">Your fitness journey starts here</p>
    </div>

    <form method="POST" action="">
      <?php if (!empty($error)): ?>
        <p class="text-red-500 text-sm mb-2 text-center"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email or Membership ID</label>
          <input name="email" id="email" type="text" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" />
        </div>

        <div class="relative">
  <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
  <input name="password" id="password" type="password" required
         class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 pr-10" />
  <button type="button" id="togglePassword" class="absolute right-2 top-9 text-gray-500">
    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
         viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" />
    </svg>
  </button>
</div>


        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2 text-sm text-gray-700">
            <input type="checkbox" name="remember" class="w-4 h-4 text-purple-600 rounded" />
            <span>Remember me</span>
          </label>
          <a href="#" class="text-sm text-purple-600 hover:underline">Forgot Password?</a>
        </div>

        <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">Sign In</button>
      </div>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      Don’t have an account? <a href="#" class="text-purple-600 hover:underline">Contact your gym</a>
    </p>
  </div>

  <footer class="absolute bottom-4 text-white text-xs text-center w-full z-0">
    © <?= date("Y") ?> FitTrack Gym Management System
  </footer>
</body>
</html>
<script>
  document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 012.045-3.368M9.88 9.88a3 3 0 104.24 4.24" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 01-6 0m8.485-3.515L4.515 19.485" />
      `;
    } else {
      passwordInput.type = 'password';
      eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" />
      `;
    }
  });
</script>
