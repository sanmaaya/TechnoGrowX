<?php
include 'config.php';
session_start();

// Redirect if not logged in as a farmer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "farmer") {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update (excluding password)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Check if phone number already exists for another user
    $check_sql = "SELECT id FROM users WHERE phone = '$phone' AND id != '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $success_message = "Phone number already exists! Please use a different one.";
    } else {
        $update_sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE id='$user_id'";
        if (mysqli_query($conn, $update_sql)) {
            $success_message = "Profile updated successfully!";
        } else {
            $success_message = "Error updating profile: " . mysqli_error($conn);
        }
    }
}



// Fetch farmer details
$user_sql = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$farmer = mysqli_fetch_assoc($user_result);

// Fetch farmer's reports
$reports_sql = "SELECT id, crop_type, symptoms, report_status, created_at, solution 
                FROM disease_reports 
                WHERE user_id='$user_id'
                ORDER BY created_at DESC";
$reports_result = mysqli_query($conn, $reports_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer Profile - TechnoGrowX</title>
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
    .tab-button.active {
      background-color: #e5e7eb;
      font-weight: 600;
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
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
        </a>
        <a href="report.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-plus-circle mr-2"></i> Submit Report
        </a>
        <a href="farmer_profile.php" class="hover:text-green-200 flex items-center font-semibold">
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
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="report.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-plus-circle mr-3"></i> Submit Report
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

  <div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-6">
      <!-- Profile Section -->
      <div class="md:w-1/3">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="bg-green-600 p-4 text-white">
            <h2 class="text-xl font-semibold">Farmer Profile</h2>
          </div>
          <div class="p-4">
            <?php if (isset($success_message)): ?>
              <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                <?= $success_message ?>
              </div>
            <?php endif; ?>
            
            <form method="POST">
              <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($farmer['name']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
              </div>
              
              <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="email">Email</label>
                <input type="email" id="email" readonly name="email" value="<?= htmlspecialchars($farmer['email']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
              </div>
              
              <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="phone">Phone Number</label>
                <input type="tel" id="phone" required name="phone" value="<?= htmlspecialchars($farmer['phone'] ?? '') ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
              </div>
              
              <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="address">Farm Address</label>
                <textarea id="address" name="address" required rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($farmer['address'] ?? '') ?></textarea>
              </div>
              
              <div class="mb-4">
                <a href="change_password.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                  <i class="fas fa-key mr-2"></i> Change Password
                </a>
              </div>
              
              <button type="submit" name="update_profile" 
                      class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition-colors">
                Update Profile
              </button>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Reports Section -->
      <div class="md:w-2/3">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="bg-green-600 p-4 text-white">
            <h2 class="text-xl font-semibold">My Disease Reports</h2>
          </div>
          
          <div class="overflow-x-auto">
            <?php if (mysqli_num_rows($reports_result) > 0): ?>
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symptoms</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($report['crop_type']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars(substr($report['symptoms'], 0, 50)) ?>...</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs rounded-full status-<?= $report['report_status'] ?>">
                        <?= ucfirst($report['report_status']) ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="p-6 text-center text-gray-500">
                <i class="fas fa-clipboard-list text-4xl mb-2"></i>
                <p>You haven't submitted any disease reports yet.</p>
                <a href="submit_report.php" class="mt-4 inline-block bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                  Submit Your First Report
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>