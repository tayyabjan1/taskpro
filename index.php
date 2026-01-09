<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {

	 include "DB_connection.php";
     include "app/Model/Task.php";
     include "app/Model/User.php";
    include "app/Model/Project.php";

	if ($_SESSION['role'] == "admin") {
         include_once "app/Model/ProjectModules.php";
         // DATE, PROJECT, EMPLOYEE & DEPT FILTERS
         $from_date = isset($_GET['from']) ? $_GET['from'] : null;
         $to_date = isset($_GET['to']) ? $_GET['to'] : null;
         $selected_project_id = isset($_GET['project_id']) && $_GET['project_id'] != '' ? $_GET['project_id'] : null;
         $selected_employee_id = isset($_GET['employee_id']) && $_GET['employee_id'] != '' ? $_GET['employee_id'] : null;
         $selected_dept_id = isset($_GET['dept_id']) && $_GET['dept_id'] != '' ? $_GET['dept_id'] : null;

         $all_projects_list = get_all_projects($conn);
         $all_employees_list = get_all_users($conn);
         include_once "app/Model/Department.php";
         $all_departments_list = get_all_departments($conn);

         // Build Filter SQL for Counts and Master Query
         $filter_sql_counts = " WHERE 1=1";
         $params_counts = [];
         
         if ($selected_project_id) { $filter_sql_counts .= " AND p.id = ?"; $params_counts[] = $selected_project_id; }
         if ($selected_employee_id) { 
             $filter_sql_counts .= " AND EXISTS (SELECT 1 FROM project_assignments pa WHERE pa.project_id = p.id AND pa.user_id = ?)"; 
             $params_counts[] = $selected_employee_id; 
         }
         if ($selected_dept_id) { 
             $filter_sql_counts .= " AND EXISTS (SELECT 1 FROM project_assignments pa JOIN users u ON pa.user_id = u.id WHERE pa.project_id = p.id AND u.department_id = ?)"; 
             $params_counts[] = $selected_dept_id; 
         }

         // Summary Counts with Filters
         $stmt_tp = $conn->prepare("SELECT COUNT(*) FROM projects p" . $filter_sql_counts);
         $stmt_tp->execute($params_counts);
         $total_projects = $stmt_tp->fetchColumn();

         $stmt_ap = $conn->prepare("SELECT COUNT(*) FROM projects p" . $filter_sql_counts . " AND p.status != 'completed'");
         $stmt_ap->execute($params_counts);
         $active_projects = $stmt_ap->fetchColumn();

         $stmt_cp = $conn->prepare("SELECT COUNT(*) FROM projects p" . $filter_sql_counts . " AND p.status = 'completed'");
         $stmt_cp->execute($params_counts);
         $completed_projects = $stmt_cp->fetchColumn();

         $stmt_ud = $conn->prepare("SELECT COUNT(*) FROM projects p" . $filter_sql_counts . " AND p.deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND p.deadline >= CURDATE() AND p.deadline != '0000-00-00' AND p.status != 'completed'");
         $stmt_ud->execute($params_counts);
         $upcoming_deadlines = $stmt_ud->fetchColumn();

         // DAILY TASK Pulse (Today's execution)
         $today_date = date('Y-m-d');
         // Pulse Counts (Today's Tasks) - Filtered by department/employee if needed
         $task_filters = " WHERE due_date = ?";
         $task_params = [$today_date];
         if ($selected_employee_id) { $task_filters .= " AND assigned_to = ?"; $task_params[] = $selected_employee_id; }
         if ($selected_dept_id) { $task_filters .= " AND EXISTS (SELECT 1 FROM users u WHERE u.id = tasks.assigned_to AND u.department_id = ?)"; $task_params[] = $selected_dept_id; }

         $stmt_todo = $conn->prepare("SELECT count(*) FROM tasks" . $task_filters . " AND status != 'completed'");
         $stmt_todo->execute($task_params);
         $today_todo_count = $stmt_todo->fetchColumn();

         $stmt_done = $conn->prepare("SELECT count(*) FROM tasks" . $task_filters . " AND status = 'completed'");
         $stmt_done->execute($task_params);
         $today_done_count = $stmt_done->fetchColumn();

         // ALL-TIME STRATEGIC TEMPORAL ENGINE
         $strategic_hierarchy = [];
         $category_history = []; // Aggregated history for Category-level charts
         
         $filter_sql = "";
         $params = [];

         if ($from_date) { $filter_sql .= " AND s.snapshot_date >= ?"; $params[] = $from_date; }
         if ($to_date) { $filter_sql .= " AND s.snapshot_date <= ?"; $params[] = $to_date; }
         if ($selected_project_id) { $filter_sql .= " AND s.project_id = ?"; $params[] = $selected_project_id; }
         if ($selected_employee_id) { 
             $filter_sql .= " AND EXISTS (SELECT 1 FROM project_assignments pa WHERE pa.project_id = pr.id AND pa.user_id = ?)"; 
             $params[] = $selected_employee_id; 
         }
         if ($selected_dept_id) { 
             $filter_sql .= " AND EXISTS (SELECT 1 FROM project_assignments pa JOIN users u ON pa.user_id = u.id WHERE pa.project_id = pr.id AND u.department_id = ?)"; 
             $params[] = $selected_dept_id; 
         }

         // Fetch every single available data point chronologically
         $sql = "SELECT i.category_name, g.title as goal_title, i.title as objective_title, 
                        i.target, i.achieved, s.snapshot_date, i.original_id as obj_id, s.id as snapshot_id
                 FROM project_snapshot_items i
                 JOIN project_snapshot_items g ON i.parent_id = g.original_id AND g.module_type = 'goal' AND i.snapshot_id = g.snapshot_id
                 JOIN project_snapshots s ON i.snapshot_id = s.id
                 JOIN projects pr ON s.project_id = pr.id
                 WHERE i.module_type = 'objective'
                 $filter_sql
                 ORDER BY i.category_name, s.snapshot_date ASC, g.title, i.original_id";
         $stmt = $conn->prepare($sql);
         $stmt->execute($params);
         $full_data_history = $stmt->fetchAll();

         foreach ($full_data_history as $row) {
             $cat = $row['category_name'];
             $goal = $row['goal_title'];
             $oid = $row['obj_id'];
             $date_key = date('M d', strtotime($row['snapshot_date']));
             $snap_id = $row['snapshot_id'];

             // 1. Build Detailed Drill-down Hierarchy (Goal > Objective > History)
             if (!isset($strategic_hierarchy[$cat])) $strategic_hierarchy[$cat] = [];
             if (!isset($strategic_hierarchy[$cat][$goal])) $strategic_hierarchy[$cat][$goal] = [];
             if (!isset($strategic_hierarchy[$cat][$goal][$oid])) {
                 $strategic_hierarchy[$cat][$goal][$oid] = [
                     'title' => $row['objective_title'],
                     'history' => []
                 ];
             }
             $strategic_hierarchy[$cat][$goal][$oid]['history'][] = [
                 'date' => $date_key,
                 'target' => (float)$row['target'],
                 'achieved' => (float)$row['achieved']
             ];

             // 2. Build Aggregated Category Pulse (Chronological Category Totals)
             if (!isset($category_history[$cat])) $category_history[$cat] = [];
             if (!isset($category_history[$cat][$snap_id])) {
                 $category_history[$cat][$snap_id] = ['date' => $date_key, 'target' => 0, 'achieved' => 0];
             }
             $category_history[$cat][$snap_id]['target'] += (float)$row['target'];
             $category_history[$cat][$snap_id]['achieved'] += (float)$row['achieved'];
         }

         // PROJECT SPECIFIC GOAL/OBJ DATA
         $goal_performance = [];
         $obj_performance = [];
         if ($selected_project_id) {
             $goal_performance = get_goal_wise_performance($conn, $selected_project_id, $from_date, $to_date);
             $obj_performance = get_objective_performance_details($conn, $selected_project_id, $from_date, $to_date);
         }
	}else {
        $num_my_task = count_my_tasks($conn, $_SESSION['id']);
        $overdue_task = count_my_tasks_overdue($conn, $_SESSION['id']);
        $nodeadline_task = count_my_tasks_NoDeadline($conn, $_SESSION['id']);
        $pending = count_my_pending_tasks($conn, $_SESSION['id']);
	     $in_progress = count_my_in_progress_tasks($conn, $_SESSION['id']);
	     $completed = count_my_completed_tasks($conn, $_SESSION['id']);

	}
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-body: #f6f7fb;
            --c-orange: #fe9365;
            --c-green: #0ac282;
            --c-pink: #fe5d70;
            --c-cyan: #01a9ac;
            --c-dark: #404e67;
            --c-text: #666;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body { 
            font-family: 'Open Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--c-dark);
            position: relative;
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('img/bg-pattern.png');
            background-size: 600px;
            background-repeat: repeat;
            opacity: 0.04;
            z-index: -1;
            pointer-events: none;
            mix-blend-mode: multiply;
        }

        .dashboard-container { 
            width: 100%;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
            position: relative;
        }

        @media (max-width: 768px) {
            .dashboard-container { padding: 15px; }
        }

        /* Summary Metric Blocks */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }
        .metric-card {
            padding: 25px;
            border-radius: var(--radius-3d);
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-3d);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slideUp 0.5s ease-out forwards;
            opacity: 0;
            border: 1px solid rgba(255,255,255,0.2);
            border-top: 1px solid rgba(255,255,255,0.4);
            border-left: 1px solid rgba(255,255,255,0.4);
        }

        .metric-card:nth-child(1) { animation-delay: 0.1s; }
        .metric-card:nth-child(2) { animation-delay: 0.2s; }
        .metric-card:nth-child(3) { animation-delay: 0.3s; }
        .metric-card:nth-child(4) { animation-delay: 0.4s; }

        .metric-card:hover {
            transform: translateY(-8px) scale(1.02);
            filter: brightness(1.05);
            box-shadow: 20px 20px 50px rgba(0,0,0,0.25);
        }

        .metric-card h4 { margin: 0; font-size: 13px; font-weight: 800; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-card .value { font-size: 32px; font-weight: 800; margin: 12px 0; }
        .metric-card .footer { border-top: 1px solid rgba(255,255,255,0.15); padding-top: 12px; font-size: 11px; display: flex; align-items: center; gap: 8px; font-weight: 600; }
        
        .bg-orange { background: linear-gradient(135deg, #fe9365, #fe7139); }
        .bg-green { background: linear-gradient(135deg, #0ac282, #089c69); }
        .bg-pink { background: linear-gradient(135deg, #fe5d70, #fe2c44); }
        .bg-cyan { background: linear-gradient(135deg, #01a9ac, #017b7d); }

        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 3fr 1.2fr;
            gap: 25px;
            margin-top: 25px;
        }

        .white-card {
            background: #fff;
            padding: 25px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            animation: fadeIn 1s ease-out forwards;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }

        .white-card:hover {
            transform: translateY(-5px);
            box-shadow: 20px 20px 45px rgba(0,0,0,0.12);
        }

        .white-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            margin: -25px -25px 25px -25px;
            padding: 20px 25px;
            background: rgba(248,250,252,0.5);
        }
        .white-card .card-header h5 { margin: 0; color: var(--dark); font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Control Styles */
        .modern-select {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            color: #555;
            background: #fff;
            transition: border-color 0.3s ease;
        }

        .modern-select:focus {
            border-color: var(--c-cyan);
            outline: none;
        }

        /* Activity Item */
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-avatar {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            background: #eee;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #777;
            transition: all 0.3s ease;
        }

        .activity-item:hover .activity-avatar {
            transform: scale(1.1);
            background: var(--c-cyan) !important;
            color: #fff !important;
        }
        .activity-info h6 { margin: 0; font-size: 13px; color: #444; }
        .activity-info p { margin: 2px 0 0 0; font-size: 11px; color: #999; line-height: 1.4; }

        .btn-apply {
            background: var(--c-cyan);
            color: #fff; border: none; padding: 6px 15px; border-radius: 3px;
            font-size: 11px; font-weight: 600; cursor: pointer;
        }

        .chart-container-large { height: 350px; width: 100%; }
        .chart-container-small { height: 180px; width: 100%; }

        @media (max-width: 1200px) {
            .content-grid { grid-template-columns: 1fr; }
            .metric-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .metric-grid { grid-template-columns: 1fr; }
            .dashboard-container { padding: 15px; }
            .filter-form { 
                flex-direction: column !important; 
                align-items: stretch !important;
                gap: 10px !important;
            }
            .filter-form select, .filter-form input {
                width: 100% !important;
            }
            .white-card .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<?php if ($_SESSION['role'] == "admin") { ?>
                <div class="container-fluid">
                <div class="dashboard-container">
                    <!-- Mobile Menu Trigger (Floats above content) -->
                    <div class="mobile-toggle" onclick="toggleSidebar()" style="display: none; position: sticky; top: 10px; z-index: 1001; background: var(--white); padding: 10px 15px; border-radius: 12px; box-shadow: var(--card-shadow); margin-bottom: 20px; align-items: center; gap: 10px; cursor: pointer;">
                        <i class="fa fa-bars" style="color: var(--primary); font-size: 18px;"></i>
                        <span style="font-weight: 700; font-size: 14px; color: var(--text-main);">COMMAND MENU</span>
                    </div>

                    <!-- Global Filter Bar -->
                    <div class="white-card" style="padding: 15px 20px; margin-bottom: 25px;">
                        <div class="filter-header" style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                            <h2 style="margin:0; font-size: 16px; font-weight: 700; color: var(--c-dark); white-space: nowrap;">STRATEGY HUB</h2>
                            <form method="GET" class="filter-form" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; flex: 1;">
                                <select name="project_id" class="modern-select">
                                    <option value="">All Projects</option>
                                    <?php foreach($all_projects_list as $p): ?>
                                        <option value="<?=$p['id']?>" <?=($selected_project_id == $p['id'] ? 'selected' : '')?>><?=$p['name']?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="employee_id" class="modern-select">
                                    <option value="">All Employees</option>
                                    <?php foreach($all_employees_list as $e): ?>
                                        <option value="<?=$e['id']?>" <?=($selected_employee_id == $e['id'] ? 'selected' : '')?>><?=$e['full_name']?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="dept_id" class="modern-select">
                                    <option value="">All Departments</option>
                                    <?php foreach($all_departments_list as $d): ?>
                                        <option value="<?=$d['id']?>" <?=($selected_dept_id == $d['id'] ? 'selected' : '')?>><?=$d['name']?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="date" name="from" value="<?=$from_date?>" class="modern-select">
                                <input type="date" name="to" value="<?=$to_date?>" class="modern-select">
                                <button type="submit" class="btn-apply">APPLY DATA</button>
                                <?php if($from_date || $to_date || $selected_project_id || $selected_employee_id || $selected_dept_id): ?>
                                    <a href="index.php" style="font-size: 11px; color: var(--c-pink); text-decoration: none;">RESET</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Metrics Top Row -->
                    <div class="metric-grid">
                        <div class="metric-card bg-orange">
                            <h4>TOTAL PROJECTS</h4>
                            <div class="value"><?=$total_projects?></div>
                            <div class="footer"><i class="fa fa-clock-o"></i> update : <?=date('H:i a')?></div>
                            <div style="position:absolute; right: -10px; bottom: 10px; opacity: 0.2; font-size: 60px;"><i class="fa fa-folder-open"></i></div>
                        </div>
                        <div class="metric-card bg-green">
                            <h4>ACTIVE STRATEGY</h4>
                            <div class="value"><?=$active_projects?></div>
                            <div class="footer"><i class="fa fa-clock-o"></i> update : <?=date('H:i a')?></div>
                            <div style="position:absolute; right: -10px; bottom: 10px; opacity: 0.2; font-size: 60px;"><i class="fa fa-rocket"></i></div>
                        </div>
                        <div class="metric-card bg-pink">
                            <h4>COMPLETED GOALS</h4>
                            <div class="value"><?=$completed_projects?></div>
                            <div class="footer"><i class="fa fa-clock-o"></i> update : <?=date('H:i a')?></div>
                            <div style="position:absolute; right: -10px; bottom: 10px; opacity: 0.2; font-size: 60px;"><i class="fa fa-check-circle"></i></div>
                        </div>
                        <div class="metric-card bg-cyan">
                            <h4>NEAR DEADLINES</h4>
                            <div class="value"><?=$upcoming_deadlines?></div>
                            <div class="footer"><i class="fa fa-clock-o"></i> update : <?=date('H:i a')?></div>
                            <div style="position:absolute; right: -10px; bottom: 10px; opacity: 0.2; font-size: 60px;"><i class="fa fa-bell"></i></div>
                        </div>
                    </div>

                    <!-- Layout: Master Strategy (75%) | Execution Details (25%) -->
                    <div class="content-grid">
                        <!-- Left Column: Command Center -->
                        <div class="left-col">
                            <?php if (empty($strategic_hierarchy)): ?>
                                <div class="white-card" style="text-align: center; padding: 50px;">
                                    <h4 style="color: #999;">NO STRATEGIC DATA LOADED FOR CURRENT VIEW</h4>
                                </div>
                            <?php else: ?>
                                <?php $gIndex = 0; foreach ($strategic_hierarchy as $catName => $goals): ?>
                                    <div class="white-card">
                                        <div class="card-header">
                                            <h5><?=htmlspecialchars($catName)?> DRILLED-DOWN OBJECTIVES</h5>
                                        </div>
                                        
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                            <?php foreach ($goals as $goalTitle => $objectives): 
                                                $isWide = ($gIndex % 3 == 0); // Structured variation: every 3rd card is wide
                                            ?>
                                                <div class="chart-card" style="grid-column: span <?=($isWide ? '2' : '1')?>; border: 1px solid #f1f1f1; padding: 15px; border-radius: 4px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                                        <h6 style="margin:0; font-size: 12px; font-weight: 600; color: var(--c-dark);"><?=htmlspecialchars($goalTitle)?></h6>
                                                        <div style="display: flex; gap: 5px;">
                                                            <select class="obj-selector modern-select" data-chart-id="chart_<?=$gIndex?>" style="font-size: 10px; padding: 2px 5px;">
                                                                <?php foreach ($objectives as $oid => $obj): ?>
                                                                    <option value="<?=$oid?>" data-history='<?=json_encode($obj['history'])?>'><?=htmlspecialchars($obj['title'])?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <select class="type-selector modern-select" data-chart-id="chart_<?=$gIndex?>" style="font-size: 10px; padding: 2px 5px;">
                                                                <option value="bar">Bar</option>
                                                                <option value="line">Trend</option>
                                                                <option value="doughnut">Mix</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="chart-container-small" style="height: <?=($isWide ? '280' : '180')?>px;">
                                                        <canvas id="chart_<?=$gIndex?>"></canvas>
                                                    </div>

                                                    <div style="margin-top: 15px; display: flex; justify-content: space-between; border-top: 1px solid #f9f9f9; padding-top: 10px;">
                                                        <div>
                                                            <div style="font-size: 9px; color: #999; font-weight: 600;">LATEST TARGET</div>
                                                            <div id="target_chart_<?=$gIndex?>" style="font-size: 14px; font-weight: 600;"><?=number_format(end($objectives[array_key_first($objectives)]['history'])['target'])?></div>
                                                        </div>
                                                        <div style="text-align: right;">
                                                            <div style="font-size: 9px; color: #999; font-weight: 600;">SUCCESS TO DATE</div>
                                                            <div id="achieved_chart_<?=$gIndex?>" style="font-size: 14px; font-weight: 600; color: var(--c-green);"><?=number_format(end($objectives[array_key_first($objectives)]['history'])['achieved'])?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php $gIndex++; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Right Column: Operational Details -->
                        <div class="right-col">
                            <!-- Daily Pulse -->
                            <div class="white-card">
                                <div class="card-header"><h5>Today's Pulse</h5></div>
                                <div class="activity-item">
                                    <div class="activity-avatar" style="border-right: 3px solid var(--c-orange);"><i class="fa fa-clock-o"></i></div>
                                    <div class="activity-info">
                                        <h6>To-Do Queue</h6>
                                        <p><?=$today_todo_count?> tasks awaiting action</p>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-avatar" style="border-right: 3px solid var(--c-green);"><i class="fa fa-check"></i></div>
                                    <div class="activity-info">
                                        <h6>Verified Success</h6>
                                        <p><?=$today_done_count?> tasks completed today</p>
                                    </div>
                                </div>
                                <a href="tasks.php?date=<?=date('Y-m-d')?>" style="display: block; text-align: center; margin-top: 15px; font-size: 11px; color: var(--c-cyan); font-weight: 600; text-decoration: none;">VIEW ALL OPERATIONAL RECORDS</a>
                            </div>

                            <!-- User Spotlight -->
                            <div class="white-card">
                                <div class="card-header"><h5>Strategy Leads</h5></div>
                                <?php 
                                    $leads_sql = "SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE role = 'employee'";
                                    $leads_params = [];
                                    if ($selected_dept_id) {
                                        $leads_sql .= " AND department_id = ?";
                                        $leads_params[] = $selected_dept_id;
                                    }
                                    $leads_sql .= " LIMIT 5";
                                    $stmt_leads = $conn->prepare($leads_sql);
                                    $stmt_leads->execute($leads_params);
                                    $leads = $stmt_leads->fetchAll();
                                    
                                    foreach($leads as $l): 
                                ?>
                                <div class="activity-item">
                                    <div class="activity-avatar" style="background: var(--bg-body);"><?=substr($l['full_name'], 0, 1)?></div>
                                    <div class="activity-info">
                                        <h6><?=htmlspecialchars($l['full_name'])?></h6>
                                        <p><?=htmlspecialchars($l['dept_name'] ?? 'General')?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php }else{ ?>
				<div class="dashboard">
					<div class="dashboard-item">
						<i class="fa fa-tasks"></i>
						<span><?=$num_my_task?> My Tasks</span>
					</div>
					<div class="dashboard-item">
						<i class="fa fa-window-close-o"></i>
						<span><?=$overdue_task?> Overdue</span>
					</div>
					<div class="dashboard-item">
						<i class="fa fa-clock-o"></i>
						<span><?=$nodeadline_task?> No Deadline</span>
					</div>
					<div class="dashboard-item">
						<i class="fa fa-square-o"></i>
						<span><?=$pending?> Pending</span>
					</div>
					<div class="dashboard-item">
						<i class="fa fa-spinner"></i>
						<span><?=$in_progress?> In progress</span>
					</div>
					<div class="dashboard-item">
						<i class="fa fa-check-square-o"></i>
						<span><?=$completed?> Completed</span>
					</div>
				</div>
                </div>
			<?php } ?>
		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(1)");
	active.classList.add("active");

    <?php if ($_SESSION['role'] == "admin" && !empty($strategic_hierarchy)) { ?>
        const charts = {};

        function createChart(id, type, historyData) {
            const ctx = document.getElementById(id).getContext('2d');
            if (charts[id]) charts[id].destroy();

            const isPie = type === 'pie' || type === 'doughnut';
            const labels = historyData.map(h => h.date);
            const achievedValues = historyData.map(h => h.achieved);
            const targetValues = historyData.map(h => h.target);
            
            const color = getColors(id);

            charts[id] = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Achieved',
                            data: achievedValues,
                            backgroundColor: isPie ? achievedValues.map((_,i) => i % 2==0 ? color : '#f1f1f1') : color,
                            borderRadius: type === 'bar' ? 3 : 0,
                            barThickness: type === 'bar' ? 25 : null,
                            borderColor: color,
                            borderWidth: 2,
                            pointBackgroundColor: '#fff',
                            tension: 0.3,
                            fill: type === 'line' ? 'origin' : false
                        },
                        {
                            label: 'Target',
                            data: targetValues,
                            type: (type === 'line' || isPie) ? type : 'line', // Always show target as line for comparison in bar
                            borderColor: '#cbd5e1',
                            borderDash: [5, 5],
                            borderWidth: 1,
                            pointRadius: 0,
                            fill: false,
                            hidden: isPie
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: !isPie, position: 'bottom', labels: { boxWidth: 8, font: { size: 9 } } } 
                    },
                    scales: isPie ? { x: { display: false }, y: { display: false } } : {
                        y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { font: { size: 9 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        }

        function getColors(id) {
            const idx = parseInt(id.split('_')[1]);
            const colors = ['#fe9365', '#0ac282', '#fe5d70', '#01a9ac'];
            return colors[idx % 4];
        }

        <?php 
        $gInternal = 0;
        foreach ($strategic_hierarchy as $catName => $goals):
            foreach ($goals as $goalTitle => $objectives):
                $firstObj = reset($objectives);
        ?>
            createChart('chart_<?=$gInternal?>', 'bar', <?=json_encode($firstObj['history'])?>);
        <?php $gInternal++; endforeach; endforeach; ?>

        // Global Interaction Hub
        document.querySelectorAll('.obj-selector, .type-selector').forEach(select => {
            select.addEventListener('change', function() {
                const chartId = this.dataset.chartId;
                const card = this.closest('.chart-card');
                const objSelect = card.querySelector('.obj-selector');
                const typeSelect = card.querySelector('.type-selector');
                
                const history = JSON.parse(objSelect.options[objSelect.selectedIndex].dataset.history);
                const type = typeSelect.value;

                // Update metrics labels (show latest)
                const latest = history[history.length - 1];
                document.getElementById('target_' + chartId).innerText = latest.target.toLocaleString();
                document.getElementById('achieved_' + chartId).innerText = latest.achieved.toLocaleString();

                // Plot Historical Comparison
                createChart(chartId, type, history);
            });
        });
        function toggleSidebar() {
            document.querySelector('.side-bar').classList.toggle('open');
        }
    <?php } ?>
</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
 ?>