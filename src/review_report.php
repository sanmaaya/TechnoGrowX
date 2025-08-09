<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "expert") {
    header("Location: login.html");
    exit();
}

$user_id=$_SESSION['user_id'];
// Handle review button click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_reviewed'])) {
    $report_id = $_POST['report_id'];
    $update_sql = "UPDATE disease_reports SET report_status = 'reviewed' WHERE id = '$report_id'";
    mysqli_query($conn, $update_sql);
}

// Fetch all reports
$sql = "SELECT disease_reports.id AS report_id, users.name, crop_type, symptoms, image_path, disease_reports.created_at, report_status 
        FROM disease_reports 
        JOIN users ON disease_reports.user_id = users.id
        WHERE disease_reports.expert_id = $user_id
        ORDER BY disease_reports.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Reports</title>
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
  <!-- Navigation Header -->
  <header class="bg-green-700 text-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">TechnoGrowX</h1>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center space-x-6">
        <a href="expertDashboard.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
        </a>
        <a href="review_report.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-clipboard-check mr-2"></i> Review Reports
        </a>
        <a href="expert_profile.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-user mr-2"></i> Profile
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </nav>
      
      <!-- Mobile Menu Button -->
      <button class="md:hidden text-white text-2xl" id="menu-btn">☰</button>
    </div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu md:hidden absolute top-0 left-0 w-64 h-screen bg-green-800 z-50 p-4 shadow-lg">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold">Menu</h2>
        <button class="text-white text-2xl" id="close-btn">✕</button>
      </div>
      <nav class="flex flex-col space-y-4">
        <a href="expert_dashboard.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="review_report.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-clipboard-check mr-3"></i> Review Reports
        </a>
        <a href="expert_profile.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-user mr-3"></i> Profile
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </nav>
    </div>
  </header>

  <div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-green-700 mb-6">Submitted Reports</h2>

    <div class="overflow-x-auto bg-gray-200 rounded-lg shadow">
      <table class="min-w-full">
        <thead class="bg-green-600 text-white">
          <tr>
            <th class="px-6 py-3 text-left">Farmer</th>
            <th class="px-6 py-3 text-left">Crop Type</th>
            <th class="px-6 py-3 text-left">Symptoms</th>
            <th class="px-6 py-3 text-left">Image</th>
            <th class="px-6 py-3 text-left">Submitted At</th>
            <th class="px-6 py-3 text-left">Status</th>
            <th class="px-6 py-3 text-left">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['crop_type']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['symptoms']) ?></td>
              <td class="px-6 py-4">
                <?php if ($row['image_path']): ?>
                  <a href="<?= $row['image_path'] ?>" target="_blank" class="text-blue-600 hover:underline flex items-center">
                    <i class="fas fa-image mr-2"></i> View
                  </a>
                <?php else: ?>
                  <span class="text-gray-500">No image</span>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4"><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td>
              <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                  <?= $row['report_status'] === 'reviewed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                  <?= ucfirst($row['report_status']) ?>
                </span>
              </td>
              <td class="px-6 py-4">
                <?php if ($row['report_status'] !== 'reviewed'): ?>
                  <form method="POST" onsubmit="return confirm('Mark this report as reviewed?');">
                    <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                    <button type="submit" name="mark_reviewed" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                      <i class="fas fa-check-circle mr-2"></i> Mark Reviewed
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-green-600 font-semibold flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> Reviewed
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Mobile Menu Toggle
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-btn');
    const mobileMenu = document.querySelector('.mobile-menu');

    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.add('open');
      document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
    });

    closeBtn.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      document.body.style.overflow = ''; // Restore scrolling
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