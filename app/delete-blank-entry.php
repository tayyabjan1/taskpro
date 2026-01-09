<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    
    $id = $_POST['id'];
    $table = $_POST['table'];

    // List of allowed tables for security
    $allowed_tables = ['project_goals', 'project_objectives', 'project_kpis', 'project_milestones', 'project_achievements'];

    if (in_array($table, $allowed_tables)) {
        try {
            $sql = "DELETE FROM $table WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            echo "Success";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid Table";
    }
} else {
    echo "Unauthorized";
}
?>
