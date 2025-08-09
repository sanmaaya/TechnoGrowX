<?php
include 'config.php';
session_start();

// Uncomment for production
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "admin") {
    header("Location: login.html");
    exit();
}

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
  $report_id = $_POST['report_id'];
  $new_status = $_POST['new_status'];
  $update_sql = "UPDATE disease_reports SET report_status = '$new_status' WHERE id = '$report_id'";
  mysqli_query($conn, $update_sql);
  header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page
  exit();
}

// Handle user actions (block/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
  $user_id = $_POST['user_id'];
  $action = $_POST['action'];
  
  if ($action === 'block') {
      $update_sql = "UPDATE users SET is_active = 0 WHERE id = '$user_id'";
  } elseif ($action === 'unblock') {
      $update_sql = "UPDATE users SET is_active = 1 WHERE id = '$user_id'";
  } elseif ($action === 'delete') {
      $update_sql = "DELETE FROM users WHERE id = '$user_id'";
  }
  
  if (isset($update_sql)) {
      mysqli_query($conn, $update_sql);
      header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page
      exit();
  }
}

// Handle contact form responses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
  $submission_id = $_POST['submission_id'];
  $update_sql = "UPDATE contact_messages SET is_responded = 1 WHERE id = '$submission_id'";
  mysqli_query($conn, $update_sql);
  header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page
  exit();
}
// Fetch all reports
$reports_sql = "SELECT disease_reports.id AS report_id, users.name, crop_type, symptoms, image_path, 
                disease_reports.created_at, report_status, solution 
                FROM disease_reports 
                JOIN users ON disease_reports.user_id = users.id
                ORDER BY disease_reports.created_at DESC";
$reports_result = mysqli_query($conn, $reports_sql);

// Fetch all users
$users_sql = "SELECT id, name, email, user_type, created_at, is_active FROM users";
$users_result = mysqli_query($conn, $users_sql);

// Fetch contact form submissions
$contact_sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$contact_result = mysqli_query($conn, $contact_sql);

// ANALYTICS QUERIES
// Total users count
$total_users_query = "SELECT COUNT(*) as total_users FROM users";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

// New users this month
$new_users_query = "SELECT COUNT(*) as new_users FROM users 
                   WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                   AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$new_users_result = mysqli_query($conn, $new_users_query);
$new_users = mysqli_fetch_assoc($new_users_result)['new_users'];

// Total reports count
$total_reports_query = "SELECT COUNT(*) as total_reports FROM disease_reports";
$total_reports_result = mysqli_query($conn, $total_reports_query);
$total_reports = mysqli_fetch_assoc($total_reports_result)['total_reports'];

// Reports this month
$monthly_reports_query = "SELECT COUNT(*) as monthly_reports FROM disease_reports
                         WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$monthly_reports_result = mysqli_query($conn, $monthly_reports_query);
$monthly_reports = mysqli_fetch_assoc($monthly_reports_result)['monthly_reports'];

// Response rate (percentage of responded contact submissions)
$response_rate_query = "SELECT 
                       (COUNT(CASE WHEN is_responded = 1 THEN 1 END) * 100.0 / 
                       NULLIF(COUNT(*), 0)) as response_rate 
                       FROM contact_messages";
$response_rate_result = mysqli_query($conn, $response_rate_query);
$response_rate = round(mysqli_fetch_assoc($response_rate_result)['response_rate'] ?? 0, 1);

// Recent activity (combining reports and user registrations)
$recent_activity_query = "(
    SELECT 'report' as type, users.name, disease_reports.created_at, 
           CONCAT('Submitted a ', crop_type, ' disease report') as activity
    FROM disease_reports
    JOIN users ON disease_reports.user_id = users.id
    ORDER BY disease_reports.created_at DESC
    LIMIT 5
) UNION ALL (
    SELECT 'user' as type, name, created_at, 'Registered as new user' as activity
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
) ORDER BY created_at DESC LIMIT 5";
$recent_activity_result = mysqli_query($conn, $recent_activity_query);

// User type distribution
$user_distribution_query = "SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type";
$user_distribution_result = mysqli_query($conn, $user_distribution_query);
$user_distribution = [];
while ($row = mysqli_fetch_assoc($user_distribution_result)) {
    $user_distribution[$row['user_type']] = $row['count'];
}

// Report status distribution
$report_status_query = "SELECT report_status, COUNT(*) as count FROM disease_reports GROUP BY report_status";
$report_status_result = mysqli_query($conn, $report_status_query);
$report_status = [];
while ($row = mysqli_fetch_assoc($report_status_result)) {
    $report_status[$row['report_status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - TechnoGrowX</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .mobile-menu {
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
    }
    .mobile-menu.open {
      transform: translateX(0);
    }
    .nav-link {
      transition: all 0.3s ease;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    .status-pending {
      background-color: #fef3c7;
      color: #92400e;
    }
    .status-reviewed {
      background-color: #d1fae5;
      color: #065f46;
    }
    .status-rejected {
      background-color: #fee2e2;
      color: #991b1b;
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Navigation Header -->
  <header class="bg-white shadow">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
      <div class="flex items-center">
        <button id="mobile-menu-button" class="md:hidden mr-4">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <a href="#" class="text-xl font-bold text-green-600">TechnoGrowX</a>
      </div>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <button id="user-menu-button" class="flex items-center space-x-2">
            <span class="hidden md:inline">Admin</span>
            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white">
              <i class="fas fa-user"></i>
            </div>
          </button>
          <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="mobile-menu fixed inset-y-0 left-0 z-20 w-64 bg-white shadow-lg">
    <div class="p-4 border-b">
      <h2 class="text-lg font-semibold text-gray-800">Admin Dashboard</h2>
    </div>
    <nav class="p-4">
      <ul class="space-y-2">
        <li>
          <a href="#" class="nav-link block px-4 py-2 rounded bg-green-50 text-green-700" onclick="showTab('reports')">
            <i class="fas fa-clipboard-list mr-2"></i> Disease Reports
          </a>
        </li>
        <li>
          <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('users')">
            <i class="fas fa-users mr-2"></i> User Management
          </a>
        </li>
        <li>
          <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('contact')">
            <i class="fas fa-envelope mr-2"></i> Contact Submissions
          </a>
        </li>
        <li>
          <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('analytics')">
            <i class="fas fa-chart-line mr-2"></i> Analytics
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-6">
      <!-- Sidebar -->
      <div class="hidden md:block md:w-1/4">
        <div class="bg-white rounded-lg shadow p-4 sticky top-4">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Admin Dashboard</h2>
          <nav>
            <ul class="space-y-2">
              <li>
                <a href="#" class="nav-link block px-4 py-2 rounded bg-green-50 text-green-700" onclick="showTab('reports')">
                  <i class="fas fa-clipboard-list mr-2"></i> Disease Reports
                </a>
              </li>
              <li>
                <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('users')">
                  <i class="fas fa-users mr-2"></i> User Management
                </a>
              </li>
              <li>
                <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('contact')">
                  <i class="fas fa-envelope mr-2"></i> Contact Submissions
                </a>
              </li>
              <li>
                <a href="#" class="nav-link block px-4 py-2 rounded hover:bg-gray-100" onclick="showTab('analytics')">
                  <i class="fas fa-chart-line mr-2"></i> Analytics
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <!-- Main Content -->
      <div class="md:w-3/4">
        <!-- Reports Tab -->
        <div id="reports" class="tab-content active">
          <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Disease Reports</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symptoms</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php if (mysqli_num_rows($reports_result) > 0): ?>
                    <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($report['name']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($report['crop_type']) ?></td>
                      <td class="px-6 py-4"><?= htmlspecialchars(substr($report['symptoms'], 0, 50)) ?>...</td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 rounded-full text-xs 
                          <?= $report['report_status'] == 'pending' ? 'status-pending' : '' ?>
                          <?= $report['report_status'] == 'reviewed' ? 'status-reviewed' : '' ?>
                          <?= $report['report_status'] == 'rejected' ? 'status-rejected' : '' ?>">
                          <?= ucfirst($report['report_status']) ?>
                        </span>
                      </td>
                      
                    </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="px-6 py-4 text-center text-gray-500">No reports found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Users Tab -->
        <div id="users" class="tab-content">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">User Management</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (mysqli_num_rows($users_result) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= ucfirst($user['user_type']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Blocked' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <?php if ($user['is_active']): ?>
                                        <button type="submit" name="action" value="block" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                            <i class="fas fa-ban"></i> Block
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="unblock" class="text-green-600 hover:text-green-900 mr-2">
                                            <i class="fas fa-check-circle"></i> Unblock
                                        </button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                    <input type="hidden" name="user_action" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
        
        <!-- Contact Submissions Tab -->
        <div id="contact" class="tab-content">
          <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Contact Form Submissions</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php if (mysqli_num_rows($contact_result) > 0): ?>
                    <?php while ($contact = mysqli_fetch_assoc($contact_result)): ?>
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($contact['name']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($contact['email']) ?></td>
                      <td class="px-6 py-4"><?= htmlspecialchars($contact['subject']) ?></td>
                      <td class="px-6 py-4"><?= htmlspecialchars(substr($contact['message'], 0, 50)) ?>...</td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= date('M d, Y', strtotime($contact['created_at'])) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 rounded-full text-xs <?= $contact['is_responded'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                          <?= $contact['is_responded'] ? 'Responded' : 'Pending' ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <button onclick="openReplyModal('<?= $contact['id'] ?>', '<?= htmlspecialchars($contact['email']) ?>', '<?= htmlspecialchars($contact['subject']) ?>')" class="text-blue-600 hover:text-blue-900 mr-2">
                          <i class="fas fa-reply"></i> Reply
                        </button>
                        
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="px-6 py-4 text-center text-gray-500">No contact submissions found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Enhanced Analytics Tab with Real Data -->
        <div id="analytics" class="tab-content">
          <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">System Analytics</h2>
            
            <!-- Stats Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-medium text-blue-800">Total Users</h3>
                <p class="text-2xl font-bold text-blue-600"><?= $total_users ?></p>
                <p class="text-sm text-blue-500">+<?= $new_users ?> this month</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-medium text-green-800">Total Reports</h3>
                <p class="text-2xl font-bold text-green-600"><?= $total_reports ?></p>
                <p class="text-sm text-green-500">+<?= $monthly_reports ?> this month</p>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-medium text-purple-800">Response Rate</h3>
                <p class="text-2xl font-bold text-purple-600"><?= $response_rate ?>%</p>
                <p class="text-sm text-purple-500">of contact submissions</p>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-medium text-yellow-800">Active Experts</h3>
                <p class="text-2xl font-bold text-yellow-600"><?= $user_distribution['expert'] ?? 0 ?></p>
                <p class="text-sm text-yellow-500">available for consultation</p>
              </div>
            </div>

            <!-- Charts Row -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h3 class="font-medium text-gray-800 mb-4">User Distribution</h3>
                <canvas id="userDistributionChart" height="250"></canvas>
              </div>
              <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h3 class="font-medium text-gray-800 mb-4">Report Status</h3>
                <canvas id="reportStatusChart" height="250"></canvas>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="font-medium text-gray-800 mb-4">Recent Activity</h3>
              <div class="space-y-3">
                <?php if (mysqli_num_rows($recent_activity_result) > 0): ?>
                  <?php while ($activity = mysqli_fetch_assoc($recent_activity_result)): ?>
                  <div class="flex items-start">
                    <div class="<?= $activity['type'] == 'report' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' ?> p-2 rounded-full mr-3">
                      <i class="fas <?= $activity['type'] == 'report' ? 'fa-clipboard-list' : 'fa-user-plus' ?>"></i>
                    </div>
                    <div>
                      <p class="text-sm"><span class="font-medium"><?= htmlspecialchars($activity['name']) ?></span> <?= $activity['activity'] ?></p>
                      <p class="text-xs text-gray-500"><?= date('M d, Y g:i A', strtotime($activity['created_at'])) ?></p>
                    </div>
                  </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <p class="text-gray-500">No recent activity</p>
                <?php endif; ?>
              </div>
            </div>

            
  <!-- Reply Modal -->
  <div id="replyModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Send Reply</h3>
          <form id="replyForm" method="POST">
            <input type="hidden" name="submission_id" id="submissionId">
            <div class="mb-4">
              <label for="recipientEmail" class="block text-sm font-medium text-gray-700">To:</label>
              <input type="email" id="recipientEmail" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500" readonly>
            </div>
            <div class="mb-4">
              <label for="emailSubject" class="block text-sm font-medium text-gray-700">Subject:</label>
              <input type="text" id="emailSubject" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500" readonly>
            </div>
            <div class="mb-4">
              <label for="replyMessage" class="block text-sm font-medium text-gray-700">Message:</label>
              <textarea id="replyMessage" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
          </form>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="button" onclick="submitReply()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
            Send Reply
          </button>
          <button type="button" onclick="closeReplyModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      document.getElementById('mobile-menu').classList.toggle('open');
    });

    // User menu toggle
    document.getElementById('user-menu-button').addEventListener('click', function() {
      document.getElementById('user-menu').classList.toggle('hidden');
    });

    // Tab functionality
    function showTab(tabId) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Show the selected tab
      document.getElementById(tabId).classList.add('active');
      
      // Close mobile menu if open
      document.getElementById('mobile-menu').classList.remove('open');
    }

    // Modal functions
    function openReplyModal(submissionId, email, subject) {
      document.getElementById('submissionId').value = submissionId;
      document.getElementById('recipientEmail').value = email;
      document.getElementById('emailSubject').value = 'Re: ' + subject;
      document.getElementById('replyModal').classList.remove('hidden');
    }

    function closeReplyModal() {
      document.getElementById('replyModal').classList.add('hidden');
    }

    function submitReply() {
      // In a real application, you would send the email here
      // For now, we'll just mark it as responded in the database
      document.getElementById('replyForm').submit();
      closeReplyModal();
    }

    // Initialize Charts
    document.addEventListener('DOMContentLoaded', function() {
      // User Distribution Pie Chart
      const userCtx = document.getElementById('userDistributionChart').getContext('2d');
      new Chart(userCtx, {
        type: 'pie',
        data: {
          labels: ['Farmers', 'Experts', 'Admins'],
          datasets: [{
            data: [
              <?= $user_distribution['farmer'] ?? 0 ?>,
              <?= $user_distribution['expert'] ?? 0 ?>,
              <?= $user_distribution['admin'] ?? 0 ?>
            ],
            backgroundColor: [
              'rgba(59, 130, 246, 0.7)',
              'rgba(16, 185, 129, 0.7)',
              'rgba(139, 92, 246, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

      // Report Status Doughnut Chart
      const reportCtx = document.getElementById('reportStatusChart').getContext('2d');
      new Chart(reportCtx, {
        type: 'doughnut',
        data: {
          labels: ['Pending', 'Reviewed', 'Rejected'],
          datasets: [{
            data: [
              <?= $report_status['pending'] ?? 0 ?>,
              <?= $report_status['reviewed'] ?? 0 ?>,
              <?= $report_status['resolved'] ?? 0 ?>
            ],
            backgroundColor: [
              'rgba(253, 230, 138, 0.7)',
              'rgba(134, 239, 172, 0.7)',
              'rgba(252, 165, 165, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

     
    });
  </script>
</body>
</html>