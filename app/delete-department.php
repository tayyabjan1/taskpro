<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/Department.php";

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        delete_department($conn, $id);
        header("Location: ../user.php?tab=departments&success=Department deleted");
    }
} else {
    header("Location: ../login.php");
}
