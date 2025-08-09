<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = mysqli_real_escape_string($conn, $_POST["name"]);
    $email   = mysqli_real_escape_string($conn, $_POST["email"]);
    $subject = mysqli_real_escape_string($conn, $_POST["subject"]);
    $message = mysqli_real_escape_string($conn, $_POST["message"]);

    $sql = "INSERT INTO contact_messages (name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thank you! Your message has been submitted.');</script>";
        echo "<script>setTimeout(() => { window.location.href='contact.php'; }, 100);</script>";
    } else {
        echo "<script>alert('Failed to send message. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <div class="max-w-2xl mx-auto mt-12 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6 text-green-700 text-center">Contact Us</h2>

    <form action="contact.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-semibold">Name</label>
        <input type="text" name="name" required class="w-full p-2 border rounded"/>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold">Email</label>
        <input type="email" name="email" required class="w-full p-2 border rounded"/>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold">Subject</label>
        <input type="text" name="subject" required class="w-full p-2 border rounded"/>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold">Message</label>
        <textarea name="message" rows="4" required class="w-full p-2 border rounded"></textarea>
      </div>
      <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-700">
        Send Message
      </button>
    </form>
  </div>

</body>
</html>
