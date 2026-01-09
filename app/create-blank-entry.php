<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    
    $type = $_POST['type'] ?? 'goal'; // default to goal

    try {
        if ($type == 'goal') {
            $project_id = $_POST['project_id'];
            $sql = "INSERT INTO project_goals (project_id, title, description, deadline, status) VALUES (?, 'New Goal', '', CURDATE(), 'planned')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$project_id]);
        } else if ($type == 'objective') {
            $goal_id = $_POST['goal_id'];
            $sql = "INSERT INTO project_objectives (goal_id, title, status) VALUES (?, 'New Objective', 'planned')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$goal_id]);
        }
        echo "Success";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Unauthorized";
}
?>
