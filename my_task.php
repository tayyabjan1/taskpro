<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

    $tasks = get_all_tasks_by_id($conn, $_SESSION['id']);
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>My Tasks | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pending { background: #fee2e2; color: #ef4444; }
        .status-in_progress { background: #dcfce7; color: #22c55e; }
        .status-completed { background: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 24px; font-weight: 700; color: var(--dark); margin: 0;">My Personal Tasks</h2>
                <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 14px;">Review and manage your daily operational workload.</p>
            </div>

			<?php if (isset($_GET['success'])) {?>
                <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #bbf7d0;">
                    <i class="fa fa-check-circle" style="margin-right: 8px;"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
			<?php } ?>

			<?php if ($tasks != 0) { ?>
			<div style="background: #fff; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-md);">
                <table class="main-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                            <th style="padding: 16px 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">#</th>
                            <th style="padding: 16px 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Task Overview</th>
                            <th style="padding: 16px 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Due Date</th>
                            <th style="padding: 16px 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Status</th>
                            <th style="padding: 16px 20px; text-align: center; font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=0; foreach ($tasks as $task) { ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='white'">
                            <td style="padding: 16px 20px; font-size: 14px; color: var(--text-muted);"><?=++$i?></td>
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: var(--dark); font-size: 14px;"><?=$task['title']?></div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;"><?=substr($task['description'], 0, 80)?><?=strlen($task['description']) > 80 ? '...' : ''?></div>
                            </td>
                            <td style="padding: 16px 20px;">
                                <div style="font-size: 13px; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-calendar-check-o" style="color: var(--text-muted);"></i>
                                    <?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo "<span style='color:#94a3b8;'>Open-ended</span>";
                                          else echo date('D, d M Y', strtotime($task['due_date']));
                                    ?>
                                </div>
                            </td>
                            <td style="padding: 16px 20px;">
                                <span class="status-badge status-<?=$task['status']?>">
                                    <?=str_replace('_', ' ', $task['status'])?>
                                </span>
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <a href="edit-task-employee.php?id=<?=$task['id']?>" style="padding: 8px 16px; border-radius: 8px; background: var(--primary); color: #fff; text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <i class="fa fa-pencil-square"></i> Update Status
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
			<?php } else { ?>
                <div style="text-align: center; padding: 80px 40px; background: #fff; border-radius: 16px; border: 1px dashed #cbd5e1;">
                    <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fa fa-check" style="font-size: 24px; color: #94a3b8;"></i>
                    </div>
                    <h3 style="color: var(--dark); margin-bottom: 8px;">No tasks assigned</h3>
                    <p style="color: var(--text-muted); font-size: 14px;">You're all caught up! Enjoy your day.</p>
                </div>
			<?php } ?>
		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(2)");
	if(active) active.classList.add("active");
</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>