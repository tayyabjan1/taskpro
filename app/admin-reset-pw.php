<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/User.php";

    if (isset($_POST['user_id']) && isset($_POST['new_pw'])) {
        $user_id = $_POST['user_id'];
        $new_pw = password_hash($_POST['new_pw'], PASSWORD_DEFAULT);
        
        if (admin_reset_password($conn, $user_id, $new_pw)) {
            header("Location: ../user.php?success=Password reset successfully");
        } else {
            header("Location: ../user.php?error=Error resetting password");
        }
    }
} else {
    header("Location: ../login.php");
}
