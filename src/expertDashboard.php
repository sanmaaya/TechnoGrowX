<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "expert") {
    header("Location: login.html");
    exit();
}

$user_id=$_SESSION['user_id'];
// Fetch report status data
$statusQuery = "SELECT report_status, COUNT(*) as count FROM disease_reports where expert_id=$user_id GROUP BY report_status";
$statusResult = mysqli_query($conn, $statusQuery);
$statusData = ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
while ($row = mysqli_fetch_assoc($statusResult)) {
    $status = strtolower($row['report_status']);
    $statusData[$status] = $row['count'];
}

// Fetch crop type distribution data
$cropQuery = "SELECT crop_type, COUNT(*) as count FROM disease_reports where expert_id=$user_id GROUP BY crop_type";
$cropResult = mysqli_query($conn, $cropQuery);
$cropLabels = [];
$cropCounts = [];
$cropColors = [];
$colorPalette = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
    '#FF9F40', '#8AC24A', '#607D8B', '#E91E63', '#9C27B0'
];

$i = 0;
while ($row = mysqli_fetch_assoc($cropResult)) {
    $cropLabels[] = $row['crop_type'];
    $cropCounts[] = $row['count'];
    $cropColors[] = $colorPalette[$i % count($colorPalette)];
    $i++;
}

// Fetch reports over time data
$timeQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
              FROM disease_reports where expert_id=$user_id
              GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
              ORDER BY month";
$timeResult = mysqli_query($conn, $timeQuery);
$months = [];
$reportCounts = [];
while ($row = mysqli_fetch_assoc($timeResult)) {
    $months[] = date("M Y", strtotime($row['month']));
    $reportCounts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expert Dashboard - TechnoGrowX</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Include Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100" onclick="closeSidebarOnOutsideClick(event)">
  <div class="min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Sidebar Toggle Button (At the Top) -->
    <header class="w-full bg-green-700 text-white p-4 flex justify-between items-center md:hidden">
      <div class="flex items-center space-x-2">
        <i class="fas fa-seedling text-2xl"></i>
        <h2 class="text-xl font-bold">Expert Dashboard</h2>
      </div>
      <button class="text-white text-2xl" onclick="toggleSidebar(event)">☰</button>
    </header>

    <!-- Sidebar (Full Height) -->
    <aside class="w-64 bg-green-700 text-white p-5 hidden md:block h-screen sticky top-0">
      <div class="flex items-center space-x-3 mb-8">
        <i class="fas fa-seedling text-3xl"></i>
        <h2 class="text-2xl font-bold">Expert Dashboard</h2>
      </div>
      <ul class="space-y-3">
        <li>
          <a href="expert_home.php" class="flex items-center space-x-3 p-4 rounded-lg hover:bg-green-600 transition-all">
          <i class="fas fa-home w-5 text-center"></i>
          <span class="font-medium">Home</span>
          </a>
        </li>
        <li>
          <a href="review_report.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-clipboard-check w-5 text-center"></i>
            <span class="font-medium">Review Reports</span>
          </a>
        </li>
        <li>
          <a href="manage_solution.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-tasks w-5 text-center"></i>
            <span class="font-medium">Manage Solutions</span>
          </a>
        </li>
        <li>
          <a href="profile_expert.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span class="font-medium">Profile Settings</span>
          </a>
        </li>
        <li class="absolute bottom-5 w-[calc(100%-2.5rem)]">
          <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span class="font-medium">Logout</span>
          </a>
        </li>
      </ul>
    </aside>

    <!-- Mobile Sidebar (Full Height) -->
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-green-700 text-white p-5 transform -translate-x-full transition-transform md:hidden z-50 h-full">
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-3">
          <i class="fas fa-seedling text-2xl"></i>
          <h2 class="text-2xl font-bold">Expert Dashboard</h2>
        </div>
        <button class="text-white text-2xl" onclick="toggleSidebar(event)">✖</button>
      </div>
      <ul class="space-y-4">
        <li>
          <a href="review_report.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-clipboard-check w-5 text-center"></i>
            <span class="font-medium">Review Reports</span>
          </a>
        </li>
        <li>
          <a href="manage_solution.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-tasks w-5 text-center"></i>
            <span class="font-medium">Manage Solutions</span>
          </a>
        </li>
        <li>
          <a href="profile_expert.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span class="font-medium">Profile Settings</span>
          </a>
        </li>
        <li class="mt-8">
          <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span class="font-medium">Logout</span>
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-4 md:p-8 bg-gray-200">
      <!-- Top Navigation Bar -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-green-700">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']) ?></h1>
          <p class="text-gray-600">Here you can review farmers' reports, provide solutions, and manage your profile.</p>
        </div>
        
        <!-- Quick Action Buttons -->
        <div class="mt-4 md:mt-0 flex space-x-3">
          <a href="review_report.php" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 transition flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>New Review</span>
          </a>
          <a href="manage_solution.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center space-x-2">
            <i class="fas fa-lightbulb"></i>
            <span>Add Solution</span>
          </a>
        </div>
      </div>

      <!-- Dashboard Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 ">
        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-green-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-green-100 p-3 rounded-full">
              <i class="fas fa-clipboard-list text-green-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-green-700">Review Reports</h3>
          </div>
          <p class="text-gray-500 mb-4">View and assess reported issues from farmers.</p>
          <a href="review_report.php" class="text-green-600 font-medium hover:underline flex items-center">
            View Reports <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </a>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-blue-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-blue-100 p-3 rounded-full">
              <i class="fas fa-tasks text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-blue-700">Manage Solutions</h3>
          </div>
          <p class="text-gray-500 mb-4">Provide recommendations and updates to reports.</p>
          <a href="manage_solution.php" class="text-blue-600 font-medium hover:underline flex items-center">
            Manage Solutions <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </a>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-purple-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-purple-100 p-3 rounded-full">
              <i class="fas fa-user-cog text-purple-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-purple-700">Profile</h3>
          </div>
          <p class="text-gray-500 mb-4">View and edit your profile details.</p>
          <a href="profile_expert.php" class="text-purple-600 font-medium hover:underline flex items-center">
            View Profile <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </a>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart - Report Status -->
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-green-700">Report Status Overview</h2>
            <div class="relative">
              <select class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 90 Days</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-500 text-xs"></i>
            </div>
          </div>
          <div class="w-full h-80">
            <canvas id="statusChart"></canvas>
          </div>
        </div>

        <!-- Pie Chart - Crop Type Distribution -->
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
          <h2 class="text-xl font-bold text-green-700 mb-4">Crop Type Distribution</h2>
          <div class="w-full h-80">
            <canvas id="cropChart"></canvas>
          </div>
          <div class="mt-4 flex flex-wrap gap-2 justify-center" id="chartLegend"></div>
        </div>
      </div>
    </main>
  </div>


  <script>
    // Bar Chart - Report Status
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
      type: 'bar',
      data: {
        labels: ['Pending', 'Reviewed', 'Resolved'],
        datasets: [{
          label: 'Number of Reports',
          data: [<?= $statusData['pending'] ?>, <?= $statusData['reviewed'] ?>, <?= $statusData['resolved'] ?>],
          backgroundColor: [
            'rgba(234, 179, 8, 0.7)',
            'rgba(59, 130, 246, 0.7)',
            'rgba(34, 197, 94, 0.7)'
          ],
          borderColor: [
            'rgba(234, 179, 8, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(34, 197, 94, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Reports'
            },
            ticks: {
              precision: 0
            }
          },
          x: {
            title: {
              display: true,
              text: 'Status'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: ${context.raw}`;
              }
            }
          }
        }
      }
    });

    // pie chart  - crop type
    const cropCtx = document.getElementById('cropChart').getContext('2d');
    new Chart(cropCtx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($cropLabels) ?>,
        datasets: [{
          data: <?= json_encode($cropCounts) ?>,
          backgroundColor: <?= json_encode($cropColors) ?>,
          borderColor: '#fff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 12,
              padding: 20
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    
    function toggleSidebar(event) {
      event.stopPropagation();
      const sidebar = document.getElementById('mobileSidebar');
      sidebar.classList.toggle('-translate-x-full');
    }

    function closeSidebarOnOutsideClick(event) {
      const sidebar = document.getElementById('mobileSidebar');
      if (!sidebar.classList.contains('-translate-x-full') && !sidebar.contains(event.target)) {
        sidebar.classList.add('-translate-x-full');
      }
    }
  </script>
</body>
</html>