<?php
include 'config.php';
session_start();

$alertMessage = "";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type']!="farmer") {
    header("Location: login.html");
    exit();
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $filename = $_FILES["image"]["name"];
    $filetype = $_FILES["image"]["type"];
    $filesize = $_FILES["image"]["size"];
    $filetmpname = $_FILES["image"]["tmp_name"];
    $fileerror = $_FILES["image"]["error"];
    $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $user_id = $_SESSION['user_id'];
    $crop_type = $_POST['crop_type'];
    $symptoms = $_POST['symptoms'];
    $image_path = "";

    if ($fileerror === 0) {
        if ($filesize < 10000000) {
            if (in_array($fileext, ['jpg', 'jpeg', 'png'])) {
                $newFileName = uniqid("img_", true) . '.' . $fileext;
                $targetPath = "uploads/" . $newFileName;

                if (move_uploaded_file($filetmpname, $targetPath)) {
                    $image_path = $targetPath;
                    // Fetch a random expert
                    $expert_query = "SELECT user_id FROM experts ORDER BY RAND() LIMIT 1";
                    $expert_result = mysqli_query($conn, $expert_query);
                    $expert = mysqli_fetch_assoc($expert_result);
                    $assigned_expert_id = $expert['user_id'];




                    $sql = "INSERT INTO disease_reports (user_id, crop_type, symptoms, image_path,expert_id) 
                            VALUES ('$user_id', '$crop_type', '$symptoms', '$image_path','$assigned_expert_id')";

                    if (mysqli_query($conn, $sql)) {
                        $alertMessage = "✅ Report submitted successfully!";
                    } else {
                        $alertMessage = "❌ Database error: " . mysqli_error($conn);
                    }
                } else {
                    $alertMessage = "❌ Failed to move uploaded file.";
                }
            } else {
                $alertMessage = "⚠️ Only JPG, JPEG, and PNG files are allowed.";
            }
        } else {
            $alertMessage = "⚠️ File size too large. Max 10MB allowed.";
        }
    } else {
        $alertMessage = "⚠️ File upload error code: $fileerror";
    }

    mysqli_close($conn);
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Disease Report</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .mobile-menu {
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
    }
    .mobile-menu.open {
      transform: translateX(0);
    }
    .menu-overlay {
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease-in-out;
    }
    .mobile-menu.open + .menu-overlay {
      opacity: 1;
      visibility: visible;
    }
  </style>

</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- Navigation Header -->
  <header class="bg-green-700 text-white shadow-md relative z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">TechnoGrowX</h1>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center space-x-6">
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
        </a>
        <a href="view_reports.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-clipboard-check mr-2"></i> View Reports
        </a>
        <a href="farmer_profile.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-user mr-2"></i> Profile
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </nav>
      
      <!-- Mobile Menu Button -->
      <button class="md:hidden text-white text-2xl focus:outline-none" id="menu-btn" aria-label="Open menu">☰</button>
    </div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu md:hidden fixed top-0 left-0 w-64 h-screen bg-green-800 z-50 p-4 shadow-lg">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold">Menu</h2>
        <button class="text-white text-2xl focus:outline-none" id="close-btn" aria-label="Close menu">✕</button>
      </div>
      <nav class="flex flex-col space-y-4">
        <a href="farmerDashboard.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="review_reports.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-clipboard-check mr-3"></i> Review Reports
        </a>
        <a href="farmer_profile.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-user mr-3"></i> Profile
        </a>
        <a href="logout.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </nav>
    </div>
    
    <!-- Overlay when mobile menu is open -->
    <div class="menu-overlay fixed inset-0 bg-black bg-opacity-50 z-40"></div>
  </header>

  <script>
    // Mobile Menu Functions
    function toggleMobileMenu() {
      const mobileMenu = document.querySelector('.mobile-menu');
      mobileMenu.classList.toggle('open');
      document.querySelector('.menu-overlay').classList.toggle('open');
      document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    }

    function closeMobileMenu() {
      document.querySelector('.mobile-menu').classList.remove('open');
      document.querySelector('.menu-overlay').classList.remove('open');
      document.body.style.overflow = '';
    }

    // Event Listeners
    document.getElementById('menu-btn').addEventListener('click', toggleMobileMenu);
    document.getElementById('close-btn').addEventListener('click', closeMobileMenu);
    document.querySelector('.menu-overlay').addEventListener('click', closeMobileMenu);

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeMobileMenu();
      }
    });
  </script>



  <!-- Form Container -->
  <main class="flex-grow container mx-auto px-4 py-10">
    <div class="bg-gray-200 p-8 rounded-lg shadow max-w-xl mx-auto">
      <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Submit Disease Report</h2>
      <form action="report.php" method="POST" enctype="multipart/form-data" class="space-y-5">
      <div class="mb-4">
  <label for="crop_type" class="block text-sm font-medium text-gray-700 mb-1">Crop Type</label>
  <select name="crop_type" id="crop_type" required class="w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-2 focus:ring-green-500">
    <option value="" disabled selected>Select a crop type</option>
    <option value="Wheat">Wheat</option>
    <option value="Rice">Rice</option>
    <option value="Maize">Maize</option>
    <option value="Sugarcane">Sugarcane</option>
    <option value="Cotton">Cotton</option>
    <option value="Millet">Millet</option>
    <option value="Barley">Barley</option>
    <option value="Vegetables">Vegetables</option>
    <option value="Fruits">Fruits</option>
    <option value="Other">Other</option>
  </select>
</div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Symptoms</label>
          <textarea name="symptoms" rows="4" placeholder="Describe symptoms..." required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Upload Image</label>
          <input type="file" name="image"
                 accept="image/*"
                 class="w-full file:mr-4 file:py-2 file:px-4 file:border-0
                        file:text-sm file:font-semibold file:bg-green-100 file:text-green-700
                        hover:file:bg-green-200 rounded">
        </div>
        <div class="text-center">
          <button type="submit" name="submit"
                  class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition">
            Submit Report
          </button>
        </div>
      </form>
    </div>
  </main>



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
  <script>
    // Mobile Menu Toggle
    const menuBtn = document.getElementById('menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
menuBtn.addEventListener('click', () => {
  mobileMenu.classList.toggle('hidden');
});

  </script>

  <?php if (!empty($alertMessage)): ?>
  <script>
    alert("<?= addslashes($alertMessage); ?>");
    //  reset the form after alert
    window.onload = () => {
      const form = document.querySelector("form");
      if (form) form.reset();
    };
  </script>
<?php endif; ?>

</body>
</html>
