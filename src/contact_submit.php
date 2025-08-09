<?php
session_start();
include 'config.php'; // Make sure $conn is defined here with mysqli_connect

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit();
    }

    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, subject, message)
            VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Your message has been submitted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Failed to submit your message. Please try again.'); window.history.back();</script>";
    }

    mysqli_close($conn);
} else {
    header("Location: index.php");
    exit();
}
?>
