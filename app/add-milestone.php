<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    if ($_SESSION['role'] != 'admin') {
         header("Location: ../index.php");
         exit();
    }
    
    if (isset($_POST['title']) && isset($_POST['project_id'])) {
        include "../DB_connection.php";
        include "Model/ProjectModules.php";

        $data = [
            'project_id' => $_POST['project_id'],
            'title' => $_POST['title'],
            'due_date' => $_POST['due_date'],
            'status' => $_POST['status']
        ];

        add_project_milestone($conn, $data);
        $em = "Milestone created successfully";
        header("Location: ../project-view.php?id=".$data['project_id']."&tab=milestones&success=$em");
        exit();
    } else {
        $em = "Unknown error occurred";
        header("Location: ../project-view.php?id=".$_POST['project_id']."&tab=milestones&error=$em");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
