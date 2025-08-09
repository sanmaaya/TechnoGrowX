<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "expert") {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch expert details
$sql = "SELECT u.*, e.* FROM users u LEFT JOIN experts e ON u.id = e.user_id WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $sql);
$expert = mysqli_fetch_assoc($result);


// Get report statistics

// Handle form submissions
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $bio = mysqli_real_escape_string($conn, $_POST['bio']);
        $experience = mysqli_real_escape_string($conn, $_POST['experience']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
        
        // Update users table
        $update_user_sql = "UPDATE users SET name = '$name' WHERE id = '$user_id'";
        mysqli_query($conn, $update_user_sql);
        
        // Update experts table
        $update_expert_sql = "UPDATE experts SET 
                            bio = '$bio',
                            experience = '$experience',
                            address = '$address',
                            specialization = '$specialization'
                            WHERE user_id = '$user_id'";
        mysqli_query($conn, $update_expert_sql);
        
        $_SESSION['user_name'] = $name;
        $message = "Profile updated successfully!";
        
        // Refresh data
        $result = mysqli_query($conn, $sql);
        $expert = mysqli_fetch_assoc($result);
    }
    
    if (isset($_POST['upload_photo'])) {
        // Handle photo upload
        $target_dir = "uploads/expert_photos/";
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES["photo"]["size"] > 500000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $message = "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $update_photo_sql = "UPDATE experts SET photo = '$target_file' WHERE user_id = '$user_id'";
                mysqli_query($conn, $update_photo_sql);
                $message = "The file ". htmlspecialchars(basename($_FILES["photo"]["name"])). " has been uploaded.";
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expert Profile | Farmers' Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .rating-stars {
      color: #fbbf24;
    }
    .profile-photo {
      transition: all 0.3s ease;
    }
    .profile-photo:hover {
      transform: scale(1.05);
    }
    .stat-card {
      transition: transform 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-green-700 text-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Farmers' Portal</h1>
      <nav>
        <a href="expertDashboard.php" class="hover:text-green-200 mx-2"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
        <a href="logout.php" class="hover:text-green-200 mx-2"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
      </nav>
    </div>
  </header>

  <div class="container mx-auto px-4 py-8">
    <!-- Success Message -->
    <?php if (!empty($message)): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
        <p><?php echo $message; ?></p>
      </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Left Column - Profile Card -->
      <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- Profile Header -->
          <div class="bg-green-700 p-6 text-center relative">
            <div class="absolute top-4 right-4">
              <span class="bg-white text-green-700 px-3 py-1 rounded-full text-sm font-semibold shadow-sm">Expert</span>
            </div>
            
            <!-- Profile Photo -->
            <div class="relative mx-auto w-32 h-32 rounded-full bg-white border-4 border-white shadow-md overflow-hidden profile-photo">
              <?php if (!empty($expert['photo'])): ?>
                <img src="<?php echo htmlspecialchars($expert['photo']); ?>" alt="Profile Photo" class="w-full h-full object-cover">
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                  <i class="fas fa-user-tie text-5xl text-gray-500"></i>
                </div>
              <?php endif; ?>
              <form method="post" enctype="multipart/form-data" class="absolute inset-0 opacity-0 hover:opacity-100 transition-opacity">
                <label class="w-full h-full flex items-center justify-center cursor-pointer bg-black bg-opacity-50">
                  <i class="fas fa-camera text-white text-2xl"></i>
                  <input type="file" name="photo" class="hidden" onchange="this.form.submit()">
                  <input type="hidden" name="upload_photo" value="1">
                </label>
              </form>
            </div>
            
            <h2 class="text-xl font-bold text-white mt-4"><?= htmlspecialchars($expert['name']) ?></h2>
            
          </div>
          
          <!-- Profile Details -->
          <div class="p-6">
            <div class="mb-6">
              <h3 class="font-bold text-lg mb-3 text-gray-800 border-b pb-2">About Me</h3>
              <p class="text-gray-600"><?= !empty($expert['bio']) ? nl2br(htmlspecialchars($expert['bio'])) : 'No bio added yet.' ?></p>
            </div>
            
            <div class="space-y-4">
              <div>
                <div class="flex items-center text-gray-700">
                  <i class="fas fa-map-marker-alt text-green-600 mr-3 w-5"></i>
                  <span><?= !empty($expert['address']) ? htmlspecialchars($expert['address']) : 'Address not specified' ?></span>
                </div>
              </div>
              
              <div>
                <div class="flex items-center text-gray-700">
                  <i class="fas fa-briefcase text-green-600 mr-3 w-5"></i>
                  <span><?= !empty($expert['experience']) ? htmlspecialchars($expert['experience']) : 'Experience not specified' ?></span>
                </div>
              </div>
              
              <div>
                <div class="flex items-center text-gray-700">
                  <i class="fas fa-certificate text-green-600 mr-3 w-5"></i>
                  <span><?= !empty($expert['specialization']) ? htmlspecialchars($expert['specialization']) : 'Specialization not specified' ?></span>
                </div>
              </div>
              
              <div>
                <div class="flex items-center text-gray-700">
                  <i class="fas fa-envelope text-green-600 mr-3 w-5"></i>
                  <span><?= htmlspecialchars($expert['email']) ?></span>
                </div>
              </div>
            </div>
            
            <div class="mt-6">
              <h3 class="font-bold text-lg mb-3 text-gray-800 border-b pb-2">Specializations</h3>
              <div class="flex flex-wrap gap-2">
                <?php
                $specializations = !empty($expert['specialization']) ? explode(',', $expert['specialization']) : ['Not specified'];
                foreach ($specializations as $spec): ?>
                  <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm"><?= trim(htmlspecialchars($spec)) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right Column - Edit Profile and Stats -->
      <div class="w-full lg:w-2/3 space-y-6">
        <!-- Edit Profile Card -->
        <div class="bg-gray-200 rounded-lg shadow-md p-6">
          <h2 class="text-2xl font-bold text-green-700 mb-6 border-b pb-2">Edit Profile</h2>
          
          <form method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-semibold text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($expert['name']) ?>" 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
              </div>
              
              <div>
                <label class="block font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" value="<?= htmlspecialchars($expert['email']) ?>" 
                       class="w-full p-3 border rounded-lg bg-gray-100 cursor-not-allowed" readonly>
              </div>
              
              <div class="md:col-span-2">
                <label class="block font-semibold text-gray-700 mb-2">Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($expert['address']) ?>" 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                       placeholder="Enter your full address">
              </div>
              
              <div class="md:col-span-2">
                <label class="block font-semibold text-gray-700 mb-2">Bio</label>
                <textarea name="bio" rows="3" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          placeholder="Tell farmers about yourself and your expertise"><?= htmlspecialchars($expert['bio']) ?></textarea>
              </div>
              
              <div class="md:col-span-2">
                <label class="block font-semibold text-gray-700 mb-2">Experience</label>
                <textarea name="experience" rows="3" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          placeholder="Describe your experience and qualifications"><?= htmlspecialchars($expert['experience']) ?></textarea>
              </div>
              
              <div class="md:col-span-2">
                <label class="block font-semibold text-gray-700 mb-2">Specialization (comma separated)</label>
                <input type="text" name="specialization" value="<?= htmlspecialchars($expert['specialization']) ?>" 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                       placeholder="e.g. Crop diseases, Soil health, Pest control">
              </div>
            </div>
            
            <div class="flex justify-between items-center mt-8">
              <button type="submit" name="update_profile" 
                      class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                <i class="fas fa-save mr-2"></i> Save Profile
              </button>
              
              <a href="change_password.php" class="text-blue-600 hover:underline flex items-center">
                <i class="fas fa-key mr-2"></i> Change Password
              </a>
            </div>
          </form>
        </div>
        
        
            
            
</body>
</html>