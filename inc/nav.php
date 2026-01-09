<?php
// Get ALL projects for sidebar
if (file_exists("app/Model/Project.php")) {
	include_once "app/Model/Project.php";
	if (function_exists('get_all_projects')) {
        if ($_SESSION['role'] == 'admin') {
		    $all_sidebar_projects = get_all_projects($conn);
        } else {
            $all_sidebar_projects = get_my_projects($conn, $_SESSION['id']);
        }
	} else {
		$all_sidebar_projects = [];
	}
} else {
    $all_sidebar_projects = [];
}

// Get task dates for dropdown
if (file_exists("app/Model/Task.php")) {
	include_once "app/Model/Task.php";
	if (function_exists('get_distinct_task_dates')) {
		$task_record_dates = get_distinct_task_dates($conn);
	} else {
		$task_record_dates = [];
	}
} else {
    $task_record_dates = [];
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$active_project_id = isset($_GET['id']) ? $_GET['id'] : null;
$active_task_date = isset($_GET['date']) ? $_GET['date'] : null;
?>
<nav class="side-bar">
			<div class="user-p">
				<img src="img/user.png">
				<h4>@<?=$_SESSION['username']?></h4>
			</div>
			
			<?php 
               if($_SESSION['role'] == "employee"){
			 ?>
			 <!-- Employee Navigation Bar -->
			<ul id="navList">
				<li>
					<a href="index.php">
						<i class="fa fa-tachometer" aria-hidden="true"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li class="<?=($current_page == 'my_task.php'?'active':'')?>">
					<a href="my_task.php">
						<i class="fa fa-tasks" aria-hidden="true"></i>
						<span>My Task</span>
					</a>
				</li>
				<li class="<?=($current_page == 'profile.php'?'active':'')?>">
					<a href="profile.php">
						<i class="fa fa-user" aria-hidden="true"></i>
						<span>Profile</span>
					</a>
				</li>
				<li class="<?=($current_page == 'notifications.php'?'active':'')?>">
					<a href="notifications.php">
						<i class="fa fa-bell" aria-hidden="true"></i>
						<span>Notifications</span>
					</a>
				</li>
			</ul>
		<?php }else { ?>
			<!-- Admin Navigation Bar -->
            <ul id="navList">
				<li class="<?=($current_page == 'index.php'?'active':'')?>">
					<a href="index.php">
						<i class="fa fa-tachometer" aria-hidden="true"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li class="<?=($current_page == 'projects.php'?'active':'')?>">
					<a href="projects.php">
						<i class="fa fa-folder" aria-hidden="true"></i>
						<span>All Projects</span>
					</a>
				</li>
				<li class="<?=($current_page == 'user.php' || $current_page == 'add-user.php' || $current_page == 'edit-user.php' ? 'active' : '')?>">
					<a href="user.php">
						<i class="fa fa-users" aria-hidden="true"></i>
						<span>Employees</span>
					</a>
				</li>

				<li class="<?=($current_page == 'collaboration.php' ? 'active' : '')?>">
					<a href="collaboration.php">
						<i class="fa fa-handshake-o" aria-hidden="true"></i>
						<span>Collaboration Hub</span>
					</a>
				</li>


				<li class="<?=($current_page == 'tasks.php' && !$active_task_date ? 'active' : '')?>">
					<a href="tasks.php">
						<i class="fa fa-file-text-o" aria-hidden="true"></i>
						<span>Task Records</span>
					</a>
				</li>
			</ul>

			<!-- All Projects Section -->
			<?php if (!empty($all_sidebar_projects)) { ?>
			<div class="projects-sidebar">
				<ul class="project-list">
					<?php foreach ($all_sidebar_projects as $side_proj) { ?>
					<li class="project-item <?=($active_project_id == $side_proj['id']?'active':'')?>">
						<a href="project-view.php?id=<?=$side_proj['id']?>">
							<i class="fa fa-folder-o"></i>
							<span class="project-name"><?=htmlspecialchars(substr($side_proj['name'], 0, 20))?><?=strlen($side_proj['name']) > 20 ? '...' : ''?></span>
						</a>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		<?php } ?>

		<div class="sidebar-footer">
			<a href="logout.php" class="logout-link">
				<i class="fa fa-sign-out"></i>
				<span>Logout</span>
			</a>
		</div>
</nav>