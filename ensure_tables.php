<?php
include "DB_connection.php";
try {
    $sql = "CREATE TABLE IF NOT EXISTS meeting_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meeting_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (meeting_id) REFERENCES meeting_minutes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
    echo "Table 'meeting_comments' ensured successfully.<br>";
} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
