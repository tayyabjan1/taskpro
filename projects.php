<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {
	include "DB_connection.php";
	include "app/Model/Project.php";
	include "app/Model/User.php";

    if ($_SESSION['role'] == 'admin') {
	    $projects = get_all_projects($conn);
    } else {
        // Employee sees only their projects
        $projects = get_my_projects($conn, $_SESSION['id']);
    }
	$users = get_all_users($conn);
    
    // Stats for the top bar
    $total_p = count($projects);
    $active_p = 0;
    $completed_p = 0;
    foreach($projects as $p) {
        if($p['status'] == 'completed') $completed_p++;
        else $active_p++;
    }
?>
<!DOCTYPE html>
<html>
<head>
	<title>Strategy Hub | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .project-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1100px) {
            .project-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .project-stats-grid { grid-template-columns: 1fr; }
            .search-row { 
                flex-direction: column; 
                align-items: stretch !important; 
                gap: 15px; 
            }
        }

        .stat-project-card {
            background: #fff;
            padding: 25px;
            border-radius: var(--radius-3d);
            border: 1px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--shadow-3d);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
            transition: var(--transition);
        }
        .stat-project-card:hover { transform: translateY(-5px); }
        
        .stat-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .project-table-container {
            background: #fff;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            overflow-x: auto;
            width: 100%;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .p-status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .p-status-pending { background: #fef9c3; color: #854d0e; }
        .p-status-active { background: #dcfce7; color: #166534; }
        .p-status-completed { background: #e0f2fe; color: #0369a1; }
        .p-status-on_hold { background: #fef2f2; color: #991b1b; }

        .p-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.2s;
            font-size: 14px;
            margin: 0 2px;
        }
        .p-view { background: #f1f5f9; color: #475569; }
        .p-edit { background: #eff6ff; color: #2563eb; }
        .p-del { background: #fef2f2; color: #dc2626; }
        .p-action-btn:hover { transform: translateY(-2px); filter: brightness(0.95); }

        .search-row {
            padding: 16px 24px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-input-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            width: 300px;
        }
        .search-input-box input {
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
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
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
                <div>
                    <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">Strategy Hub</h2>
                    <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 14px;">Master control for all high-level organizational projects.</p>
                </div>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="create_project.php" class="btn" style="background: var(--primary); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 14px rgba(18, 123, 142, 0.4); display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-plus"></i> Launch New Project
                </a>
                <?php endif; ?>
            </div>

            <!-- Stats Bar -->
            <div class="project-stats-grid">
                <div class="stat-project-card">
                    <div class="stat-icon-box" style="background: #eff6ff; color: #3b82f6;"><i class="fa fa-folder-open"></i></div>
                    <div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--dark);"><?=$total_p?></div>
                        <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Total Projects</div>
                    </div>
                </div>
                <div class="stat-project-card">
                    <div class="stat-icon-box" style="background: #fdfaf2; color: #f59e0b;"><i class="fa fa-spinner fa-spin-hover"></i></div>
                    <div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--dark);"><?=$active_p?></div>
                        <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Ongoing Execution</div>
                    </div>
                </div>
                <div class="stat-project-card">
                    <div class="stat-icon-box" style="background: #f0fdf4; color: #22c55e;"><i class="fa fa-check-circle"></i></div>
                    <div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--dark);"><?=$completed_p?></div>
                        <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Verified Success</div>
                    </div>
                </div>
            </div>

			<?php if (isset($_GET['success'])) { ?>
				<div style="background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; border: 1px solid #bbf7d0;">
					<i class="fa fa-check-circle-o"></i> <?php echo $_GET['success']; ?>
				</div>
			<?php } ?>

            <!-- Premium 3D Project Grid -->
            <div class="search-row" style="margin-bottom: 30px; border-radius: 20px; box-shadow: var(--shadow-sm);">
                <div class="search-input-box" style="width: 100%;">
                    <i class="fa fa-search" style="color: #94a3b8;"></i>
                    <input type="text" id="projectSearch" placeholder="Search strategy maps, leads, or status...">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn" style="padding: 10px 20px; font-size: 13px; font-weight: 700; background: #fff; border: 1px solid #e2e8f0; color: #64748b; border-radius: 12px; box-shadow: var(--shadow-sm);" onclick="window.print()"><i class="fa fa-print"></i> REPORT</button>
                </div>
            </div>

            <div id="projectGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
                <?php if (!empty($projects)) { 
                    foreach ($projects as $project) { 
                        // Progress Calculation
                        $now = time();
                        $start = strtotime($project['start_date']);
                        $end = strtotime($project['deadline']);
                        $pct = 0;
                        if($now > $start && $end > $start) {
                            $pct = (($now - $start) / ($end - $start)) * 100;
                            $pct = min(100, max(0, $pct));
                        }
                        $status_colors = [
                            'planning' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'bar' => '#eab308'],
                            'in_progress' => ['bg' => '#dcfce7', 'text' => '#166534', 'bar' => '#22c55e'],
                            'completed' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'bar' => '#3b82f6'],
                            'on_hold' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'bar' => '#ef4444']
                        ];
                        $s_conf = $status_colors[$project['status']] ?? $status_colors['planning'];
                ?>
                    <div class="premium-card project-item-card" style="padding: 25px; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                <div style="width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, rgba(18, 123, 142, 0.1), rgba(18, 123, 142, 0.05)); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid rgba(18, 123, 142, 0.1);">
                                    <i class="fa fa-folder-open"></i>
                                </div>
                                <span class="p-status-badge" style="background: <?=$s_conf['bg']?>; color: <?=$s_conf['text']?>;">
                                    <?=str_replace('_', ' ', $project['status'])?>
                                </span>
                            </div>
                            
                            <h3 style="font-size: 18px; font-weight: 800; color: var(--dark); margin-bottom: 8px; line-height: 1.3;">
                                <?=htmlspecialchars($project['name'])?>
                            </h3>
                            <p style="font-size: 13px; color: #64748b; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 20px;">
                                <?=htmlspecialchars($project['description'])?>
                            </p>

                            <!-- Timeline & Progress -->
                            <div style="margin-bottom: 20px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;">
                                    <span>Progress</span>
                                    <span><?=round($pct)?>%</span>
                                </div>
                                <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?=$pct?>%; height: 100%; background: <?=$s_conf['bar']?>; border-radius: 3px;"></div>
                                </div>
                                <div style="margin-top: 8px; font-size: 12px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-clock-o"></i> Due: <?=date('M d, Y', strtotime($project['deadline']))?>
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Team & Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: auto;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #64748b; border: 2px solid #fff;">
                                    <?=strpos($project['employee_names'], ',') !== false ? '<i class="fa fa-users"></i>' : strtoupper(substr($project['employee_names'] ?? 'U', 0, 1))?>
                                </div>
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= $project['employee_names'] ? htmlspecialchars($project['employee_names']) : 'Unassigned' ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; gap: 5px;">
                                <a href="project-view.php?id=<?=$project['id']?>" class="btn" style="padding: 8px 12px; border-radius: 8px; background: var(--primary); color: #fff; font-size: 12px; text-decoration: none; transition: 0.2s;"><i class="fa fa-arrow-right"></i> Open</a>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="edit-project.php?id=<?=$project['id']?>" style="padding: 8px; color: #64748b; font-size: 14px;"><i class="fa fa-pencil"></i></a>
                                    <a href="delete-project.php?id=<?=$project['id']?>" style="padding: 8px; color: #ef4444; font-size: 14px;" onclick="return confirm('Delete this project?')"><i class="fa fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php } 
                } else { ?>
                    <div style="grid-column: 1 / -1; padding: 60px; text-align: center; background: #fff; border-radius: 20px; border: 2px dashed #e2e8f0;">
                        <div style="font-size: 40px; color: #cbd5e1; margin-bottom: 15px;"><i class="fa fa-folder-open-o"></i></div>
                        <h3 style="color: var(--text-main); font-weight: 700;">No Projects Found</h3>
                        <p style="color: #94a3b8; margin-top: 5px;">Get started by launching a new strategic initiative.</p>
                    </div>
                <?php } ?>
            </div>
		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(2)");
	if(active) active.classList.add("active");

    // Live Search Logic for Card Grid
    document.getElementById('projectSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let cards = document.querySelectorAll('.project-item-card');
        cards.forEach(card => {
            let text = card.innerText.toLowerCase();
            card.style.display = text.includes(val) ? 'flex' : 'none';
        });
    });
</script>
</body>
</html>
<?php }else{ 
	header("Location: login.php?error=First login");
	exit();
}
?>
