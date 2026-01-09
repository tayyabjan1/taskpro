<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    $name = $_POST['name'];
    $project_id = $_POST['project_id'];

    try {
        $stmt = $conn->prepare("INSERT INTO project_statuses (project_id, name) VALUES (?, ?)");
        $stmt->execute([$project_id, $name]);
        echo "Success";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Unauthorized";
}
?>
