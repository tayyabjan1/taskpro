<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/Department.php";

    if (isset($_POST['dept_name'])) {
        $id = $_POST['dept_id'];
        $name = $_POST['dept_name'];

        if (empty($id)) {
            add_department($conn, $name);
            $msg = "Department created";
        } else {
            update_department($conn, $id, $name);
            $msg = "Department updated";
        }
        header("Location: ../user.php?tab=departments&success=$msg");
    }
} else {
    header("Location: ../login.php");
}
