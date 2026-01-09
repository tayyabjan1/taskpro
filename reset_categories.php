<?php
include "DB_connection.php";
try {
    // 1. Delete all existing categories across all projects
    $conn->exec("DELETE FROM project_categories");
    
    // 2. Define the new standard categories
    $new_categories = [
        'SALES',
        'CUSTOMERS',
        'MARKETING',
        'CONTENTs',
        'FINANCEs',
        'INVENTORY & ASSETS',
        'OPERATIONS',
        'HR / TEAM PERFORMANCE'
    ];

    // 3. Fetch all projects
    $projects = $conn->query("SELECT id FROM projects")->fetchAll();
    
    // 4. Insert the new standard categories for every project
    $stmt = $conn->prepare("INSERT INTO project_categories (project_id, name) VALUES (?, ?)");
    foreach ($projects as $p) {
        foreach ($new_categories as $cat_name) {
            $stmt->execute([$p['id'], $cat_name]);
        }
    }

    echo "Success: Categories updated for all projects.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
