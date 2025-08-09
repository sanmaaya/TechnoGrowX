<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type']!="farmer") {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM disease_reports WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Reports - Farmers' Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .mobile-menu {
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
    }
    .mobile-menu.open {
      transform: translateX(0);
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Header with Mobile Menu -->
  <header class="bg-green-700 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
      <h1 class="text-xl md:text-2xl font-bold">TechnoGrowX</h1>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center space-x-4">
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
        </a>
        <a href="report.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-plus-circle mr-2"></i> New Report
        </a>
        <a href="farmer_profile.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-user mr-2"></i> Profile
        </a>
        <a href="logout.php" class="bg-white text-green-700 px-3 py-1 rounded text-sm hover:bg-gray-200">
          Logout
        </a>
      </nav>
      
      <!-- Mobile Menu Button -->
      <button class="md:hidden text-white text-2xl" id="menu-btn">☰</button>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu md:hidden fixed inset-y-0 left-0 w-64 bg-green-800 text-white p-5 z-50">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold">Menu</h2>
        <button class="text-white text-2xl" id="close-btn">✕</button>
      </div>
      <nav class="flex flex-col space-y-4">
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="report.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-plus-circle mr-3"></i> New Report
        </a>
        <a href="view_reports.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-list-alt mr-3"></i> View Reports
        </a>
        <a href="farmer_profile.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-user mr-3"></i> Profile
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </nav>
    </div>
  </header>

  <main class="container mx-auto px-4 py-6 bg-gray-200">
    <h2 class="text-2xl md:text-3xl font-bold text-green-700 mb-6">My Disease Reports</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-semibold text-gray-800">
                  <?= htmlspecialchars($row['crop_type']) ?>
                </h3>
                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                  <?= $row['report_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                     ($row['report_status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                  <?= ucfirst($row['report_status']) ?>
                </span>
              </div>
              
              <p class="text-gray-600 mb-3"><strong>Symptoms:</strong> <?= htmlspecialchars($row['symptoms']) ?></p>
              
              <?php if ($row['image_path']) : ?>
                <img src="<?= $row['image_path'] ?>" alt="Disease image" class="w-full h-48 object-cover rounded mb-3">
              <?php endif; ?>
              
              <div class="text-sm text-gray-500 mb-3">
                <i class="far fa-calendar-alt mr-1"></i> <?= date("F j, Y", strtotime($row['created_at'])) ?>
              </div>
              
              <div class="border-t pt-3">
                <h4 class="font-medium text-green-700 mb-1">Expert Solution:</h4>
                <p class="text-gray-700"><?= $row['solution'] ? htmlspecialchars($row['solution']) : 'Pending expert review...' ?></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-clipboard-list text-5xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Reports Found</h3>
        <p class="text-gray-600 mb-4">You haven't submitted any disease reports yet.</p>
        <a href="report.php" class="inline-block bg-green-700 text-white px-6 py-2 rounded-lg hover:bg-green-800">
          <i class="fas fa-plus-circle mr-2"></i> Submit Your First Report
        </a>
      </div>
    <?php endif; ?>
  </main>

  <script>
    // Mobile Menu Toggle
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.add('open');
      document.body.style.overflow = 'hidden';
    });

    closeBtn.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      document.body.style.overflow = '';
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!mobileMenu.contains(e.target) && e.target !== menuBtn) {
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      }
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  </script>
</body>
</html>