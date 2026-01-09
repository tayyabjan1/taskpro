<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: tasks.php");
    	 exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);

    if ($task == 0) {
    	 header("Location: tasks.php");
    	 exit();
    }
   $users = get_all_users($conn);
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Task | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }
        .form-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .label-text {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .modern-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            color: var(--dark);
            transition: var(--transition);
            background: #f8fafc;
            outline: none;
            margin-bottom: 20px;
        }
        .modern-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1);
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(18, 123, 142, 0.3);
            filter: brightness(1.1);
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
                <a href="tasks.php" style="text-decoration: none; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                    <i class="fa fa-arrow-left" style="margin-right: 6px;"></i> Back to All Tasks
                </a>
            </div>

            <div class="form-container">
                <h2 class="form-title">
                    <i class="fa fa-pencil-square" style="color: var(--primary);"></i>
                    Update Task Details
                </h2>

                <form method="POST" action="app/update-task.php">
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
                        <label class="label-text">Task Title</label>
                        <input type="text" name="title" class="modern-input" placeholder="e.g., Weekly Strategic Review" value="<?=htmlspecialchars($task['title'])?>" required>
                    </div>

                    <div class="input-holder">
                        <label class="label-text">Description</label>
                        <textarea name="description" class="modern-input" style="height: 120px; resize: none;" placeholder="Provide detailed instructions for the task..." required><?=htmlspecialchars($task['description'])?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="input-holder">
                            <label class="label-text">Due Date</label>
                            <input type="date" name="due_date" class="modern-input" value="<?=$task['due_date']?>">
                        </div>
                        <div class="input-holder">
                            <label class="label-text">Assign Responsible</label>
                            <select name="assigned_to" class="modern-input">
                                <option value="0" <?=($task['assigned_to'] == null ? 'selected' : '')?>>Unassigned</option>
                                <?php if ($users !=0) { 
                                    foreach ($users as $user) {
                                ?>
                                    <option value="<?=$user['id']?>" <?=($task['assigned_to'] == $user['id'] ? 'selected' : '')?>><?=$user['full_name']?></option>
                                <?php } } ?>
                            </select>
                        </div>
                    </div>

                    <input type="text" name="id" value="<?=$task['id']?>" hidden>

                    <button type="submit" class="submit-btn" style="background: #0d9488;">
                        <i class="fa fa-refresh"></i>
                        Update Task Info
                    </button>
                </form>
            </div>
		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(3)");
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