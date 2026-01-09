<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/Contact.php";

    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $designation = $_POST['designation'];
        $company = $_POST['company'];

        $data = [$name, $email, $phone, $designation, $company];
        $res = add_contact($conn, $data);

        if ($res) {
            header("Location: ../collaboration.php?tab=contacts&success=Contact added successfully");
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
