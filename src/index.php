<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers' Disease Diagnostic Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-white text-gray-800">

  <!-- Header -->
  <header class="bg-green-700 text-white shadow-md">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">TechnoGrowX</h1>
      <nav class="space-x-4">
        <a href="#features" class="hover:font-semibold hover:text-lg">Features</a>
        <a href="#contact" class="hover:font-semibold hover:text-lg">Contact</a>
        <?php if ($isLoggedIn): ?>
          <a href="farmerDashboard.php" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-gray-200">Dashboard</a>
        <?php else: ?>
          <a href="login.html" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-gray-200">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="bg-green-100 py-32 text-center bg-cover bg-center  bg-[url('./home-bg1.jpg')] mb-5">
  <div class="container mx-auto px-6">
    <h2 class="text-4xl md:text-5xl font-bold text-white">Diagnose. Report. Get Help.</h2>
    <p class="mt-4 text-lg md:text-xl text-white font-semibold">Empowering farmers to report crop and livestock issues easily</p>
    <div class="mt-6">
      <?php if (!$isLoggedIn): ?>
        <a href="register.php" class="bg-white text-green-800 px-6 py-3 rounded font-bold hover:text-lg">Get Started</a>
      <?php else: ?>
        <a href="report.php" class="bg-green-700 text-white px-6 py-3 rounded hover:bg-green-800">Report Now</a>
      <?php endif; ?>
    </div>
  </div>
</section>


 <!-- Features Section -->
<section id="features" class="py-16 bg-green-50">
  <div class="container mx-auto px-6">
    <h3 class="text-3xl font-bold text-center text-green-700 mb-12">Features</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <!-- Feature Card 1 -->
      <div class="bg-white p-6 rounded-2xl shadow-lg transform transition duration-300 hover:scale-105">
        <h4 class="text-2xl font-semibold text-green-600 mb-3">Easy Disease Reporting</h4>
        <p class="text-gray-600">Upload symptoms and images of affected crops or livestock quickly and easily through a simple form.</p>
      </div>

      <!-- Feature Card 2 -->
      <div class="bg-white p-6 rounded-2xl shadow-lg transform transition duration-300 hover:scale-105">
        <h4 class="text-2xl font-semibold text-green-600 mb-3">Expert Feedback</h4>
        <p class="text-gray-600">Get responses from agricultural experts who provide customized advice and solutions to your problems.</p>
      </div>

      <!-- Feature Card 3 -->
      <div class="bg-white p-6 rounded-2xl shadow-lg transform transition duration-300 hover:scale-105">
        <h4 class="text-2xl font-semibold text-green-600 mb-3">Secure Dashboard</h4>
        <p class="text-gray-600">Farmers can manage reports, view expert solutions, and track issue resolution progress from a single place.</p>
      </div>

    </div>
  </div>
</section>

  <!-- Contact Section -->
  <section id="contact" class="py-16 bg-green-100">
    <div class="container mx-auto px-6">
      <h3 class="text-3xl font-bold text-center text-green-700 mb-8">Contact Us</h3>
      <form action="contact_submit.php" method="POST" class="max-w-xl mx-auto bg-gray-100 p-8 shadow rounded">
        <input type="text" name="name" placeholder="Your Name" required class="w-full p-3 mb-4 border rounded border-gray-500" />
        <input type="email" name="email" placeholder="Your Email" required class="w-full p-3 mb-4 border rounded border-gray-500" />
        <input type="text" name="subject" placeholder="Your Subject" required class="w-full p-3 mb-4 border rounded border-gray-500" />
        <textarea name="message" placeholder="Your Message" required class="w-full p-3 mb-4 border rounded h-32 border-gray-500"></textarea>
        <button type="submit" class="bg-green-700 text-white px-6 py-2 rounded  hover:bg-green-800">Send Message</button>
      </form>
    </div>
  </section>

  
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
</body>
</html>
