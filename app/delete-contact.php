<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/Contact.php";

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $res = delete_contact($conn, $id);

        if ($res) {
            header("Location: ../collaboration.php?tab=contacts&success=Contact deleted successfully");
            exit();
        } else {
            header("Location: ../collaboration.php?tab=contacts&error=Unknown error occurred");
            exit();
        }
    } else {
        header("Location: ../contacts.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
