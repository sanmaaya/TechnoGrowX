<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["btn_login"])){
        $email = $_POST['email'];
        $password = md5($_POST['password']); // Match MD5 password
    
        $sql = "SELECT id, name, user_type FROM users WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            if($_SESSION['user_type']=="farmer"){
                header("Location:index.php");
            }
            else if($_SESSION['user_type']=="expert"){
                header("Location: expert_home.php");
            }
            else if($_SESSION['user_type']=="admin"){
                header("Location:admin.php");
            }
            
        } else {
            echo "<script>
            alert('Invalid email or password!');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 100);
            </script>";
        
            
        }
        
        mysqli_close($conn);
    }

}
?>
