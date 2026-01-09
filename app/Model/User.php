<?php 

function get_all_users($conn){
	$sql = "SELECT u.*, d.name as department_name,
            (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.status != 'completed') as pending_tasks,
            (SELECT COUNT(*) FROM projects p WHERE p.assigned_to = u.id AND p.status != 'completed') as active_projects
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.role =? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute(["employee"]);
    return $stmt->fetchAll();
}

function insert_user($conn, $data){
	$sql = "INSERT INTO users (full_name, username, password, role, department_id) VALUES(?,?,?,?,?)";
	$stmt = $conn->prepare($sql);
	return $stmt->execute([
        $data['full_name'],
        $data['username'],
        $data['password'],
        $data['role'],
        $data['department_id']
    ]);
}

function update_user($conn, $id, $data){
	$sql = "UPDATE users SET full_name=?, username=?, role=?, department_id=? WHERE id=?";
	$stmt = $conn->prepare($sql);
	return $stmt->execute([
        $data['full_name'],
        $data['username'],
        $data['role'],
        $data['department_id'],
        $id
    ]);
}

function admin_reset_password($conn, $user_id, $new_password) {
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$new_password, $user_id]);
}

function delete_user($conn, $id){
    // 1. Unassign Tasks
	$sql_tasks = "UPDATE tasks SET assigned_to = NULL WHERE assigned_to = ?";
	$stmt_tasks = $conn->prepare($sql_tasks);
	$stmt_tasks->execute([$id]);

    // 2. Unassign Projects (Remove from project_assignments)
	$sql_proj = "DELETE FROM project_assignments WHERE user_id = ?";
	$stmt_proj = $conn->prepare($sql_proj);
	$stmt_proj->execute([$id]);

    // 3. Clear Notifications
	$sql_notif = "DELETE FROM notifications WHERE recipient = ?";
	$stmt_notif = $conn->prepare($sql_notif);
	$stmt_notif->execute([$id]);

    // 4. Finally Delete User
	$sql = "DELETE FROM users WHERE id=? AND role=?";
	$stmt = $conn->prepare($sql);
	return $stmt->execute([$id, 'employee']);
}

function get_user_by_id($conn, $id){
	$sql = "SELECT u.*, d.name as department_name 
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id 
            WHERE u.id =? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);
	return $stmt->fetch();
}

function update_profile($conn, $data){
	$sql = "UPDATE users SET full_name=?,  password=? WHERE id=? ";
	$stmt = $conn->prepare($sql);
	return $stmt->execute($data);
}

function count_users($conn){
	$sql = "SELECT id FROM users WHERE role='employee'";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	return $stmt->rowCount();
}