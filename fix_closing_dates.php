<?php
include "DB_connection.php";
try {
    // Select projects where next_snapshot_at is empty or zero-date
    $stmt = $conn->query("SELECT id, start_date FROM projects WHERE next_snapshot_at IS NULL OR next_snapshot_at = '0000-00-00 00:00:00' OR next_snapshot_at = ''");
    $projects = $stmt->fetchAll();
    
    $count = 0;
    foreach ($projects as $p) {
        $start = !empty($p['start_date']) && $p['start_date'] != '0000-00-00' ? $p['start_date'] : date('Y-m-d');
        $next = date('Y-m-d 23:59:59', strtotime($start . ' +7 days'));
        
        $upd = $conn->prepare("UPDATE projects SET next_snapshot_at = ? WHERE id = ?");
        $upd->execute([$next, $p['id']]);
        $count++;
    }
    echo "Fixed $count projects.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
