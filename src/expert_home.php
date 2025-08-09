<?php
session_start();
include 'config.php';

// Check if expert is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_type'] == "expert";
$expert_id = $isLoggedIn ? $_SESSION['user_id'] : null;
$userName = $isLoggedIn ? $_SESSION['user_name'] : 'Expert User';

// Fetch data if logged in
if ($isLoggedIn) {
    // Fetch report statistics
    $stats_query = "SELECT 
        COUNT(*) as total_reports,
        SUM(CASE WHEN report_status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
        SUM(CASE WHEN report_status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_reports,
        SUM(CASE WHEN report_status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports
        FROM disease_reports 
        WHERE expert_id = '$expert_id'";
    $stats_result = mysqli_query($conn, $stats_query);
    $stats = mysqli_fetch_assoc($stats_result);

    // Fetch recent reports (last 3)
    $reports_query = "SELECT dr.id, u.name as farmer_name, dr.crop_type, dr.report_status, 
                     DATE_FORMAT(dr.created_at, '%b %d, %Y') as report_date
                     FROM disease_reports dr
                     JOIN users u ON dr.user_id = u.id
                     WHERE dr.expert_id = '$expert_id'
                     ORDER BY dr.created_at DESC
                     LIMIT 3";
    $reports_result = mysqli_query($conn, $reports_query);
    $recent_reports = [];
    while ($row = mysqli_fetch_assoc($reports_result)) {
        $recent_reports[] = $row;
    }
} else {
    // Dummy data for non-logged in users
    $stats = [
        'total_reports' => 42,
        'pending_reports' => 8,
        'reviewed_reports' => 22,
        'resolved_reports' => 12
    ];
    
    $recent_reports = [
        [
            'farmer_name' => 'John Doe',
            'crop_type' => 'Tomato',
            'disease_type' => 'Blight',
            'report_status' => 'pending',
            'report_date' => date('M d, Y')
        ],
        [
            'farmer_name' => 'Jane Smith',
            'crop_type' => 'Wheat',
            'disease_type' => 'Rust',
            'report_status' => 'reviewed',
            'report_date' => date('M d, Y', strtotime('-1 day'))
        ],
        [
            'farmer_name' => 'Robert Johnson',
            'crop_type' => 'Corn',
            'disease_type' => 'Smut',
            'report_status' => 'resolved',
            'report_date' => date('M d, Y', strtotime('-3 days'))
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expert Portal - Farmers' Disease Diagnostic System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    .stat-card {
      transition: transform 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .report-card {
      transition: all 0.3s ease;
    }
    .report-card:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class="bg-gray-50">

<!-- Header -->
<header class="bg-green-700 text-white shadow-md">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <i class="fas fa-leaf text-2xl"></i>
      <h1 class="text-xl md:text-2xl font-bold">TechnoGrowX</h1>
    </div>
    
    <nav class="hidden md:flex items-center space-x-6">
      <a href="expertDashboard.php" class="hover:text-green-200 flex items-center space-x-1">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
      </a>
      <a href="#reports" class="hover:text-green-200 flex items-center space-x-1">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
      </a>
      <a href="#resources" class="hover:text-green-200 flex items-center space-x-1">
        <i class="fas fa-book"></i>
        <span>Resources</span>
      </a>
      <?php if ($isLoggedIn): ?>
        <a href="profile_expert.php" class="hover:text-green-200 flex items-center space-x-1">
          <i class="fas fa-user"></i>
          <span>Profile</span>
        </a>
        <a href="logout.php" class="bg-white text-green-700 px-3 py-1 rounded text-sm hover:bg-gray-200 flex items-center space-x-1">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      <?php else: ?>
        <a href="login.html" class="bg-white text-green-700 px-3 py-1 rounded text-sm hover:bg-gray-200 flex items-center space-x-1">
          <i class="fas fa-sign-in-alt"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>
    </nav>
    
    <button class="md:hidden text-white text-2xl" id="menu-btn">☰</button>
  </div>

  <!-- Mobile Menu -->
  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-green-800 px-4 py-2">
    <a href="expertDashboard.php" class="block py-2 hover:text-green-200">Dashboard</a>
    <a href="#reports" class="block py-2 hover:text-green-200">Reports</a>
    <a href="#resources" class="block py-2 hover:text-green-200">Resources</a>
    <?php if ($isLoggedIn): ?>
      <a href="profile_expert.php" class="block py-2 hover:text-green-200">Profile</a>
      <a href="logout.php" class="block py-2 hover:text-green-200">Logout</a>
    <?php else: ?>
      <a href="login.html" class="block py-2 hover:text-green-200">Login</a>
    <?php endif; ?>
  </div>

<!-- Hero Section -->
<section id="dashboard" class="bg-[url('./expert_home4.jpg')] bg-cover bg-center  text-white py-12 md:py-20 text-center">
  <div class="container mx-auto px-4">
    <h2 class="text-3xl md:text-4xl font-bold mb-4">Welcome, <?= htmlspecialchars($userName) ?></h2>
    <p class="text-lg font-semibold md:text-xl mb-6"><?= $isLoggedIn ? 'Manage your expert dashboard' : 'Login to access expert features' ?></p>
    <?php if ($isLoggedIn): ?>
      <a href="review_report.php" class="inline-block bg-white text-green-800 font-bold px-6 py-2 rounded-lg  hover:text-lg">
        Review Reports
      </a>
    <?php else: ?>
      <a href="login.html" class="inline-block bg-white text-green-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-100">
        Expert Login
      </a>
    <?php endif; ?>
  </div>
</section>

<!-- Stats Section -->
<section class="py-8 md:py-12 bg-white">
  <div class="container mx-auto px-4">
    <h3 class="text-2xl md:text-3xl font-bold text-center text-green-700 mb-8">Report Statistics</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
      <div class="stat-card bg-blue-200 p-4 md:p-6 rounded-lg shadow text-center border-t-4 border-blue-500">
        <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2"><?= $stats['total_reports'] ?></div>
        <h4 class="text-lg font-semibold text-gray-700">Total Reports</h4>
      </div>
      <div class="stat-card bg-green-200 p-4 md:p-6 rounded-lg shadow text-center border-t-4 border-green-500">
        <div class="text-3xl md:text-4xl font-bold text-green-600 mb-2"><?= $stats['reviewed_reports'] ?></div>
        <h4 class="text-lg font-semibold text-gray-700">Reviewed</h4>
      </div>
      <div class="stat-card bg-purple-200 p-4 md:p-6 rounded-lg shadow text-center border-t-4 border-purple-500">
        <div class="text-3xl md:text-4xl font-bold text-purple-600 mb-2"><?= $stats['resolved_reports'] ?></div>
        <h4 class="text-lg font-semibold text-gray-700">Resolved</h4>
      </div>
    </div>
  </div>
</section>

<!-- Recent Reports Section -->
<section id="reports" class="py-8 md:py-12 bg-gray-100">
  <div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl md:text-3xl font-bold text-green-700">Recent Reports</h3>
      <?php if ($isLoggedIn): ?>
        <a href="review_report.php" class="text-green-700 hover:underline text-sm md:text-base">
          View All <i class="fas fa-arrow-right ml-1"></i>
        </a>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
      <?php foreach ($recent_reports as $report): ?>
        <div class="report-card bg-white p-4 rounded-lg shadow border-l-4 
            <?= $report['report_status'] === 'pending' ? 'border-yellow-500' : 
               ($report['report_status'] === 'reviewed' ? 'border-blue-500' : 'border-green-500') ?>">
          <div class="flex justify-between items-start mb-3">
            <div>
              <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($report['crop_type']) ?></h4>
              <p class="text-sm text-gray-600"><?= htmlspecialchars($report['disease_type'] ?? '') ?></p>
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-semibold 
              <?= $report['report_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                 ($report['report_status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
              <?= ucfirst($report['report_status']) ?>
            </span>
          </div>
          <div class="text-sm text-gray-500 mb-3">
            <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($report['farmer_name']) ?>
          </div>
          <div class="text-xs text-gray-400">
            <i class="far fa-clock mr-1"></i> <?= $report['report_date'] ?>
          </div>
          <?php if ($isLoggedIn): ?>
            <div class="mt-4">
              <a href="report_detail.php?id=<?= $report['id'] ?>" 
                 class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs md:text-sm px-3 py-1 rounded">
                View Details
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Resources Section -->
<section id="resources" class="py-8 md:py-12 bg-white">
  <div class="container mx-auto px-4">
    <h3 class="text-2xl md:text-3xl font-bold text-center text-green-700 mb-8">Expert Resources</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
      <div class="bg-gray-50 p-4 md:p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <div class="text-green-600 text-2xl md:text-3xl mb-3"><i class="fas fa-book-open"></i></div>
        <h4 class="text-lg md:text-xl font-semibold mb-2">Disease Handbook</h4>
        <p class="text-gray-600 text-sm md:text-base mb-3">Comprehensive guide to agricultural diseases with identification and treatment methods.</p>
        <a href="#" class="text-green-700 hover:underline text-sm md:text-base">Access Resource →</a>
      </div>
      <div class="bg-gray-50 p-4 md:p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <div class="text-green-600 text-2xl md:text-3xl mb-3"><i class="fas fa-video"></i></div>
        <h4 class="text-lg md:text-xl font-semibold mb-2">Training Videos</h4>
        <p class="text-gray-600 text-sm md:text-base mb-3">Video tutorials on disease diagnosis and modern treatment techniques.</p>
        <a href="#" class="text-green-700 hover:underline text-sm md:text-base">Watch Videos →</a>
      </div>
      <div class="bg-gray-50 p-4 md:p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <div class="text-green-600 text-2xl md:text-3xl mb-3"><i class="fas fa-comments"></i></div>
        <h4 class="text-lg md:text-xl font-semibold mb-2">Expert Forum</h4>
        <p class="text-gray-600 text-sm md:text-base mb-3">Connect with other experts to discuss challenging cases and solutions.</p>
        <a href="#" class="text-green-700 hover:underline text-sm md:text-base">Join Discussion →</a>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-auto w-full">
    <div class="container mx-auto text-center">
      <p>&copy; 2025 TechnoGrowX. All rights reserved.</p>
      <div class="mt-4">
        <a href="#" class="mx-2 hover:underline">Privacy Policy</a>
        <a href="#" class="mx-2 hover:underline">Terms of Service</a>
      </div>
      <!-- Social Media Icons -->
      <div class="flex justify-center mt-4 space-x-4">
        <a href="https://facebook.com" target="_blank" class="text-white hover:text-blue-500">
          <i class="fab fa-facebook-f text-xl"></i>
        </a>
        <a href="https://twitter.com" target="_blank" class="text-white hover:text-blue-400">
          <i class="fab fa-twitter text-xl"></i>
        </a>
        <a href="https://instagram.com" target="_blank" class="text-white hover:text-pink-500">
          <i class="fab fa-instagram text-xl"></i>
        </a>
        <a href="https://linkedin.com" target="_blank" class="text-white hover:text-blue-600">
          <i class="fab fa-linkedin-in text-xl"></i>
        </a>
        <a href="https://youtube.com" target="_blank" class="text-white hover:text-red-500">
          <i class="fab fa-youtube text-xl"></i>
        </a>
      </div>
    </div>
  </footer> 
<!-- Mobile Menu Script -->
<script>
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  
  menuBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });
</script>

</body>
</html>
