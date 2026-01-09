<?php

function get_all_departments($conn) {
    $sql = "SELECT d.*, (SELECT COUNT(*) FROM users u WHERE u.department_id = d.id) as employee_count 
            FROM departments d ORDER BY d.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function add_department($conn, $name) {
    $sql = "INSERT INTO departments (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$name]);
}

function delete_department($conn, $id) {
    // Unassign users from this department first
    $sql_upd = "UPDATE users SET department_id = NULL WHERE department_id = ?";
    $stmt_upd = $conn->prepare($sql_upd);
    $stmt_upd->execute([$id]);

    $sql = "DELETE FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
}

function update_department($conn, $id, $name) {
    $sql = "UPDATE departments SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$name, $id]);
}
