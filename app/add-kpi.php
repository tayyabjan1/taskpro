<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    if ($_SESSION['role'] != 'admin') {
         header("Location: ../index.php");
         exit();
    }
    
    if (isset($_POST['name']) && isset($_POST['project_id'])) {
        include "../DB_connection.php";
        include "Model/ProjectModules.php";

        $data = [
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target_value' => $_POST['target_value'],
            'achieved_value' => $_POST['achieved_value'],
            'unit' => $_POST['unit'],
            'status' => $_POST['status']
        ];

        add_project_kpi($conn, $data);
        $em = "KPI created successfully";
        header("Location: ../project-view.php?id=".$data['project_id']."&tab=kpis&success=$em");
        exit();
    } else {
        $em = "Unknown error occurred";
        header("Location: ../project-view.php?id=".$_POST['project_id']."&tab=kpis&error=$em");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
