<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sidebar</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
    }

    .sidebar {
      width: 220px;
      height: 100vh;
      background-color: #1e2a38;
      color: white;
      position: fixed;
      left: 0;
      top: 0;
      padding: 20px 0;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
      font-weight: 700;
      padding: 0 20px 30px;
      color: #fff;
    }

    .logo i {
      font-size: 24px;
    }

    .nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .nav li {
      position: relative;
    }

    .nav > li > a {
      display: flex;
      align-items: center;
      padding: 14px 20px;
      color: #cbd5e0;
      text-decoration: none;
      transition: background 0.3s;
    }

    .nav > li > a:hover,
    .nav > li.active > a {
      background-color: #2d3b4d;
      color: #fff;
      border-left: 4px solid #3b82f6;
    }

    .nav li i {
      margin-right: 12px;
      font-size: 18px;
    }

    .nav li span {
      font-size: 15px;
    }

   /* Updated submenu styling for right-side hover */
.nav li .submenu {
  display: none;
  flex-direction: column;
  background-color: #263446;
  position: absolute;
  top: 0;
  left: 100%;
  width: 180px;
  z-index: 100;
  border-radius: 4px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.nav li:hover .submenu {
  display: flex;
}


    .submenu a {
      padding: 12px 20px;
      font-size: 14px;
      color: #cbd5e0;
      text-decoration: none;
      transition: background 0.3s;
    }

    .submenu a:hover,
    .submenu .active {
      background-color: #3b4d5f;
      color: #fff;
    }
  </style>
</head>
<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="sidebar">
  <div class="logo">
    <i class="fas fa-cube"></i>
    <span>FitTrack Pro</span>
  </div>
  <ul class="nav">
    <li class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
      <a href="index.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
    </li>
    <li class="<?= ($current_page == 'members.php') ? 'active' : '' ?>">
      <a href="members.php"><i class="fas fa-users"></i><span>Members</span></a>
    </li>
    <li class="<?= ($current_page == 'attendance.php') ? 'active' : '' ?>">
      <a href="attendance.php"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
    </li>
    <li class="<?= ($current_page == 'classes.php') ? 'active' : '' ?>">
      <a href="classes.php"><i class="fas fa-dumbbell"></i><span>Classes</span></a>
    </li>

    <!-- Payments with hover submenu -->
    <li class="<?= ($current_page == 'payments.php' || $current_page == 'pending_payments.php') ? 'active' : '' ?>">
      <a href="payments.php"><i class="fas fa-rupee-sign"></i><span>Payments</span></a>
      <div class="submenu">
        <a href="payments.php" class="<?= ($current_page == 'payments.php') ? 'active' : '' ?>">Renewals</a>
        <a href="pending_payments.php" class="<?= ($current_page == 'pending_payments.php') ? 'active' : '' ?>">Pending Payments</a>
      </div>
    </li>

    <li class="<?= ($current_page == 'settings.php') ? 'active' : '' ?>">
      <a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a>
    </li>
  </ul>
</div>

</body>
</html>
