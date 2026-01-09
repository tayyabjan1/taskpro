<?php
include "DB_connection.php";
try {
    $conn->exec("ALTER TABLE project_goals ADD COLUMN IF NOT EXISTS start_date DATE AFTER title");
    echo "Success";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
