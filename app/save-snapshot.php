<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    include "Model/ProjectModules.php";

    $project_id = $_POST['project_id'];
    $snapshot_date = date('Y-m-d'); 

    $result = create_project_snapshot($conn, $project_id, $snapshot_date);
    
    if ($result === true) {
        // Advance the next auto-report deadline by 1 week
        $stmt = $conn->prepare("SELECT next_snapshot_at FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $current_deadline = $stmt->fetchColumn();
        
        if ($current_deadline) {
            $next_target = date('Y-m-d 23:59:59', strtotime($current_deadline . ' +7 days'));
            
            $upd = $conn->prepare("UPDATE projects SET next_snapshot_at = ? WHERE id = ?");
            $upd->execute([$next_target, $project_id]);
        }

        echo "Success";
    } else {
        echo "Error: " . $result;
    }
} else {
    echo "Unauthorized";
}
?>
