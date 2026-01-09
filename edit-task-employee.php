<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: my_task.php");
    	 exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);

    if ($task == 0 || $task['assigned_to'] != $_SESSION['id']) {
    	 header("Location: my_task.php");
    	 exit();
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Update Task Status | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .form-container {
            max-width: 550px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }
        .task-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .task-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 8px 0;
        }
        .task-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }
        .label-text {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .modern-select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            background: #f8fafc;
            outline: none;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 24px;
        }
        .modern-select:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1);
        }
        .btn-update {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .btn-update:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(18, 123, 142, 0.3);
        }
    </style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div style="margin-bottom: 30px;">
                <a href="my_task.php" style="text-decoration: none; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                    <i class="fa fa-arrow-left" style="margin-right: 6px;"></i> Back to My Tasks
                </a>
            </div>

            <div class="form-container">
                <div class="task-header">
                    <h3 class="task-title"><?=htmlspecialchars($task['title'])?></h3>
                    <p class="task-desc"><?=nl2br(htmlspecialchars($task['description']))?></p>
                </div>

                <form method="POST" action="app/update-task-employee.php">
                    <?php if (isset($_GET['error'])) {?>
                        <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fecaca;">
                            <i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>
                            <?php echo stripcslashes($_GET['error']); ?>
                        </div>
                    <?php } ?>

                    <?php if (isset($_GET['success'])) {?>
                        <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border: 1px solid #bbf7d0;">
                            <i class="fa fa-check-circle" style="margin-right: 8px;"></i>
                            <?php echo stripcslashes($_GET['success']); ?>
                        </div>
                    <?php } ?>

                    <div class="input-holder">
                        <label class="label-text">Select Execution Status</label>
                        <select name="status" class="modern-select">
                            <option value="pending" <?=($task['status'] == "pending" ? "selected" : "")?>>Pending</option>
                            <option value="in_progress" <?=($task['status'] == "in_progress" ? "selected" : "")?>>In Progress</option>
                            <option value="completed" <?=($task['status'] == "completed" ? "selected" : "")?>>Completed</option>
                        </select>
                    </div>

                    <input type="text" name="id" value="<?=$task['id']?>" hidden>

                    <button type="submit" class="btn-update">
                        <i class="fa fa-save"></i>
                        Confirm Status Update
                    </button>
                </form>
            </div>
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