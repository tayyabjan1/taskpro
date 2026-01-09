<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    $id = $_POST['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM project_categories WHERE id = ?");
        $stmt->execute([$id]);
        echo "Success";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Unauthorized";
}
?>
