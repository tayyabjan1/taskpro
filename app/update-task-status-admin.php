<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {

    if (isset($_POST['id']) && isset($_POST['status'])) {
        include "../DB_connection.php";
        include "Model/Task.php";

        $id = $_POST['id'];
        $status = $_POST['status'];
        $redirect_date = isset($_POST['redirect_date']) ? $_POST['redirect_date'] : null;

        $data = [$status, $id];
        update_task_status($conn, $data);

        $sm = "Task status updated!";
        $url = "../tasks.php?success=$sm";
        if ($redirect_date) {
            $url .= "&date=" . $redirect_date;
        }
        header("Location: $url");
        exit();
    } else {
        header("Location: ../tasks.php");
        exit();
    }

} else {
    header("Location: ../login.php?error=Access Denied");
    exit();
}
?>
