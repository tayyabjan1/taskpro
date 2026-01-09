<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    include "../DB_connection.php";
    
    $id = $_POST['id'];
    $table = $_POST['table'];
    $column = $_POST['column'];
    $value = trim($_POST['value']);

    // Access Security Check
    if ($_SESSION['role'] == 'employee') {
        // If updating a project-related module, verify assignment
        $project_id = null;
        if ($table === 'projects') {
            $project_id = $id;
        } elseif (in_array($table, ['project_goals', 'project_kpis', 'project_milestones', 'project_achievements'])) {
            $stmt = $conn->prepare("SELECT project_id FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $project_id = $stmt->fetchColumn();
        }

        if ($project_id) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM project_assignments WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$project_id, $_SESSION['id']]);
            if ($stmt->fetchColumn() == 0) {
                echo "Unauthorized"; exit();
            }
        }
    }

    // List of allowed tables and columns for security
    $allowed_tables = ['projects', 'project_goals', 'project_objectives', 'project_kpis', 'project_milestones', 'project_achievements'];
    $allowed_columns = ['name', 'description', 'status', 'deadline', 'start_date', 'category_id', 'title', 'target_value', 'achieved_value', 'unit', 'due_date', 'date_achieved', 'metrics', 'target', 'achievement', 'results'];

    if (in_array($table, $allowed_tables) && in_array($column, $allowed_columns)) {
        try {
            $sql = "UPDATE $table SET $column = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$value, $id]);

            // Special logic for projects: if start_date changes, update next_snapshot_at
            if ($table === 'projects' && $column === 'start_date') {
                $stmt = $conn->prepare("SELECT start_date FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                $proj = $stmt->fetch();
                
                if ($proj) {
                    $start_raw = !empty($proj['start_date']) && $proj['start_date'] != '0000-00-00' ? $proj['start_date'] : date('Y-m-d');
                    $next_snapshot = date('Y-m-d 23:59:59', strtotime($start_raw . ' +7 days'));
                    
                    $upd = $conn->prepare("UPDATE projects SET next_snapshot_at = ? WHERE id = ?");
                    $upd->execute([$next_snapshot, $id]);
                    
                    // Return the new date so UI can update without reload
                    echo "Success|" . date('d-m-Y H:i', strtotime($next_snapshot));
                    exit();
                }
            }

            echo "Success";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid Table or Column";
    }
} else {
    echo "Unauthorized";
}
?>
