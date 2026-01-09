<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    include "Model/ProjectModules.php";

    $project_id = $_POST['project_id'];
    
    // Find the most recent snapshot for this project
    $stmt = $conn->prepare("SELECT id FROM project_snapshots WHERE project_id = ? ORDER BY snapshot_date DESC, created_at DESC LIMIT 1");
    $stmt->execute([$project_id]);
    $snapshot_id = $stmt->fetchColumn();

    if ($snapshot_id) {
        $result = update_snapshot_items($conn, $snapshot_id, $project_id);
        if ($result === true) {
            echo "Success";
        } else {
            echo "Error: " . $result;
        }
    } else {
        echo "No record found to update.";
    }
} else {
    echo "Unauthorized";
}
?>
