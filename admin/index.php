<?php


require '../config/config.php';

// Fetch dashboard statistics
$statsStmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM members) as total_members,
        (SELECT COUNT(*) FROM members WHERE status = 'Active') as active_members,
        (SELECT SUM(p.amount_paid) FROM payments p WHERE MONTH(p.date_paid) = MONTH(CURRENT_DATE())) as monthly_revenue,
        (SELECT COUNT(*) FROM members WHERE DATE(join_date) = CURDATE()) as new_members_today,
        (SELECT SUM(p.amount_paid) FROM payments p WHERE DATE(p.date_paid) = CURDATE()) as todays_revenue
");
$statsStmt->execute();
$stats = $statsStmt->fetch();

// Demo data for check-ins (since you don't have this table)
$todays_checkins = 42; // Demo value
$checkin_change = "-3%"; // Demo value

// Calculate percentage changes (demo calculations)
$member_change = "+12%";
$active_change = "+5%";
$revenue_change = "+8%";

// Fetch recent activity - fixed all ambiguous column references
$activityStmt = $pdo->prepare("
    (SELECT 'member' as type, m.id as id, m.full_name as name, m.join_date as date, NULL as amount 
     FROM members m ORDER BY m.join_date DESC LIMIT 2)
    UNION ALL
    (SELECT 'payment' as type, p.id as id, m.full_name as name, p.date_paid as date, p.amount_paid as amount 
     FROM payments p JOIN members m ON p.member_id = m.id 
     ORDER BY p.date_paid DESC LIMIT 3)
    ORDER BY date DESC 
    LIMIT 3
");
$activityStmt->execute();
$recentActivity = $activityStmt->fetchAll();

$pageTitle = 'Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FitTrack Pro Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-inter">

  <div class="flex">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
  </div>
  
  <!-- Main Section -->
  <main class="flex-1 p-6 lg:p-0 ml-[250px]">
    
    <!-- Cards Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

      <!-- Total Members -->
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full">
            <i class="fas fa-users text-lg"></i>
          </div>
          <div>
            <h3 class="text-gray-500 text-sm">Total Members</h3>
            <h1 class="text-2xl font-bold text-gray-800"><?= $stats['total_members'] ?></h1>
            <p class="text-sm text-green-600 mt-1"><?= $member_change ?> from last month</p>
            <p class="text-xs text-gray-500 mt-1"><?= $stats['new_members_today'] ?> new today</p>
          </div>
        </div>
      </div>

      <!-- Active Members -->
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center bg-green-100 text-green-600 rounded-full">
            <i class="fas fa-user-check text-lg"></i>
          </div>
          <div>
            <h3 class="text-gray-500 text-sm">Active Members</h3>
            <h1 class="text-2xl font-bold text-gray-800"><?= $stats['active_members'] ?></h1>
            <p class="text-sm text-green-600 mt-1"><?= $active_change ?> from last month</p>
            <p class="text-xs text-gray-500 mt-1"><?= round(($stats['active_members']/$stats['total_members'])*100) ?>% active rate</p>
          </div>
        </div>
      </div>

      <!-- Today's Check-ins (Demo Data) -->
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full">
            <i class="fas fa-clipboard-list text-lg"></i>
          </div>
          <div>
            <h3 class="text-gray-500 text-sm">Today's Check-ins</h3>
            <h1 class="text-2xl font-bold text-gray-800"><?= $todays_checkins ?></h1>
            <p class="text-sm text-red-500 mt-1"><?= $checkin_change ?> from yesterday</p>
            <p class="text-xs text-gray-500 mt-1">Peak hour: 6-7 PM (demo)</p>
          </div>
        </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-yellow-500">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded-full">
            <i class="fas fa-rupee-sign text-lg"></i>
          </div>
          <div>
            <h3 class="text-gray-500 text-sm">Monthly Revenue</h3>
            <h1 class="text-2xl font-bold text-gray-800">₹<?= number_format($stats['monthly_revenue'], 2) ?></h1>
            <p class="text-sm text-green-600 mt-1"><?= $revenue_change ?> from last month</p>
            <p class="text-xs text-gray-500 mt-1">₹<?= number_format($stats['todays_revenue'], 2) ?> today</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-6">Recent Activity</h3>

      <div class="space-y-5">
        <?php foreach($recentActivity as $activity): ?>
        <div class="flex items-start space-x-4">
          <div class="w-10 h-10 flex items-center justify-center 
                    <?= $activity['type'] === 'member' ? 'bg-blue-500' : 'bg-green-500' ?> 
                    text-white rounded-full text-lg">
            <i class="<?= $activity['type'] === 'member' ? 'fas fa-user-plus' : 'fas fa-check-circle' ?>"></i>
          </div>
          <div>
            <p class="font-medium text-gray-700">
              <?= $activity['type'] === 'member' ? 'New member registered' : 'Payment received' ?>
            </p>
            <p class="text-sm text-gray-500">
              <?= htmlspecialchars($activity['name']) ?>
              <?= $activity['type'] === 'payment' ? ' (₹'.number_format($activity['amount'], 2).')' : '' ?>
            </p>
            <span class="text-xs text-gray-400">
              <?= date('M j, g:i a', strtotime($activity['date'])) ?>
              • 
              <?= $activity['type'] === 'member' ? 'Joined' : 'Payment processed' ?>
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </main>
</body>
</html>