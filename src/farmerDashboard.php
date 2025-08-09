<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type']!="farmer") {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch report status count
$status_query = "SELECT report_status, COUNT(*) as count FROM disease_reports WHERE user_id = $user_id GROUP BY report_status";
$status_result = mysqli_query($conn, $status_query);

// Initialize status data
$status_data = [
    "pending" => 0,
    "reviewed" => 0,
    "resolved" => 0
];

while ($row = mysqli_fetch_assoc($status_result)) {
    $status = strtolower($row['report_status']);
    $status_data[$status] = $row['count'];
}

// Fetch crop type distribution
$crop_query = "SELECT crop_type, COUNT(*) as count FROM disease_reports WHERE user_id = $user_id GROUP BY crop_type";
$crop_result = mysqli_query($conn, $crop_query);
$crop_labels = [];
$crop_counts = [];
$color_palette = ['#facc15', '#3b82f6', '#22c55e', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

$i = 0;
while ($row = mysqli_fetch_assoc($crop_result)) {
    $crop_labels[] = $row['crop_type'];
    $crop_counts[] = $row['count'];
    $i++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmer Dashboard - Farmers' Portal</title>
  <link rel="stylesheet" href="./output.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100" onclick="closeSidebarOnOutsideClick(event)">
  <div class="min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Sidebar Toggle Button (At the Top) -->
    <header class="w-full bg-green-700 text-white p-4 flex justify-between items-center md:hidden">
      <div class="flex items-center space-x-2">
        <i class="fas fa-leaf text-xl"></i>
        <h2 class="text-xl font-bold">Farmer Dashboard</h2>
      </div>
      <button class="text-white text-2xl" onclick="toggleSidebar(event)">☰</button>
    </header>

    <!-- Sidebar (Full Height) -->
    <aside class="w-64 bg-green-700 text-white p-5 hidden md:block h-screen sticky top-0">
      <div class="flex items-center space-x-3 mb-8">
        <i class="fas fa-leaf text-2xl"></i>
        <h2 class="text-2xl font-bold">Farmer Dashboard</h2>
      </div>
      <ul class="space-y-2">
      <li>
          <a href="index.php" class="flex items-center space-x-3 p-4 rounded-lg hover:bg-green-600 transition-all">
          <i class="fas fa-home w-5 text-center"></i>
          <span class="font-medium">Home</span>
          </a>
        </li>
        <li>
          <a href="report.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-plus-circle w-5 text-center"></i>
            <span class="font-medium">Report Disease</span>
          </a>
        </li>
        <li>
          <a href="view_reports.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-list-alt w-5 text-center"></i>
            <span class="font-medium">View Reports</span>
          </a>
        </li>
        <li>
          <a href="farmer_profile.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
            <i class="fas fa-user w-5 text-center"></i>
            <span class="font-medium">Profile</span>
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
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-green-600 text-white p-5 transform -translate-x-full transition-transform md:hidden z-50 h-full">
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-3">
          <i class="fas fa-leaf text-xl"></i>
          <h2 class="text-2xl font-bold">Farmer Dashboard</h2>
        </div>
        <button class="text-white text-2xl" onclick="toggleSidebar(event)">✖</button>
      </div>
      <ul class="space-y-4">
      <li>
        <a href="index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-all">
        <i class="fas fa-home w-5 text-center"></i>
        <span class="font-medium">Home</span>
        </a>
      </li>
        <li>
          <a href="report.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-500 transition-all">
            <i class="fas fa-plus-circle w-5 text-center"></i>
            <span class="font-medium">Report Disease</span>
          </a>
        </li>
        <li>
          <a href="view_reports.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-500 transition-all">
            <i class="fas fa-list-alt w-5 text-center"></i>
            <span class="font-medium">View Reports</span>
          </a>
        </li>
        <li>
          <a href="farmer_profile.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-500 transition-all">
            <i class="fas fa-user w-5 text-center"></i>
            <span class="font-medium">Profile</span>
          </a>
        </li>
        <li class="mt-8">
          <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-500 transition-all">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span class="font-medium">Logout</span>
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-4 md:p-8 bg-gray-200">
      <!-- Top Navigation Bar -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-green-700">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']) ?></h1>
          <p class="text-gray-600">Here you can report any crop or livestock issues, check previous reports, and get expert advice.</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="flex flex-wrap gap-2">
          <a href="report.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>New Report</span>
          </a>
          
        </div>
      </div>

      <!-- Dashboard Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <a href="report.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-green-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-green-100 p-3 rounded-full">
              <i class="fas fa-plus-circle text-green-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-green-700">Report Disease</h3>
          </div>
          <p class="text-gray-500 mb-4">Submit a new report about your crop or livestock issues.</p>
          <div class="text-green-600 font-medium hover:underline flex items-center">
            Create Report <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </div>
        </a>
        
        <a href="view_reports.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-blue-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-blue-100 p-3 rounded-full">
              <i class="fas fa-list-alt text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-blue-700">View Reports</h3>
          </div>
          <p class="text-gray-500 mb-4">Check the status of your previous reports.</p>
          <div class="text-blue-600 font-medium hover:underline flex items-center">
            View Reports <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </div>
        </a>
        
        <a href="farmer_profile.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition border-l-4 border-purple-500">
          <div class="flex items-center space-x-4 mb-3">
            <div class="bg-purple-100 p-3 rounded-full">
              <i class="fas fa-user text-purple-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-purple-700">Your Profile</h3>
          </div>
          <p class="text-gray-500 mb-4">View and update your personal and farm information.</p>
          <div class="text-purple-600 font-medium hover:underline flex items-center">
            View Profile <i class="fas fa-arrow-right ml-2 text-sm"></i>
          </div>
        </a>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Pie Chart Card - Crop Types -->
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-green-600 flex items-center">
              <i class="fas fa-seedling mr-2"></i> Your Crop Reports
            </h2>
            <select class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-3 py-1 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
              <option>Last 30 Days</option>
              <option>Last 90 Days</option>
              <option>This Year</option>
            </select>
          </div>
          <div class="w-full" style="height: 300px;">
            <canvas id="cropChart"></canvas>
          </div>
        </div>

        <!-- Bar Chart Card - Report Status -->
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
          <h2 class="text-xl font-bold text-green-600 mb-4 flex items-center">
            <i class="fas fa-clipboard-check mr-2"></i> Report Status
          </h2>
          <div class="w-full" style="height: 300px;">
            <canvas id="statusChart"></canvas>
          </div>
          <div class="mt-4 flex flex-wrap justify-center gap-2" id="chartLegend"></div>
        </div>
      </div>
    </main>
  </div>
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Crop Type Pie Chart
    const cropCtx = document.getElementById('cropChart').getContext('2d');
    const cropChart = new Chart(cropCtx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($crop_labels) ?>,
        datasets: [{
          data: <?= json_encode($crop_counts) ?>,
          backgroundColor: ['#facc15', '#3b82f6', '#22c55e', '#ef4444', '#8b5cf6'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 20,
              font: {
                size: 12
              }
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

    // Report Status Bar Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
      type: 'bar',
      data: {
        labels: ['Pending', 'Reviewed', 'Resolved'],
        datasets: [{
          label: 'Number of Reports',
          data: [<?= $status_data['pending'] ?>, <?= $status_data['reviewed'] ?>, <?= $status_data['resolved'] ?>],
          backgroundColor: ['#facc15', '#3b82f6', '#22c55e'],
          borderColor: ['#eab308', '#2563eb', '#16a34a'],
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
              text: 'Number of Reports',
              font: {
                size: 12
              }
            },
            ticks: {
              precision: 0,
              font: {
                size: 12
              }
            }
          },
          x: {
            title: {
              display: true,
              text: 'Status',
              font: {
                size: 12
              }
            },
            ticks: {
              font: {
                size: 12
              }
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
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