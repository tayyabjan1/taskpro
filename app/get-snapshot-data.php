<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && isset($_GET['id'])) {
    include "../DB_connection.php";
    include "Model/ProjectModules.php";

    $snapshot_id = $_GET['id'];
    $items = get_snapshot_items($conn, $snapshot_id);

    header('Content-Type: application/json');
    echo json_encode($items);
} else {
    echo json_encode([]);
}
?>
