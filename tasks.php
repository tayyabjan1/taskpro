<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

    $selected_date = isset($_GET['date']) ? $_GET['date'] : null;
    $task_record_dates = get_distinct_task_dates($conn);
    $users = get_all_users($conn);

    if ($selected_date) {
        $tasks = get_tasks_by_date($conn, $selected_date);
    } else {
        $sql = "SELECT t.*, u.full_name as assigned_name 
                FROM tasks t 
                LEFT JOIN users u ON t.assigned_to = u.id 
                ORDER BY t.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tasks = $stmt->fetchAll();
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Task Control Center | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .task-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }
        
        /* Integrated Entry Form */
        .entry-container {
            background: #fff;
            padding: 24px;
            border-radius: var(--radius-3d);
            margin-bottom: 30px;
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            animation: fadeInDown 0.5s ease-out;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .entry-form {
            display: grid;
            grid-template-columns: 1.5fr 2fr 1.2fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .form-field label {
            display: block; font-size: 11px; font-weight: 800;
            color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;
        }
        .entry-input {
            width: 100%; padding: 10px 14px; border-radius: 12px;
            border: 1px solid #e2e8f0; font-size: 13.5px; outline: none;
            background: #f8fafc; transition: 0.3s;
        }
        .entry-input:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 3px rgba(18, 123, 142, 0.1); }

        .task-board {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .board-column {
            background: rgba(248, 250, 252, 0.4);
            border-radius: var(--radius-3d);
            padding: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            min-height: 500px;
        }
        .column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .column-title {
            font-size: 15px; font-weight: 800; color: var(--dark);
            text-transform: uppercase; display: flex; align-items: center; gap: 10px;
        }
        .count-badge { background: #fff; padding: 4px 12px; border-radius: 10px; font-size: 12px; font-weight: 700; border: 1px solid #e2e8f0; }

        .task-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .task-card:hover { transform: translateY(-5px); box-shadow: 20px 20px 45px rgba(0, 0, 0, 0.12); }
        .t-title { font-weight: 700; color: var(--dark); font-size: 16px; margin-bottom: 6px; }
        .t-desc { font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 15px; }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Highlight Cards */
        .highlight-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .highlight-card {
            background: #fff;
            padding: 20px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
            box-shadow: var(--shadow-3d);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .highlight-card:hover { transform: translateY(-3px); }
        .h-icon {
            width: 45px; height: 45px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .h-info h4 { margin: 0; font-size: 20px; font-weight: 800; color: var(--dark); }
        .h-info p { margin: 2px 0 0 0; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }

        @media (max-width: 1100px) {
            .entry-form { grid-template-columns: 1fr 1fr; }
            .entry-submit { grid-column: 1 / -1; }
            .highlight-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .task-board { grid-template-columns: 1fr; }
            .highlight-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <!-- Header -->
                <div class="task-header-bar">
                    <div>
                        <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">
                            <?= $selected_date ? "Archive: ".date('d M Y', strtotime($selected_date)) : "Active Task Management" ?>
                        </h2>
                        <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 14px;">Unified mission control and historical workload records.</p>
                    </div>
                    <select class="entry-input" style="width: auto; background: #fff;" onchange="location.href='tasks.php?date=' + this.value">
                        <option value="">Archive History...</option>
                        <?php foreach($task_record_dates as $rd): ?>
                            <option value="<?=$rd['due_date']?>" <?=($selected_date == $rd['due_date'] ? 'selected' : '')?>><?=date('d M Y', strtotime($rd['due_date']))?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        <i class="fa fa-check-circle"></i> <?=$_GET['success']?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                        <i class="fa fa-exclamation-circle"></i> <?=$_GET['error']?>
                    </div>
                <?php endif; ?>

                <!-- Task Highlights -->
                <div class="highlight-grid">
                    <?php 
                        $total_tasks = count($tasks);
                        $active_tasks = count(array_filter($tasks, function($t) { return $t['status'] != 'completed'; }));
                        $completed_tasks = count(array_filter($tasks, function($t) { return $t['status'] == 'completed'; }));
                        $overdue_tasks = count(array_filter($tasks, function($t) { 
                            return $t['status'] != 'completed' && !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $t['due_date'] != '0000-00-00'; 
                        }));
                    ?>
                    <div class="highlight-card">
                        <div class="h-icon" style="background: rgba(18, 123, 142, 0.1); color: var(--primary);"><i class="fa fa-list-ul"></i></div>
                        <div class="h-info">
                            <h4><?=$total_tasks?></h4>
                            <p>Total Tasks</p>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <div class="h-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa fa-bolt"></i></div>
                        <div class="h-info">
                            <h4><?=$active_tasks?></h4>
                            <p>Active</p>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <div class="h-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
                        <div class="h-info">
                            <h4><?=$overdue_tasks?></h4>
                            <p>Overdue</p>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <div class="h-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa fa-check-circle"></i></div>
                        <div class="h-info">
                            <h4><?=$completed_tasks?></h4>
                            <p>Executed</p>
                        </div>
                    </div>
                </div>

                <!-- Fast Entry Form -->
                <?php if (!$selected_date): ?>
                <div class="entry-container">
                    <form action="app/add-task.php" method="POST" class="entry-form">
                        <input type="hidden" name="redirect" value="tasks.php">
                        <div class="form-field">
                            <label>Mission Title</label>
                            <input type="text" name="title" class="entry-input" placeholder="What needs to be done?" required>
                        </div>
                        <div class="form-field">
                            <label>Task Directive / Description</label>
                            <input type="text" name="description" class="entry-input" placeholder="Provide instructions..." required>
                        </div>
                        <div class="form-field">
                            <label>Assign to Team</label>
                            <select name="assigned_to" class="entry-input">
                                <option value="0">Unassigned...</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?=$user['id']?>"><?=$user['full_name']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="entry-input" value="<?=date('Y-m-d')?>">
                        </div>
                        <div class="entry-submit">
                            <button type="submit" style="background: var(--primary); color: #fff; border:none; padding: 12px 25px; border-radius: 12px; font-weight: 800; cursor:pointer; height: 42px; width: 100%;">
                                <i class="fa fa-paper-plane"></i> LAUNCH
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="task-board">
                    <!-- TO DO -->
                    <div class="board-column">
                        <?php $todo = array_filter($tasks, function($t) { return $t['status'] != 'completed'; }); ?>
                        <div class="column-header">
                            <div class="column-title"><i class="fa fa-bolt" style="color: #f59e0b;"></i> Action Items</div>
                            <span class="count-badge"><?=count($todo)?> active</span>
                        </div>
                        <?php foreach($todo as $t): ?>
                            <div class="task-card" style="<?= $t['status'] == 'in_progress' ? 'border-left: 5px solid var(--primary);' : '' ?>">
                                <div class="t-title"><?=htmlspecialchars($t['title'])?></div>
                                <div class="t-desc"><?=htmlspecialchars(substr($t['description'], 0, 100))?>...</div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; margin-top: 15px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                                    <span style="background: rgba(18, 123, 142, 0.1); color: var(--primary); padding: 4px 8px; border-radius: 6px; font-weight: 700;">@<?=$t['assigned_name'] ?: 'N/A'?></span>
                                    <a href="edit-task.php?id=<?=$t['id']?>" style="color: #94a3b8;"><i class="fa fa-pencil"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- DONE -->
                    <div class="board-column">
                        <?php $done = array_filter($tasks, function($t) { return $t['status'] == 'completed'; }); ?>
                        <div class="column-header">
                            <div class="column-title"><i class="fa fa-check-circle" style="color: #10b981;"></i> Executed</div>
                            <span class="count-badge"><?=count($done)?> done</span>
                        </div>
                        <?php foreach($done as $t): ?>
                            <div class="task-card" style="opacity: 0.7;">
                                <div class="t-title" style="text-decoration: line-through; color: #94a3b8;"><?=htmlspecialchars($t['title'])?></div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; margin-top: 10px;">
                                    <span style="color: #10b981; font-weight: 700;"><i class="fa fa-check"></i> Completed by <?=$t['assigned_name']?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
		</section>
	</div>
</body>
</html>
<?php } else { header("Location: login.php"); exit(); } ?>
