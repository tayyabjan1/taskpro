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
            'description' => $_POST['description'],
            'deadline' => $_POST['deadline'],
            'status' => $_POST['status']
        ];

        add_project_goal($conn, $data);
        $em = "Goal created successfully";
        header("Location: ../project-view.php?id=".$data['project_id']."&tab=goals&success=$em");
        exit();
    } else {
        $em = "Unknown error occurred";
        header("Location: ../project-view.php?id=".$_POST['project_id']."&tab=goals&error=$em");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
