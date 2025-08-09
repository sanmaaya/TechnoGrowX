<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Verify current password
    $sql = "SELECT password FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if (md5($current_password) !== $user['password']) {
        $error = "Current password is incorrect";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Update password
        $hashed_password = md5($new_password);
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error updating password: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - Farmers' Portal</title>
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
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Header with Mobile Menu -->
  <header class="bg-green-700 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
      <h1 class="text-xl md:text-2xl font-bold">Change Password</h1>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center space-x-4">
        <a href="<?= $user_type === 'farmer' ? 'farmerDashboard.php' : 'expertDashboard.php' ?>" 
           class="hover:text-green-200 flex items-center">
          <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
        </a>
        <a href="<?= $user_type === 'farmer' ? 'farmer_profile.php' : 'expert_profile.php' ?>" 
           class="hover:text-green-200 flex items-center">
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
        <a href="<?= $user_type === 'farmer' ? 'farmerDashboard.php' : 'expertDashboard.php' ?>" 
           class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="<?= $user_type === 'farmer' ? 'farmer_profile.php' : 'expert_profile.php' ?>" 
           class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-user mr-3"></i> Profile
        </a>
        <a href="change_password.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-key mr-3"></i> Change Password
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center py-2">
          <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </nav>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold text-green-700 mb-6 text-center">Change Your Password</h2>
      
      <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
          <p><?= htmlspecialchars($error) ?></p>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
          <p><?= htmlspecialchars($success) ?></p>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-6">
        <div class="relative">
          <label for="current_password" class="block text-gray-700 mb-2">Current Password</label>
          <input type="password" id="current_password" name="current_password" required
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-15 ">
          <i class="fas fa-eye-slash password-toggle mx-2 my-4" onclick="togglePassword('current_password', this)"></i>
        </div>
        
        <div class="relative">
          <label for="new_password" class="block text-gray-700 mb-2">New Password</label>
          <input type="password" id="new_password" name="new_password" required minlength="8"
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-10">
          <i class="fas fa-eye-slash password-toggle mx-2 my-3" onclick="togglePassword('new_password', this)"></i>
          <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
        </div>
        
        <div class="relative">
          <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                 class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-10">
          <i class="fas fa-eye-slash password-toggle mx-2 my-4" onclick="togglePassword('confirm_password', this)"></i>
        </div>
        
        <button type="submit" 
                class="w-full bg-green-700 text-white py-3 px-4 rounded-lg hover:bg-green-800 transition duration-200 font-medium">
          Change Password
        </button>
      </form>
    </div>
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

    // Password toggle functionality
    function togglePassword(fieldId, icon) {
      const field = document.getElementById(fieldId);
      if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      } else {
        field.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      }
    }
  </script>
</body>
</html>