<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FitTrack Mobile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="pb-16"> <!-- Add padding bottom to avoid content behind nav -->

  <!-- Your page content goes here -->

  <!-- Bottom Navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md flex justify-around text-xs text-gray-600 z-10">
    <a href="home.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-house text-lg"></i>
      Home
    </a>
    <a href="equipment.php" class="flex flex-col items-center p-2 hover:text-blue-600">
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
    <a href="profile.php" class="flex flex-col items-center p-2 hover:text-blue-600">
      <i class="fa-solid fa-user text-lg"></i>
      Profile
    </a>
  </nav>

</body>
</html>
