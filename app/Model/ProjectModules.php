<?php

// Goals
function get_project_goals($conn, $project_id) {
    $sql = "SELECT * FROM project_goals WHERE project_id = ? ORDER BY deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function add_project_goal($conn, $data) {
    $sql = "INSERT INTO project_goals (project_id, title, description, deadline, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$data['project_id'], $data['title'], $data['description'], $data['deadline'], $data['status']]);
}

function update_goal_status($conn, $id, $status) {
    $sql = "UPDATE project_goals SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$status, $id]);
}

function get_goal_objectives($conn, $goal_id) {
    $sql = "SELECT * FROM project_objectives WHERE goal_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$goal_id]);
    return $stmt->fetchAll();
}

// KPIs
function get_project_kpis($conn, $project_id) {
    $sql = "SELECT *, IF(target_value > 0, (achieved_value / target_value * 100), 0) as progress FROM project_kpis WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function add_project_kpi($conn, $data) {
    $sql = "INSERT INTO project_kpis (project_id, name, target_value, achieved_value, unit, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$data['project_id'], $data['name'], $data['target_value'], $data['achieved_value'], $data['unit'], $data['status']]);
}

// Milestones
function get_project_milestones($conn, $project_id) {
    $sql = "SELECT * FROM project_milestones WHERE project_id = ? ORDER BY due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function add_project_milestone($conn, $data) {
    $sql = "INSERT INTO project_milestones (project_id, title, due_date, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$data['project_id'], $data['title'], $data['due_date'], $data['status']]);
}

// Achievements
function get_project_achievements($conn, $project_id) {
    $sql = "SELECT * FROM project_achievements WHERE project_id = ? ORDER BY date_achieved DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function add_project_achievement($conn, $data) {
    $sql = "INSERT INTO project_achievements (project_id, title, description, date_achieved) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$data['project_id'], $data['title'], $data['description'], $data['date_achieved']]);
}

// Global Dashboard Stats
function get_all_active_projects_count($conn) {
    $sql = "SELECT COUNT(*) FROM projects WHERE status = 'in_progress'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_all_completed_projects_count($conn) {
    $sql = "SELECT COUNT(*) FROM projects WHERE status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_global_upcoming_deadlines_count($conn) {
    $sql = "SELECT COUNT(*) FROM projects WHERE deadline >= CURDATE() AND status != 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_project_categories($conn, $project_id) {
    $sql = "SELECT * FROM project_categories WHERE project_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function get_project_statuses($conn, $project_id) {
    $sql = "SELECT * FROM project_statuses WHERE project_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function get_project_snapshots($conn, $project_id) {
    $sql = "SELECT * FROM project_snapshots WHERE project_id = ? ORDER BY snapshot_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function get_snapshot_items($conn, $snapshot_id) {
    $sql = "SELECT * FROM project_snapshot_items WHERE snapshot_id = ? ORDER BY sort_order ASC, id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$snapshot_id]);
    return $stmt->fetchAll();
}

function create_project_snapshot($conn, $project_id, $snapshot_date) {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO project_snapshots (project_id, snapshot_date) VALUES (?, ?)");
        $stmt->execute([$project_id, $snapshot_date]);
        $snapshot_id = $conn->lastInsertId();

        // 2. Prepared Statement for Items
        $sql = "INSERT INTO project_snapshot_items 
                (snapshot_id, module_type, original_id, parent_id, category_name, title, date_ref, target, achieved, results, status, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        $order = 0;

        // Goals
        $goals = get_project_goals($conn, $project_id);
        foreach ($goals as $g) {
            $cat_stmt = $conn->prepare("SELECT name FROM project_categories WHERE id = ?");
            $cat_stmt->execute([$g['category_id']]);
            $cat_name = $cat_stmt->fetchColumn();

            $stmt->execute([$snapshot_id, 'goal', $g['id'], null, $cat_name, $g['title'], $g['start_date'], null, null, null, $g['status'], $order++]);
            
            $objectives = get_goal_objectives($conn, $g['id']);
            foreach ($objectives as $obj) {
                $stmt->execute([$snapshot_id, 'objective', $obj['id'], $g['id'], null, $obj['title'], $obj['deadline'], $obj['target'], $obj['achievement'], $obj['results'], $obj['status'], $order++]);
            }
        }

        // KPIs
        $kpis = get_project_kpis($conn, $project_id);
        foreach ($kpis as $k) {
            $stmt->execute([$snapshot_id, 'kpi', $k['id'], null, null, $k['name'], $k['deadline'], $k['target_value'], $k['achieved_value'], $k['results'], $k['status'], $order++]);
        }

        // Milestones
        $milestones = get_project_milestones($conn, $project_id);
        foreach ($milestones as $m) {
            $stmt->execute([$snapshot_id, 'milestone', $m['id'], null, null, $m['title'], $m['due_date'], $m['target'], $m['achievement'], $m['results'], $m['status'], $order++]);
        }

        // Achievements
        $achievements = get_project_achievements($conn, $project_id);
        foreach ($achievements as $a) {
            $stmt->execute([$snapshot_id, 'achievement', $a['id'], null, null, $a['title'], $a['date_achieved'], $a['target'], $a['description'], $a['results'], $a['status'], $order++]);
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        return $e->getMessage();
    }
}

function update_snapshot_items($conn, $snapshot_id, $project_id) {
    try {
        $conn->beginTransaction();
        
        // Delete existing items for this snapshot
        $stmt = $conn->prepare("DELETE FROM project_snapshot_items WHERE snapshot_id = ?");
        $stmt->execute([$snapshot_id]);

        // Re-capture data
        $sql = "INSERT INTO project_snapshot_items 
                (snapshot_id, module_type, original_id, parent_id, category_name, title, date_ref, target, achieved, results, status, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        $order = 0;

        // Goals
        $goals = get_project_goals($conn, $project_id);
        foreach ($goals as $g) {
            $cat_stmt = $conn->prepare("SELECT name FROM project_categories WHERE id = ?");
            $cat_stmt->execute([$g['category_id']]);
            $cat_name = $cat_stmt->fetchColumn();
            $stmt->execute([$snapshot_id, 'goal', $g['id'], null, $cat_name, $g['title'], $g['start_date'], null, null, null, $g['status'], $order++]);
            
            $objectives = get_goal_objectives($conn, $g['id']);
            foreach ($objectives as $obj) {
                $stmt->execute([$snapshot_id, 'objective', $obj['id'], $g['id'], null, $obj['title'], $obj['deadline'], $obj['target'], $obj['achievement'], $obj['results'], $obj['status'], $order++]);
            }
        }

        // KPIs, Milestones, Achievements (same logic as create)
        $kpis = get_project_kpis($conn, $project_id);
        foreach ($kpis as $k) {
            $stmt->execute([$snapshot_id, 'kpi', $k['id'], null, null, $k['name'], $k['deadline'], $k['target_value'], $k['achieved_value'], $k['results'], $k['status'], $order++]);
        }

        $milestones = get_project_milestones($conn, $project_id);
        foreach ($milestones as $m) {
            $stmt->execute([$snapshot_id, 'milestone', $m['id'], null, null, $m['title'], $m['due_date'], $m['target'], $m['achievement'], $m['results'], $m['status'], $order++]);
        }

        $achievements = get_project_achievements($conn, $project_id);
        foreach ($achievements as $a) {
            $stmt->execute([$snapshot_id, 'achievement', $a['id'], null, null, $a['title'], $a['date_achieved'], $a['target'], $a['description'], $a['results'], $a['status'], $order++]);
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        return $e->getMessage();
    }
}

function get_global_performance_trend($conn, $from = null, $to = null, $project_id = null) {
    $where = "WHERE i.results IS NOT NULL AND i.results != '' AND i.results != '0.00%'";
    $params = [];
    if ($from) { $where .= " AND s.snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $where .= " AND s.snapshot_date <= ?"; $params[] = $to; }
    if ($project_id) { $where .= " AND s.project_id = ?"; $params[] = $project_id; }

    $sql = "SELECT s.snapshot_date, AVG(CAST(REPLACE(i.results, '%', '') AS DECIMAL(10,2))) as avg_result 
            FROM project_snapshots s
            JOIN project_snapshot_items i ON s.id = i.snapshot_id
            $where
            GROUP BY s.snapshot_date
            ORDER BY s.snapshot_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_category_performance_data($conn, $from = null, $to = null, $project_id = null) {
    $sub_where = "";
    $params = [];
    if ($from) { $sub_where .= " WHERE snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= ($sub_where ? " AND" : " WHERE") . " snapshot_date <= ?"; $params[] = $to; }
    if ($project_id) { 
        $sub_where .= ($sub_where ? " AND" : " WHERE") . " project_id = ?"; 
        $params[] = $project_id; 
    }

    $sql = "SELECT i.category_name, AVG(CAST(REPLACE(i.results, '%', '') AS DECIMAL(10,2))) as avg_result
            FROM project_snapshot_items i
            JOIN (
                SELECT project_id, MAX(id) as latest_snap 
                FROM project_snapshots 
                $sub_where
                GROUP BY project_id
            ) s ON i.snapshot_id = s.latest_snap
            WHERE i.category_name IS NOT NULL AND i.category_name != ''
            AND i.results IS NOT NULL AND i.results != ''
            GROUP BY i.category_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_module_distribution($conn, $from = null, $to = null, $project_id = null) {
    $sub_where = "";
    $params = [];
    if ($from) { $sub_where .= " WHERE snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= ($sub_where ? " AND" : " WHERE") . " snapshot_date <= ?"; $params[] = $to; }
    if ($project_id) { 
        $sub_where .= ($sub_where ? " AND" : " WHERE") . " project_id = ?"; 
        $params[] = $project_id; 
    }

    $sql = "SELECT i.module_type, COUNT(*) as count
            FROM project_snapshot_items i
            JOIN (
                SELECT project_id, MAX(id) as latest_snap 
                FROM project_snapshots 
                $sub_where
                GROUP BY project_id
            ) s ON i.snapshot_id = s.latest_snap
            GROUP BY i.module_type";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_target_vs_achieved_global($conn, $from = null, $to = null, $project_id = null) {
    $sub_where = "";
    $params = [];
    if ($from) { $sub_where .= " WHERE snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= ($sub_where ? " AND" : " WHERE") . " snapshot_date <= ?"; $params[] = $to; }
    if ($project_id) { 
        $sub_where .= ($sub_where ? " AND" : " WHERE") . " project_id = ?"; 
        $params[] = $project_id; 
    }

    $sql = "SELECT category_name, SUM(CAST(target AS DECIMAL(10,2))) as total_target, 
                   SUM(CAST(achieved AS DECIMAL(10,2))) as total_achieved
            FROM project_snapshot_items i
            JOIN (
                SELECT project_id, MAX(id) as latest_snap 
                FROM project_snapshots 
                $sub_where
                GROUP BY project_id
            ) s ON i.snapshot_id = s.latest_snap
            WHERE i.category_name IS NOT NULL AND i.category_name != ''
            AND i.target IS NOT NULL AND i.achieved IS NOT NULL
            AND i.target != '' AND i.achieved != ''
            AND i.module_type IN ('objective', 'kpi')
            GROUP BY category_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_status_distribution($conn, $from = null, $to = null, $project_id = null) {
    $sub_where = "";
    $params = [];
    if ($from) { $sub_where .= " WHERE snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= ($sub_where ? " AND" : " WHERE") . " snapshot_date <= ?"; $params[] = $to; }
    if ($project_id) { 
        $sub_where .= ($sub_where ? " AND" : " WHERE") . " project_id = ?"; 
        $params[] = $project_id; 
    }

    $sql = "SELECT i.status, COUNT(*) as count
            FROM project_snapshot_items i
            JOIN (
                SELECT project_id, MAX(id) as latest_snap 
                FROM project_snapshots 
                $sub_where
                GROUP BY project_id
            ) s ON i.snapshot_id = s.latest_snap
            WHERE i.status IS NOT NULL AND i.status != ''
            GROUP BY i.status";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_goal_wise_performance($conn, $project_id, $from = null, $to = null) {
    if (!$project_id) return [];

    $sub_where = "WHERE project_id = ?";
    $params = [$project_id];
    if ($from) { $sub_where .= " AND snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= " AND snapshot_date <= ?"; $params[] = $to; }

    $sql = "SELECT g.title as goal_title, 
                   SUM(CASE WHEN o.target REGEXP '^[0-9.]+$' THEN CAST(o.target AS DECIMAL(10,2)) ELSE 0 END) as total_target, 
                   SUM(CASE WHEN o.achieved REGEXP '^[0-9.]+$' THEN CAST(o.achieved AS DECIMAL(10,2)) ELSE 0 END) as total_achieved
            FROM project_snapshot_items o
            JOIN project_snapshot_items g ON o.parent_id = g.original_id AND g.module_type = 'goal' AND o.snapshot_id = g.snapshot_id
            JOIN (
                SELECT MAX(id) as latest_snap FROM project_snapshots $sub_where
            ) s ON o.snapshot_id = s.latest_snap
            WHERE o.module_type = 'objective'
            GROUP BY g.title";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_objective_performance_details($conn, $project_id, $from = null, $to = null) {
    if (!$project_id) return [];

    $sub_where = "WHERE project_id = ?";
    $params = [$project_id];
    if ($from) { $sub_where .= " AND snapshot_date >= ?"; $params[] = $from; }
    if ($to) { $sub_where .= " AND snapshot_date <= ?"; $params[] = $to; }

    $sql = "SELECT i.title, CAST(REPLACE(i.results, '%', '') AS DECIMAL(10,2)) as performance
            FROM project_snapshot_items i
            JOIN (
                SELECT MAX(id) as latest_snap FROM project_snapshots $sub_where
            ) s ON i.snapshot_id = s.latest_snap
            WHERE i.module_type = 'objective' 
            AND i.results IS NOT NULL AND i.results != ''
            LIMIT 15";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
