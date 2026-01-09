<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/Project.php";
    include "app/Model/ProjectModules.php";

    if (!isset($_GET['id'])) {
        header("Location: projects.php");
        exit();
    }
    $project_id = $_GET['id'];
    $project = get_project_by_id($conn, $project_id);

    if (!$project) {
        header("Location: projects.php");
        exit();
    }

    // Access Control: Admin sees all, Employees only assigned projects
    $project_assignees = get_project_assignees($conn, $project_id);
    if ($_SESSION['role'] == 'employee' && !in_array($_SESSION['id'], $project_assignees)) {
        header("Location: index.php?error=Unauthorized access");
        exit();
    }
    
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

    // AUTOMATED WEEKLY SNAPSHOT TRIGGER
    // Check if it's time to take a snapshot (Passed next_snapshot_at)
    $now = date('Y-m-d H:i:s');
    if ($project['next_snapshot_at'] && $project['next_snapshot_at'] != '0000-00-00 00:00:00' && $now >= $project['next_snapshot_at']) {
        $snap_date = date('Y-m-d', strtotime($project['next_snapshot_at']));
        $result = create_project_snapshot($conn, $project_id, $snap_date);
        
        if ($result === true) {
            // Set next snapshot to exactly 1 week later
            $next_target = date('Y-m-d 23:59:59', strtotime($project['next_snapshot_at'] . ' +7 days'));

            $upd = $conn->prepare("UPDATE projects SET next_snapshot_at = ? WHERE id = ?");
            $upd->execute([$next_target, $project_id]);
            
            // Re-fetch project to update $project['next_snapshot_at'] for UI
            $project = get_project_by_id($conn, $project_id);
        }
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Project: <?=htmlspecialchars($project['name'])?></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .project-header {
            background: #fff;
            padding: 30px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .project-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .stat-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 20px;
            flex: 1;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: inset 1px 1px 3px rgba(255, 255, 255, 0.8), inset -2px -2px 5px rgba(0, 0, 0, 0.02);
            transition: var(--transition);
        }
        .stat-card:hover { transform: translateY(-3px); background: #fff; border-color: var(--primary); }
        .stat-card h4 { font-size: 28px; font-weight: 800; color: var(--dark); margin-bottom: 5px; }
        .stat-card span { color: #94a3b8; font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
        
        .module-section {
            background: #fff;
            padding: 30px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .item-list { list-style: none; }
        .item-list li {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-list li:last-child { border-bottom: none; }
        
        .progress-bar-bg {
            background: #eee;
            height: 10px;
            border-radius: 5px;
            width: 100px;
            display: inline-block;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
            background: #00CF22;
        }

        /* Forms */
        .add-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .add-form input, .add-form select, .add-form textarea {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
            <?php 
                // Fetch ALL data needed for the master sheet
                $goals = get_project_goals($conn, $project_id);
                $kpis = get_project_kpis($conn, $project_id);
                $milestones = get_project_milestones($conn, $project_id);
                $achievements = get_project_achievements($conn, $project_id);
                $categories = get_project_categories($conn, $project_id);
                $statuses = get_project_statuses($conn, $project_id);
                $snapshots = get_project_snapshots($conn, $project_id);
            ?>

            <!-- Modern Project Header Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <!-- 1. Project Identity Card -->
                <div class="premium-card" style="padding: 25px; display: flex; flex-direction: column; justify-content: space-between; position: relative; border-left: 5px solid var(--primary);">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1.5px; display: block; margin-bottom: 10px;">Project Identity</span>
                        <div contenteditable="true" onblur="updateCell(this, 'projects', <?=$project_id?>, 'name')" style="font-size: 28px; font-weight: 800; color: var(--dark); margin: 0; line-height: 1.2; outline: none; border-bottom: 2px solid transparent; transition: 0.3s; display: inline-block; min-width: 50px;"><?=htmlspecialchars($project['name'])?></div>
                    </div>
                    <div style="margin-top: 15px;">
                        <span style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 5px;">Description</span>
                        <div contenteditable="true" onblur="updateCell(this, 'projects', <?=$project_id?>, 'description')" style="font-size: 14px; color: #475569; line-height: 1.6; outline: none;"><?=htmlspecialchars($project['description'])?></div>
                    </div>
                </div>

                <!-- 2. Status & Timeline Card -->
                <div class="premium-card" style="padding: 25px; display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(145deg, #ffffff, #f8fafc);">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 12px;">Timeline Status</span>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: <?=$project['status']=='active'?'#22c55e':($project['status']=='completed'?'#3b82f6':'#f59e0b')?>; box-shadow: 0 0 10px <?=$project['status']=='active'?'rgba(34, 197, 94, 0.4)':($project['status']=='completed'?'rgba(59, 130, 246, 0.4)':'rgba(245, 158, 11, 0.4)')?>;"></div>
                            <span contenteditable="true" onblur="updateCell(this, 'projects', <?=$project_id?>, 'status')" style="font-size: 15px; font-weight: 700; color: var(--dark); text-transform: uppercase; outline: none;"><?=$project['status']?></span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 5px;">TARGET DEADLINE</span>
                        <input type="date" onchange="updateCellSelect(this, 'projects', <?=$project_id?>, 'deadline')" value="<?=$project['deadline']?>" style="border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; padding: 6px 10px; color: var(--dark); background: #fff; width: 100%; outline: none;">
                    </div>
                </div>

                <!-- 3. Leadership Card -->
                <div class="premium-card" style="padding: 25px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(18, 123, 142, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; border: 1px solid rgba(18, 123, 142, 0.2);">
                        <i class="fa fa-users"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Project Lead</span>
                    <div style="font-size: 14px; font-weight: 700; color: var(--dark); line-height: 1.4;"><?=htmlspecialchars($project['employee_names'] ?? 'Unassigned')?></div>
                </div>

                <!-- 4. Actions Card -->
                <div class="premium-card" style="padding: 20px; display: flex; flex-direction: column; justify-content: center; gap: 10px;">
                    <button class="btn" style="background: var(--primary); color: #fff; border: none; padding: 12px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 10px rgba(18, 123, 142, 0.2);" onclick="showCategoryModal()">
                        <i class="fa fa-tag"></i> CATEGORIES
                    </button>
                    <button class="btn" style="background: #fff; color: var(--dark); border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;" onclick="showStatusModal()">
                        <i class="fa fa-list"></i> STATUSES
                    </button>
                </div>
            </div>

            <!-- Categories Modal -->
            <div id="categoryModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:24px; border-radius:12px; width:400px; max-width:90%; box-shadow: var(--shadow-md);">
                    <h3 style="margin-bottom:20px; font-weight:600;">Manage Categories</h3>
                    <div id="categoryList" style="margin:15px 0; max-height:300px; overflow-y:auto; padding-right:5px;">
                        <?php foreach($categories as $cat): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; padding:10px 14px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
                            <span style="font-size:14px; font-weight:500;"><?=htmlspecialchars($cat['name'])?></span>
                            <button onclick="deleteCategory(<?=$cat['id']?>)" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:4px;"><i class="fa fa-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <input type="text" id="newCategoryName" placeholder="New Category Name" style="flex:1; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; outline:none; font-size:14px;">
                        <button class="btn btn-primary" onclick="addCategory()" style="background:var(--primary);">Add</button>
                    </div>
                    <button class="btn" onclick="closeCategoryModal()" style="margin-top:15px; background:#f1f5f9; color:var(--text-muted); width:100%; border:none;">Close</button>
                </div>
            </div>

            <!-- Status Modal -->
            <div id="statusModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:24px; border-radius:12px; width:400px; max-width:90%; box-shadow: var(--shadow-md);">
                    <h3 style="margin-bottom:20px; font-weight:600;">Manage Statuses</h3>
                    <div id="statusList" style="margin:15px 0; max-height:300px; overflow-y:auto; padding-right:5px;">
                        <?php foreach($statuses as $st): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; padding:10px 14px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
                            <span style="font-size:14px; font-weight:500;"><?=htmlspecialchars($st['name'])?></span>
                            <button onclick="deleteStatus(<?=$st['id']?>)" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:4px;"><i class="fa fa-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <input type="text" id="newStatusName" placeholder="New Status Name" style="flex:1; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; outline:none; font-size:14px;">
                        <button class="btn btn-primary" onclick="addStatus()" style="background:var(--primary);">Add</button>
                    </div>
                    <button class="btn" onclick="closeStatusModal()" style="margin-top:15px; background:#f1f5f9; color:var(--text-muted); width:100%; border:none;">Close</button>
                </div>
            </div>

            <!-- Advanced Project Master Sheet -->
            <div class="card" style="padding: 0; overflow: hidden; border:none; box-shadow: var(--shadow-md); margin-top: 20px;">
                <div style="padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: start; background: #fff;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px;">
                            <h2 style="font-size: 20px; font-weight: 700; color: var(--dark); margin:0; display:flex; align-items:center;">
                                <i class="fa fa-th-list" style="color: var(--primary); margin-right: 12px;"></i> 
                                Strategy Master Sheet
                            </h2>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="saveWeeklySnapshot()" class="btn" style="padding: 6px 14px; font-size: 11px; font-weight: 600; background: #22c55e; color: #fff; border:none; border-radius: 6px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);"><i class="fa fa-paper-plane"></i> Submit</button>
                                <button onclick="updateWeeklySnapshot()" class="btn" style="padding: 6px 14px; font-size: 11px; font-weight: 600; background: #3b82f6; color: #fff; border:none; border-radius: 6px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);"><i class="fa fa-refresh"></i> Update</button>
                            </div>
                        </div>
                        <div style="display: flex; gap: 24px; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; width: fit-content;">
                             <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <span style="color: #64748b;"><i class="fa fa-calendar-play"></i> START:</span> 
                                <input type="date" onchange="updateCellSelect(this, 'projects', <?=$project_id?>, 'start_date')" value="<?=$project['start_date']?>" style="color: var(--text-main); font-weight: 700; background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11px; outline: none;">
                            </div>
                            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <span style="color: #64748b;"><i class="fa fa-calendar-check"></i> DEADLINE:</span> 
                                <input type="date" onchange="updateCellSelect(this, 'projects', <?=$project_id?>, 'deadline')" value="<?=$project['deadline']?>" style="color: var(--text-main); font-weight: 700; background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11px; outline: none;">
                            </div>
                            <div style="height: 16px; width: 1px; background: #e2e8f0;"></div>
                            <div style="font-size: 11px; text-transform: uppercase; color: #22c55e; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-clock-o"></i> Closing Date: 
                                <span id="closingDateDisplay" style="color: #059669;">
                                    <?= ($project['next_snapshot_at'] && $project['next_snapshot_at'] != '0000-00-00 00:00:00') ? date('d-m-Y H:i', strtotime($project['next_snapshot_at'])) : '' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center; padding-top: 4px;">
                        <button class="btn btn-primary btn-icon" onclick="addEntry(<?=$project_id?>)" title="Add New Entry" style="border-radius:10px; width:44px; height:44px; background:var(--primary); font-size:18px; box-shadow: 0 4px 12px rgba(18, 123, 142, 0.2);"><i class="fa fa-plus"></i></button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">ID</th>
                                <th>Category</th>
                                <th>Master Strategy / Goal</th>
                                <th>Core Objectives</th>
                                <th>Target</th>
                                <th>Achieved</th>
                                <th>Performance %</th>
                                <th>Status</th>
                                <th style="width: 100px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="masterTableBody">
                            <?php 
                            if (empty($goals) && empty($kpis) && empty($milestones) && empty($achievements)) {
                                echo "<tr id='emptyRow'><td colspan='11' style='text-align:center; padding: 20px;'>Project sheet is currently empty. Click the '+' to start.</td></tr>";
                            } else {
                                // Display Goals
                                foreach ($goals as $index => $g) {
                                    $objectives = get_goal_objectives($conn, $g['id']);
                                    $hasObj = !empty($objectives);
                                    $firstObj = $hasObj ? $objectives[0] : null;

                                    echo "<tr class='row-goal'>";
                                    echo "<td style='text-align:center; font-weight: 600; color: var(--primary);'>G-" . ($index + 1) . "</td>";
                                    
                                    // Category Cell
                                    echo "<td>
                                            <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_goals\", ".$g['id'].", \"category_id\")'>
                                                <option value=''>Select Category</option>";
                                                foreach($categories as $cat) {
                                                    $selected = ($g['category_id'] == $cat['id']) ? 'selected' : '';
                                                    echo "<option value='".$cat['id']."' $selected>".htmlspecialchars($cat['name'])."</option>";
                                                }
                                    echo "  </select>
                                          </td>";

                                    // Goal Title Cell
                                    echo "<td contenteditable='true' style='font-weight: bold; min-width: 150px;' onblur='updateCell(this, \"project_goals\", ".$g['id'].", \"title\")'>" . htmlspecialchars($g['title']) . "</td>";
                                    
                                    if ($hasObj) {
                                        // Merge first objective into this row
                                        echo "<td contenteditable='true' onblur='updateCell(this, \"project_objectives\", ".$firstObj['id'].", \"title\")'>" . htmlspecialchars($firstObj['title']) . "</td>";
                                        echo "<td class='cell-target' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_objectives\", ".$firstObj['id'].", \"target\")'>" . htmlspecialchars($firstObj['target'] ?? '') . "</td>";
                                        echo "<td class='cell-achieved' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_objectives\", ".$firstObj['id'].", \"achievement\")'>" . htmlspecialchars($firstObj['achievement'] ?? '') . "</td>";
                                        echo "<td class='cell-results' contenteditable='true' onblur='updateCell(this, \"project_objectives\", ".$firstObj['id'].", \"results\")'>" . htmlspecialchars($firstObj['results'] ?? '') . "</td>";
                                        echo "<td>
                                                <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_objectives\", ".$firstObj['id'].", \"status\")'>
                                                    <option value=''>Select Status</option>";
                                                    foreach($statuses as $st) {
                                                        $selected = ($firstObj['status'] == $st['name']) ? 'selected' : '';
                                                        echo "<option value='".$st['name']."' $selected>".htmlspecialchars($st['name'])."</option>";
                                                    }
                                        echo "  </select>
                                              </td>";
                                    } else {
                                        // Fallback placeholder
                                        echo "<td colspan='5' style='color:#ccc; text-align:center; font-style:italic;'>No objectives. Click + to add.</td>";
                                    }

                                    // Action buttons for Goal / First Objective
                                    echo "<td style='text-align:center;'>
                                            <div style='display:flex; gap:4px; justify-content:center;'>
                                                <button class='btn btn-primary btn-icon' style='background: var(--primary); font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='addObjective(".$g['id'].")' title='Add Objective'><i class='fa fa-plus'></i></button>
                                                <button class='btn btn-primary btn-icon' style='background: #ef4444; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='deleteEntry(this, \"project_goals\", ".$g['id'].")' title='Delete Goal'><i class='fa fa-trash'></i></button>
                                            </div>
                                          </td>";
                                    echo "</tr>";

                                    // Display subsequent Objectives for this goal
                                    if ($hasObj && count($objectives) > 1) {
                                        for ($i = 1; $i < count($objectives); $i++) {
                                            $obj = $objectives[$i];
                                            echo "<tr class='row-goal' style='background: #fcfcfc;'>";
                                            echo "<td style='text-align:right; color: #94a3b8; font-size: 0.85em;'>." . ($i + 1) . "</td>";
                                            echo "<td style='color: #ccc; text-align:center; font-style: italic; background: #fff;'>^</td>"; 
                                            echo "<td style='color: #ccc; text-align:center; font-style: italic; background: #fff;'>^</td>"; 
                                            echo "<td contenteditable='true' onblur='updateCell(this, \"project_objectives\", ".$obj['id'].", \"title\")'>" . htmlspecialchars($obj['title']) . "</td>";
                                            echo "<td class='cell-target' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_objectives\", ".$obj['id'].", \"target\")'>" . htmlspecialchars($obj['target'] ?? '') . "</td>";
                                            echo "<td class='cell-achieved' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_objectives\", ".$obj['id'].", \"achievement\")'>" . htmlspecialchars($obj['achievement'] ?? '') . "</td>";
                                            echo "<td class='cell-results' contenteditable='true' onblur='updateCell(this, \"project_objectives\", ".$obj['id'].", \"results\")'>" . htmlspecialchars($obj['results'] ?? '') . "</td>";
                                            echo "<td>
                                                    <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_objectives\", ".$obj['id'].", \"status\")'>
                                                        <option value=''>Select Status</option>";
                                                        foreach($statuses as $st) {
                                                            $selected = ($obj['status'] == $st['name']) ? 'selected' : '';
                                                            echo "<option value='".$st['name']."' $selected>".htmlspecialchars($st['name'])."</option>";
                                                        }
                                            echo "  </select>
                                                  </td>";
                                            echo "<td style='text-align:center;'>
                                                    <button class='btn btn-primary btn-icon' style='background: #ef4444; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='deleteEntry(this, \"project_objectives\", ".$obj['id'].")' title='Delete Objective'><i class='fa fa-trash'></i></button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    }
                                }
                                
                                // Display KPIs
                                foreach ($kpis as $kIndex => $k) {
                                    echo "<tr class='row-kpi'>";
                                    echo "<td style='text-align:center; font-weight: 600; color: #007bff;'>K-" . ($kIndex + 1) . "</td>";
                                    echo "<td>--</td>";
                                    echo "<td>--</td>";
                                    echo "<td contenteditable='true' onblur='updateCell(this, \"project_kpis\", ".$k['id'].", \"name\")'>" . htmlspecialchars($k['name']) . "</td>";
                                    echo "<td class='cell-target' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_kpis\", ".$k['id'].", \"target_value\")'>" . $k['target_value'] . "</td>";
                                    echo "<td class='cell-achieved' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_kpis\", ".$k['id'].", \"achieved_value\")'>" . $k['achieved_value'] . "</td>";
                                    echo "<td class='cell-results' contenteditable='true' onblur='updateCell(this, \"project_kpis\", ".$k['id'].", \"results\")'>" . htmlspecialchars($k['results'] ?? '') . "</td>";
                                    echo "<td>
                                            <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_kpis\", ".$k['id'].", \"status\")'>
                                                <option value=''>Select Status</option>";
                                                foreach($statuses as $st) {
                                                    $selected = ($k['status'] == $st['name']) ? 'selected' : '';
                                                    echo "<option value='".$st['name']."' $selected>".htmlspecialchars($st['name'])."</option>";
                                                }
                                    echo "  </select>
                                          </td>";
                                    echo "<td style='text-align:center;'>
                                            <div style='display:flex; gap:4px; justify-content:center;'>
                                                <button class='btn btn-primary btn-icon' style='background: #64748b; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='focusRow(this)' title='Edit Row'><i class='fa fa-pencil'></i></button>
                                                <button class='btn btn-primary btn-icon' style='background: #ef4444; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='deleteEntry(this, \"project_kpis\", ".$k['id'].")' title='Delete Row'><i class='fa fa-trash'></i></button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }

                                // Display Milestones
                                foreach ($milestones as $mIndex => $m) {
                                    echo "<tr class='row-milestone'>";
                                    echo "<td style='text-align:center; font-weight: 600; color: #e11d48;'>M-" . ($mIndex + 1) . "</td>";
                                    echo "<td>--</td>";
                                    echo "<td>--</td>";
                                    echo "<td contenteditable='true' onblur='updateCell(this, \"project_milestones\", ".$m['id'].", \"title\")'>" . htmlspecialchars($m['title']) . "</td>";
                                    echo "<td class='cell-target' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_milestones\", ".$m['id'].", \"target\")'>" . htmlspecialchars($m['target'] ?? '') . "</td>";
                                    echo "<td class='cell-achieved' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_milestones\", ".$m['id'].", \"achievement\")'>" . htmlspecialchars($m['achievement'] ?? '') . "</td>";
                                    echo "<td class='cell-results' contenteditable='true' onblur='updateCell(this, \"project_milestones\", ".$m['id'].", \"results\")'>" . htmlspecialchars($m['results'] ?? '') . "</td>";
                                    echo "<td>
                                            <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_milestones\", ".$m['id'].", \"status\")'>
                                                <option value=''>Select Status</option>";
                                                foreach($statuses as $st) {
                                                    $selected = ($m['status'] == $st['name']) ? 'selected' : '';
                                                    echo "<option value='".$st['name']."' $selected>".htmlspecialchars($st['name'])."</option>";
                                                }
                                    echo "  </select>
                                          </td>";
                                    echo "<td style='text-align:center;'>
                                            <div style='display:flex; gap:4px; justify-content:center;'>
                                                <button class='btn btn-primary btn-icon' style='background: #64748b; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='focusRow(this)' title='Edit Row'><i class='fa fa-pencil'></i></button>
                                                <button class='btn btn-primary btn-icon' style='background: #ef4444; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='deleteEntry(this, \"project_milestones\", ".$m['id'].")' title='Delete Row'><i class='fa fa-trash'></i></button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }

                                // Display Achievements
                                foreach ($achievements as $aIndex => $a) {
                                    echo "<tr class='row-achievement'>";
                                    echo "<td style='text-align:center; font-weight: 600; color: #f59e0b;'>A-" . ($aIndex + 1) . "</td>";
                                    echo "<td>--</td>";
                                    echo "<td>--</td>";
                                    echo "<td contenteditable='true' onblur='updateCell(this, \"project_achievements\", ".$a['id'].", \"title\")'>" . htmlspecialchars($a['title']) . "</td>";
                                    echo "<td class='cell-target' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_achievements\", ".$a['id'].", \"target\")'>" . htmlspecialchars($a['target'] ?? '') . "</td>";
                                    echo "<td class='cell-achieved' contenteditable='true' oninput='autoCalc(this)' onblur='updateCell(this, \"project_achievements\", ".$a['id'].", \"description\")'>" . htmlspecialchars($a['description']) . "</td>";
                                    echo "<td class='cell-results' contenteditable='true' onblur='updateCell(this, \"project_achievements\", ".$a['id'].", \"results\")'>" . htmlspecialchars($a['results'] ?? '') . "</td>";
                                    echo "<td>
                                            <select style='width:100%; border:none; background:transparent;' onchange='updateCellSelect(this, \"project_achievements\", ".$a['id'].", \"status\")'>
                                                <option value=''>Select Status</option>";
                                                foreach($statuses as $st) {
                                                    $selected = ($a['status'] == $st['name'] || ($a['status'] == '' && $st['name'] == 'Verified')) ? 'selected' : '';
                                                    echo "<option value='".$st['name']."' $selected>".htmlspecialchars($st['name'])."</option>";
                                                }
                                    echo "  </select>
                                          </td>";
                                    echo "<td style='text-align:center;'>
                                            <div style='display:flex; gap:4px; justify-content:center;'>
                                                <button class='btn btn-primary btn-icon' style='background: #64748b; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='focusRow(this)' title='Edit Row'><i class='fa fa-pencil'></i></button>
                                                <button class='btn btn-primary btn-icon' style='background: #ef4444; font-size: 10px; width:26px; height:26px; border-radius:6px;' onclick='deleteEntry(this, \"project_achievements\", ".$a['id'].")' title='Delete Row'><i class='fa fa-trash'></i></button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 15px; font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center; padding: 0 24px 20px;">
                    <div>
                        * <b>Tip:</b> Click any cell to edit. <kbd>Shift</kbd> + <kbd>A</kbd> for quick add.
                    </div>
                </div>
            </div>

            <!-- Historical Weekly Records Section -->
            <div class="card" style="margin-top: 30px; border-top: 4px solid #94a3b8;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-weight: 600; color: var(--dark); margin: 0;"><i class="fa fa-archive"></i> Historical Weekly Records</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span style="font-size: 13px; color: var(--text-muted);">Select Date:</span>
                        <select id="snapshotSelector" onchange="loadSnapshot(this.value)" style="background: #f1f5f9; border: 1px solid var(--border); padding: 6px 12px;">
                            <option value="">-- Choose Week --</option>
                            <?php foreach($snapshots as $snap): ?>
                                <option value="<?=$snap['id']?>"><?=date('D, d M Y', strtotime($snap['snapshot_date']))?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div id="snapshotContainer" style="overflow-x: auto; display: none;">
                    <table class="excel-table" style="background: #fcfcfc;">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">ID</th>
                                <th>Category</th>
                                <th>Goal / Module</th>
                                <th>Date</th>
                                <th>Objectives</th>
                                <th>Target</th>
                                <th>Deadline</th>
                                <th>Achieved</th>
                                <th>Results %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="snapshotBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="snapshotPlaceholder" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">
                    Select a weekly record from the dropdown to view historical data.
                </div>
            </div>
            </div>
        </section>
	</div>

    <script>
    // Keyboard Shortcut: Shift + A to add entry
    window.addEventListener('keydown', function(e) {
        if (e.shiftKey && e.key === 'A') {
            // Check if user is not currently typing in a contenteditable cell or input
            if (!['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) && 
                !document.activeElement.isContentEditable) {
                e.preventDefault();
                addEntry(<?=$project_id?>);
            }
        }
    });

    function addObjective(goalId) {
        let formData = new FormData();
        formData.append('goal_id', goalId);
        formData.append('type', 'objective');

        fetch('app/create-blank-entry.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data === "Success") {
                location.reload(); 
            } else {
                alert("Error creating objective: " + data);
            }
        });
    }

    function addEntry(projectId) {
        let formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('type', 'goal');

        fetch('app/create-blank-entry.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data === "Success") {
                location.reload(); 
            } else {
                alert("Error creating row: " + data);
            }
        });
    }

    function focusRow(btn) {
        let row = btn.closest('tr');
        let editableCell = row.querySelector('[contenteditable="true"]');
        if (editableCell) {
            editableCell.focus();
            // Move cursor to end
            let range = document.createRange();
            let sel = window.getSelection();
            range.selectNodeContents(editableCell);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function deleteEntry(btn, table, id) {
        if(confirm("Are you sure you want to delete this row?")) {
            let formData = new FormData();
            formData.append('table', table);
            formData.append('id', id);

            fetch('app/delete-blank-entry.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if(data === "Success") {
                    btn.closest('tr').remove();
                } else {
                    alert("Error deleting row: " + data);
                }
            });
        }
    }

    function autoCalc(element) {
        let row = element.closest('tr');
        let targetCell = row.querySelector('.cell-target');
        let achievedCell = row.querySelector('.cell-achieved');
        let resultsCell = row.querySelector('.cell-results');

        if (!targetCell || !achievedCell || !resultsCell) return;

        let target = parseFloat(targetCell.innerText) || 0;
        let achieved = parseFloat(achievedCell.innerText) || 0;
        
        if (target !== 0) {
            let percentage = ((achieved / target) * 100).toFixed(2);
            resultsCell.innerText = percentage + "%";
            
            // Extract table/id from the blur event of results cell or targets
            // To auto-save results, we trigger its updateCell
            let blurAttr = resultsCell.getAttribute('onblur');
            if (blurAttr) {
                // regex to get table and id from updateCell(this, "table", id, "col")
                let match = blurAttr.match(/updateCell\(this, "(.*?)", (.*?), "(.*?)"\)/);
                if (match) {
                    sendUpdate(resultsCell, match[1], match[2], match[3], resultsCell.innerText);
                }
            }
        }
    }

    function updateCellSelect(element, table, id, column) {
        let value = element.value;
        sendUpdate(element, table, id, column, value);
    }

    function updateCell(element, table, id, column) {
        let value = element.innerText.trim();
        sendUpdate(element, table, id, column, value);
    }

    function sendUpdate(element, table, id, column, value) {
        let formData = new FormData();
        formData.append('table', table);
        formData.append('id', id);
        formData.append('column', column);
        formData.append('value', value);

        fetch('app/real-time-update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.startsWith("Success")) {
                element.classList.add('updated');
                setTimeout(() => { element.classList.remove('updated'); }, 1000);
                
                // If it's the start_date being updated, it returns "Success|DD-MM-YYYY HH:II"
                if (table === 'projects' && column === 'start_date' && data.includes('|')) {
                    let newDate = data.split('|')[1];
                    let display = document.getElementById('closingDateDisplay');
                    if (display) display.innerText = newDate;
                }
            } else {
                element.style.background = "rgba(220, 38, 38, 0.1)";
                console.error("Update error:", data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            element.style.backgroundColor = "rgba(255, 0, 0, 0.1)";
        });
    }

    // Category Management
    function showCategoryModal() {
        document.getElementById('categoryModal').style.display = 'flex';
    }
    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
    }
    function addCategory() {
        let name = document.getElementById('newCategoryName').value;
        if(!name) return;
        let formData = new FormData();
        formData.append('name', name);
        formData.append('project_id', <?=$project_id?>);
        fetch('app/add-category.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => { if(d === "Success") location.reload(); else alert(d); });
    }
    function deleteCategory(id) {
        if(!confirm('Are you sure? This will unassign this category from all goals.')) return;
        let formData = new FormData();
        formData.append('id', id);
        fetch('app/delete-category.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => { if(d === "Success") location.reload(); else alert(d); });
    }

    // Status Management
    function showStatusModal() {
        document.getElementById('statusModal').style.display = 'flex';
    }
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }
    function addStatus() {
        let name = document.getElementById('newStatusName').value;
        if(!name) return;
        let formData = new FormData();
        formData.append('name', name);
        formData.append('project_id', <?=$project_id?>);
        fetch('app/add-status.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => { if(d === "Success") location.reload(); else alert(d); });
    }
    function deleteStatus(id) {
        if(!confirm('Are you sure? Existing entries with this status will remain but the option will be gone.')) return;
        let formData = new FormData();
        formData.append('id', id);
        fetch('app/delete-status.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => { if(d === "Success") location.reload(); else alert(d); });
    }

    // Snapshot Management
    function saveWeeklySnapshot() {
        if(!confirm('Save current Master Sheet as this week\'s record?')) return;
        let formData = new FormData();
        formData.append('project_id', <?=$project_id?>);
        fetch('app/save-snapshot.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => {
            if(d === "Success") {
                alert('Weekly record saved successfully!');
                location.reload();
            } else {
                alert(d);
            }
        });
    }

    function updateWeeklySnapshot() {
        if(!confirm('Update the current historical record for this week with your latest changes?')) return;
        let formData = new FormData();
        formData.append('project_id', <?=$project_id?>);
        fetch('app/update-snapshot.php', { method: 'POST', body: formData })
        .then(r => r.text())
        .then(d => {
            if(d === "Success") {
                alert('Historical record updated successfully!');
                location.reload();
            } else {
                alert(d);
            }
        });
    }

    function loadSnapshot(snapshotId) {
        if(!snapshotId) {
            document.getElementById('snapshotContainer').style.display = 'none';
            document.getElementById('snapshotPlaceholder').style.display = 'block';
            return;
        }

        fetch('app/get-snapshot-data.php?id=' + snapshotId)
        .then(r => r.json())
        .then(data => {
            let body = document.getElementById('snapshotBody');
            body.innerHTML = '';
            data.forEach(item => {
                let rowClass = 'row-' + item.module_type;
                let tr = document.createElement('tr');
                tr.className = rowClass;
                
                // Simplified ID for snapshot
                let idPrefix = item.module_type.charAt(0).toUpperCase();
                
                tr.innerHTML = `
                    <td style="text-align:center; font-weight:600;">${idPrefix}</td>
                    <td>${item.category_name || '--'}</td>
                    <td>${item.title || '--'}</td>
                    <td>${item.date_ref || '--'}</td>
                    <td>${item.module_type === 'goal' ? '<i>Section Header</i>' : (item.title || '--')}</td>
                    <td>${item.target || '--'}</td>
                    <td>${item.date_ref || '--'}</td>
                    <td>${item.achieved || '--'}</td>
                    <td style="font-weight:600;">${item.results || '--'}</td>
                    <td><span class="badge" style="background:#e2e8f0; color:#475569;">${item.status || 'N/A'}</span></td>
                `;
                body.appendChild(tr);
            });
            document.getElementById('snapshotContainer').style.display = 'block';
            document.getElementById('snapshotPlaceholder').style.display = 'none';
        });
    }
    </script>
</body>
</html>
<?php 
} else {
    header("Location: login.php");
    exit();
}
?>
