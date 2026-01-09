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
            'date_achieved' => $_POST['date_achieved']
        ];

        add_project_achievement($conn, $data);
        $em = "Achievement created successfully";
        header("Location: ../project-view.php?id=".$data['project_id']."&tab=achievements&success=$em");
        exit();
    } else {
         $em = "Unknown error occurred";
        header("Location: ../project-view.php?id=".$_POST['project_id']."&tab=achievements&error=$em");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
