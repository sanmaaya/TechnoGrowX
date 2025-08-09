<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "expert") {
    header("Location: login.php");
    exit();
}
$user_id=$_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_solution'])) {
    $report_id = $_POST['report_id'];
    $solution = $_POST['solution'];

    // Update solution and set status to resolved
    $update_sql = "UPDATE disease_reports SET solution = '$solution', report_status = 'resolved' WHERE id = '$report_id'";
    mysqli_query($conn, $update_sql);

    echo "<script>alert('Solution submitted successfully!'); window.location.href='manage_solution.php';</script>";
    exit();
}

// Fetch all disease reports
$sql = "SELECT dr.id, dr.crop_type, dr.symptoms, dr.image_path, dr.solution, dr.report_status, u.name 
        FROM disease_reports dr
        JOIN users u ON dr.user_id = u.id
         WHERE dr.expert_id = $user_id
        ORDER BY dr.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Solutions</title>
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
<body class="bg-gray-100">

  <!-- Navigation Header -->
  <header class="bg-green-700 text-white shadow-md relative z-50">
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
        <a href="profile_expert.php" class="hover:text-green-200 flex items-center">
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
        <a href="expertDashboard.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="review_report.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
          <i class="fas fa-clipboard-check mr-3"></i> Review Reports
        </a>
        <a href="profile_expert.php" class="hover:text-green-200 flex items-center py-2" onclick="closeMobileMenu()">
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

  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-green-700 mb-6">Manage Solutions</h1>

    <?php while($row = mysqli_fetch_assoc($result)): 
      $isSubmitted = !empty($row['solution']);
      $status=$row['report_status'];
    ?>
      <form method="POST" action="" class=" p-6 rounded bg-gray-200 shadow-md mb-6" id="solutionForm_<?php echo $row['id']; ?>">
        <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">

        <h2 class="text-xl font-semibold text-gray-800 mb-2">Report by: <?php echo htmlspecialchars($row['name']); ?></h2>
        <p><strong>Crop:</strong> <?php echo htmlspecialchars($row['crop_type']); ?></p>
        <p><strong>Symptoms:</strong> <?php echo htmlspecialchars($row['symptoms']); ?></p>
        <p><strong>Status:</strong> 
          <span class="px-2 py-1 rounded text-white text-sm
            <?php echo $isSubmitted ? 'bg-green-600' : 'bg-yellow-500'; ?>">
            <?php
            echo $status === 'resolved' ? 'Resolved' :
             ($status === 'reviewed' ? 'Reviewed' : 'Pending');
              ?>

          </span>
        </p>

        <?php if (!empty($row['image_path'])): ?>
          <div class="my-4">
            <img src="<?php echo $row['image_path']; ?>" alt="Reported Issue" class="w-64 h-40 object-cover rounded border">
          </div>
        <?php endif; ?>

        <label class="block mt-4 mb-2 font-medium text-gray-700">Solution:</label>
        <textarea name="solution" id="solutionTextarea_<?php echo $row['id']; ?>" rows="4"
          class="w-full p-3 border border-gray-300 rounded disabled:bg-gray-100"
          <?php echo $isSubmitted ? 'disabled' : ''; ?>><?php echo htmlspecialchars($row['solution']); ?></textarea>

        <div class="mt-4 flex gap-4">
          <button type="submit" name="submit_solution"
            id="submitBtn_<?php echo $row['id']; ?>"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded <?php echo $isSubmitted ? 'hidden' : ''; ?>">
            Submit
          </button>

          <button type="button" onclick="enableEdit('<?php echo $row['id']; ?>')"
            id="editBtn_<?php echo $row['id']; ?>"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded <?php echo !$isSubmitted ? 'hidden' : ''; ?>">
            Edit
          </button>
        </div>
      </form>
    <?php endwhile; ?>
  </div>

  <footer class="bg-gray-800 text-white py-6 mt-auto w-full">
    <div class="container mx-auto text-center">
      <p>&copy; 2025 TechnoGrowX. All rights reserved.</p>
      <div class="mt-4">
        <a href="#" class="mx-2 hover:underline">Privacy Policy</a>
        <a href="#" class="mx-2 hover:underline">Terms of Service</a>
      </div>
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
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    function enableEdit(id) {
      const textarea = document.getElementById('solutionTextarea_' + id);
      const submitBtn = document.getElementById('submitBtn_' + id);
      const editBtn = document.getElementById('editBtn_' + id);

      textarea.disabled = false;
      submitBtn.classList.remove('hidden');
      editBtn.classList.add('hidden');
    }
  </script>
</body>
</html>
