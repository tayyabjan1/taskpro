<?php

// Get all projects with aggregated assignee names
function get_all_projects($conn) {
    $sql = "SELECT p.*, GROUP_CONCAT(u.full_name SEPARATOR ', ') as employee_names 
            FROM projects p 
            LEFT JOIN project_assignments pa ON p.id = pa.project_id
            LEFT JOIN users u ON pa.user_id = u.id 
            GROUP BY p.id
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_my_projects($conn, $user_id) {
    $sql = "SELECT p.*, (SELECT GROUP_CONCAT(u2.full_name SEPARATOR ', ') 
                          FROM project_assignments pa2 
                          JOIN users u2 ON pa2.user_id = u2.id 
                          WHERE pa2.project_id = p.id) as employee_names 
            FROM projects p 
            JOIN project_assignments pa ON p.id = pa.project_id
            WHERE pa.user_id = ? 
            GROUP BY p.id
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Get project by ID
function get_project_by_id($conn, $id) {
    $sql = "SELECT p.*, GROUP_CONCAT(u.full_name SEPARATOR ', ') as employee_names 
            FROM projects p 
            LEFT JOIN project_assignments pa ON p.id = pa.project_id
            LEFT JOIN users u ON pa.user_id = u.id 
            WHERE p.id = ?
            GROUP BY p.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_project_assignees($conn, $id) {
    $sql = "SELECT user_id FROM project_assignments WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Count total projects
function count_projects($conn) {
    $sql = "SELECT COUNT(*) as total FROM projects";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['total'];
}

// Count projects by status
function count_projects_by_status($conn, $status) {
    $sql = "SELECT COUNT(*) as total FROM projects WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status]);
    $result = $stmt->fetch();
    return $result['total'];
}

// Add new project
function add_project($conn, $data) {
    $start_raw = !empty($data['start_date']) && $data['start_date'] != '0000-00-00' ? $data['start_date'] : date('Y-m-d');
    $next_snapshot = date('Y-m-d 23:59:59', strtotime($start_raw . ' +7 days'));

    $sql = "INSERT INTO projects (name, description, start_date, deadline, status, next_snapshot_at) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['start_date'],
        $data['deadline'],
        $data['status'],
        $next_snapshot
    ]);
    
    $project_id = $conn->lastInsertId();
    if (!empty($data['assignees'])) {
        set_project_assignments($conn, $project_id, $data['assignees']);
    }
    return $project_id;
}

// Update project
function update_project($conn, $id, $data) {
    $start_raw = !empty($data['start_date']) && $data['start_date'] != '0000-00-00' ? $data['start_date'] : date('Y-m-d');
    $next_snapshot = date('Y-m-d 23:59:59', strtotime($start_raw . ' +7 days'));

    $sql = "UPDATE projects 
            SET name = ?, description = ?, start_date = ?, deadline = ?, status = ?, next_snapshot_at = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['start_date'],
        $data['deadline'],
        $data['status'],
        $next_snapshot,
        $id
    ]);

    if (isset($data['assignees'])) {
        set_project_assignments($conn, $id, $data['assignees']);
    }
    return true;
}

function set_project_assignments($conn, $project_id, $assignees) {
    // Clear old assignments
    $sql = "DELETE FROM project_assignments WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);

    // Add new ones
    if (!empty($assignees)) {
        $sql = "INSERT INTO project_assignments (project_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        foreach ($assignees as $user_id) {
            $stmt->execute([$project_id, $user_id]);
        }
    }
}

// Delete project
function delete_project($conn, $id) {
    $sql = "DELETE FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
}
