<?php
include "DB_connection.php";

$sql = file_get_contents("project_modules.sql");

try {
    $conn->exec($sql);
    echo "Project modules tables created successfully!";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
