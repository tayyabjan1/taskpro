<?php
include "DB_connection.php";
try {
    // 1. Create project_categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS `project_categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `project_id` INT(11) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // 2. Add category_id to project_goals
    $conn->exec("ALTER TABLE project_goals ADD COLUMN IF NOT EXISTS category_id INT(11) AFTER project_id");
    $conn->exec("ALTER TABLE project_goals ADD FOREIGN KEY (category_id) REFERENCES project_categories(id) ON DELETE SET NULL");

    // 3. Insert some default categories for the project if none exist
    $projects = $conn->query("SELECT id FROM projects")->fetchAll();
    foreach ($projects as $p) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM project_categories WHERE project_id = ?");
        $stmt->execute([$p['id']]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO project_categories (project_id, name) VALUES (?, 'General'), (?, 'Operations'), (?, 'Marketing')");
            $stmt->execute([$p['id'], $p['id'], $p['id']]);
        }
    }

    echo "Success";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
