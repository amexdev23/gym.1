<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: index.php');
    exit;
}

require '../config/config.php'; // Ensure this is included if not already

// Fetch the expiry date from the members table
$stmt = $pdo->prepare("SELECT expiry_date FROM members WHERE id = :id");
$stmt->execute(['id' => $_SESSION['member_id']]);
$memberData = $stmt->fetch();

$expiry_date_formatted = 'Not available';
if ($memberData && $memberData['expiry_date']) {
    // Format date as 'Month day, Year' (e.g., Oct 15, 2023)
    $expiry_date_formatted = date('M d, Y', strtotime($memberData['expiry_date']));
}


// Get the full name from the session
$full_name = $_SESSION['full_name'];
$first_letter = strtoupper(substr($full_name, 0, 1)); // Get the first letter of the full name
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FitTrack Mobile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-800 pb-20">

  <!-- Header -->
  <header class="bg-gradient-to-r from-purple-700 to-blue-600 text-white p-4">
    <div class="flex justify-between items-center">
      <div class="font-bold text-lg">FitTrack</div>
      <div class="relative flex items-center space-x-3">
        <i class="fa-solid fa-bell text-xl"></i>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1 rounded-full">3</span>
        <!-- Circle with the first letter of the user's name -->
        <div class="bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center text-sm"><?= $first_letter ?></div>
      </div>
    </div>
    <div class="mt-4">
      <!-- Display the user's full name -->
      <p class="text-sm">Welcome back, <span class="font-semibold"><?= htmlspecialchars($full_name) ?></span>!</p>
      <p class="text-xs text-purple-200">Your membership is active until: <span class="font-semibold"><?= htmlspecialchars($expiry_date_formatted) ?></span></p>

    </div>
  </header>

  <!-- Stats -->
  <section class="flex justify-around text-center bg-white py-4 border-b">
    <div>
      <p class="text-sm text-gray-500">Check-ins</p>
      <p class="text-lg font-bold text-blue-700">24</p>
    </div>
    <div>
      <p class="text-sm text-gray-500">Classes</p>
      <p class="text-lg font-bold text-blue-700">8</p>
    </div>
    <div>
      <p class="text-sm text-gray-500">Points</p>
      <p class="text-lg font-bold text-blue-700">350</p>
    </div>
  </section>

  <!-- Announcements -->
  <main class="p-4 space-y-4">
    <div>
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-lg font-semibold">Announcements</h2>
        <a href="#" class="text-sm text-blue-600">View All</a>
      </div>

      <!-- Cards -->
      <div class="space-y-3">

        <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-400">
          <div class="flex justify-between items-center">
            <h3 class="font-semibold text-base">Holiday Hours</h3>
            <span class="text-xs text-white bg-red-400 px-2 py-1 rounded-full">New</span>
          </div>
          <p class="text-sm text-gray-600">Gym will close early at 7PM on October 31st for Halloween.</p>
          <p class="text-xs text-gray-400 mt-1">Posted: 2 hours ago</p>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-400">
          <div class="flex justify-between items-center">
            <h3 class="font-semibold text-base">New Yoga Class</h3>
            <span class="text-xs text-white bg-blue-400 px-2 py-1 rounded-full">Class</span>
          </div>
          <p class="text-sm text-gray-600">Join our new morning yoga sessions every Tuesday and Thursday at 7AM.</p>
          <p class="text-xs text-gray-400 mt-1">Posted: 1 day ago</p>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-400">
          <div class="flex justify-between items-center">
            <h3 class="font-semibold text-base">New Equipment Arrived</h3>
            <span class="text-xs text-white bg-green-400 px-2 py-1 rounded-full">Equipment</span>
          </div>
          <p class="text-sm text-gray-600">Check out our new rowing machines in the cardio section!</p>
          <p class="text-xs text-gray-400 mt-1">Posted: 3 days ago</p>
        </div>

      </div>
    </div>
  </main>

  <!-- Bottom Navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md flex justify-around text-xs text-gray-600 z-10">
    <a href="#" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-house text-lg"></i>
      Home
    </a>
    <a href="#" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-dumbbell text-lg"></i>
      Equipment
    </a>
    <a href="#" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-calendar text-lg"></i>
      Classes
    </a>
    <a href="#" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-credit-card text-lg"></i>
      Payments
    </a>
    <a href="profile.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-user text-lg"></i>
      Profile
    </a>
  </nav>
  

</body>
</html>
