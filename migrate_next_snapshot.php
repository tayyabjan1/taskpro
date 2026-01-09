<?php
include "DB_connection.php";
try {
    $conn->exec("ALTER TABLE projects ADD COLUMN IF NOT EXISTS next_snapshot_at DATETIME AFTER deadline");
    echo "Migration Success: Column 'next_snapshot_at' added to 'projects' table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Migration Note: Column 'next_snapshot_at' already exists.";
    } else {
        echo "Migration Error: " . $e->getMessage();
    }
}
?>
