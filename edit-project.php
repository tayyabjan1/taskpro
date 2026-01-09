<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && isset($_GET['id'])) {
	if ($_SESSION['role'] != 'admin') {
		header("Location: index.php");
		exit();
	}

	include "DB_connection.php";
	include "app/Model/Project.php";
	include "app/Model/User.php";

	$project = get_project_by_id($conn, $_GET['id']);
	if (!$project) {
		header("Location: projects.php?error=Project not found");
		exit();
	}

	$users = get_all_users($conn);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Modify Strategy | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
		.project-form-container {
			max-width: 900px;
			margin: 0 auto;
			background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
			padding: 3px;
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3);
		}
		.project-form-inner { background: #fff; padding: 40px; border-radius: 18px; }
		.form-header { text-align: center; margin-bottom: 40px; }
		.form-header h2 {
			font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 10px;
			display: flex; align-items: center; justify-content: center; gap: 15px;
		}
		.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
		.form-grid.full-width { grid-template-columns: 1fr; }
		.input-group label {
			display: block; margin-bottom: 10px; color: #475569; font-weight: 700; font-size: 13px;
			text-transform: uppercase; letter-spacing: 0.5px;
		}
		.modern-input {
			width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px;
			font-size: 15px; transition: all 0.3s ease; background: #f8fafc; outline: none;
		}
		.modern-input:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1); }
		textarea.modern-input { min-height: 120px; }
		.form-actions { display: flex; gap: 15px; margin-top: 35px; justify-content: center; }
		.btn-save {
			background: var(--primary); color: white; border: none; padding: 16px 40px; border-radius: 12px;
			font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(18, 123, 142, 0.3);
		}
		.btn-save:hover { transform: translateY(-2px); filter: brightness(1.1); }
		.btn-cancel {
			background: #f1f5f9; color: #64748b; border: none; padding: 16px 40px; border-radius: 12px;
			font-size: 16px; font-weight: 700; cursor: pointer; text-decoration: none;
		}
        .modal {
			display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
			background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); align-items: center; justify-content: center;
		}
		.modal-content { background: #fff; padding: 32px; width: 90%; max-width: 500px; border-radius: 20px; box-shadow: var(--shadow-lg); }
    </style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="project-form-container">
				<div class="project-form-inner">
					<div class="form-header">
						<h2><i class="fa fa-pencil-square" style="color: var(--primary);"></i> Modify Strategy</h2>
						<p style="color: #64748b;">Updating: <b><?=htmlspecialchars($project['name'])?></b></p>
					</div>

					<?php if (isset($_GET['error'])) { ?>
						<div class="alert alert-danger" style="background: #fef2f2; color: #dc2626; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc2626;">
							<i class="fa fa-exclamation-circle"></i> <?php echo $_GET['error']; ?>
						</div>
					<?php } ?>

					<form action="app/update-project.php" method="post">
						<input type="hidden" name="id" value="<?=$project['id']?>">
						
                        <div class="form-grid">
							<div class="input-group">
								<label>Project Name</label>
								<input type="text" name="name" class="modern-input" value="<?=htmlspecialchars($project['name'])?>" required>
							</div>

							<div class="input-group">
								<label>Global Status</label>
								<select name="status" class="modern-input" required>
                                    <option value="planning" <?=$project['status'] == 'planning' ? 'selected' : ''?>>📋 Planning</option>
                                    <option value="in_progress" <?=$project['status'] == 'in_progress' ? 'selected' : ''?>>🚀 In Progress</option>
                                    <option value="completed" <?=$project['status'] == 'completed' ? 'selected' : ''?>>✅ Completed</option>
                                    <option value="on_hold" <?=$project['status'] == 'on_hold' ? 'selected' : ''?>>⏸️ On Hold</option>
								</select>
							</div>
						</div>

						<div class="form-grid full-width">
							<div class="input-group">
								<label>initiative Description</label>
								<textarea name="description" class="modern-input"><?=htmlspecialchars($project['description'])?></textarea>
							</div>
						</div>

						<div class="form-grid full-width">
							<div class="input-group">
								<label>Assigned Strategy Lead(s)</label>
								<div style="display: flex; gap: 12px; align-items: flex-start;">
									<select name="assignees[]" id="employee_select" class="modern-input" multiple required style="flex: 1; height: 120px;">
										<?php 
                                        $current_assignees = get_project_assignees($conn, $project['id']);
                                        foreach ($users as $user) { ?>
												<option value="<?=$user['id']?>" <?=(in_array($user['id'], $current_assignees) ? 'selected' : '')?>>
													<?=htmlspecialchars($user['full_name'])?> (<?=ucfirst($user['role'])?>)
												</option>
										<?php } ?>
									</select>
									<button type="button" class="btn" onclick="openEmployeeModal()" style="padding: 12px 20px; border-radius: 12px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600;">+ New User</button>
								</div>
                                <p style="font-size: 11px; color: #777; margin-top: 5px;">Hold Ctrl (Windows) or Command (Mac) to select multiple leads.</p>
							</div>
						</div>

						<div class="form-grid">
							<div class="input-group">
								<label>Start Date</label>
								<input type="date" name="start_date" class="modern-input" value="<?=$project['start_date']?>">
							</div>

							<div class="input-group">
								<label>Final Deadline</label>
								<input type="date" name="deadline" class="modern-input" value="<?=$project['deadline']?>">
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" class="btn-save">
								<i class="fa fa-save"></i> Save Changes
							</button>
							<a href="projects.php" class="btn-cancel">
								Cancel
							</a>
						</div>
					</form>
				</div>
			</div>
		</section>
	</div>

	<!-- Employee Modal -->
	<div id="employeeModal" class="modal">
		<div class="modal-content">
            <h3 style="margin-bottom: 24px; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 12px;">
                <i class="fa fa-user-plus" style="color: var(--primary);"></i> Add Strategy Lead
            </h3>
            <form id="employeeForm">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" id="emp_full_name" class="modern-input" placeholder="Enter full name" required>
                </div>
                <div class="form-grid" style="margin-top: 20px;">
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" id="emp_username" class="modern-input" placeholder="Username" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" id="emp_password" class="modern-input" placeholder="Password" required>
                    </div>
                </div>
                <div id="employee_message" style="margin-top: 15px;"></div>
                <div class="form-actions" style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <button type="button" class="btn-save" onclick="addEmployee()" style="padding: 12px 24px; font-size: 14px;">Add Lead</button>
                    <button type="button" class="btn-cancel" onclick="closeEmployeeModal()" style="padding: 12px 24px; font-size: 14px;">Dismiss</button>
                </div>
            </form>
		</div>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(2)");
	if(active) active.classList.add("active");

	function openEmployeeModal() { document.getElementById('employeeModal').style.display = 'flex'; }
	function closeEmployeeModal() {
		document.getElementById('employeeModal').style.display = 'none';
		document.getElementById('employeeForm').reset();
		document.getElementById('employee_message').innerHTML = '';
	}

	function addEmployee() {
		const fullName = document.getElementById('emp_full_name').value;
		const username = document.getElementById('emp_username').value;
		const password = document.getElementById('emp_password').value;

		if (!fullName || !username || !password) {
			document.getElementById('employee_message').innerHTML = '<div style="color: #dc2626; font-size: 12px; font-weight: 700;">All fields required</div>';
			return;
		}

		const formData = new FormData();
		formData.append('full_name', fullName);
		formData.append('username', username);
		formData.append('password', password);
		formData.append('role', 'employee');

		fetch('app/add-user.php', { method: 'POST', body: formData })
		.then(response => response.text())
		.then(data => {
			if (data.includes('success') || !data.includes('error')) location.reload();
			else document.getElementById('employee_message').innerHTML = '<div style="color: #dc2626; font-size: 12px;">Error: ' + data + '</div>';
		});
	}

	window.onclick = function(event) {
		const modal = document.getElementById('employeeModal');
		if (event.target == modal) closeEmployeeModal();
	}
</script>
</body>
</html>
<?php }else{ 
	header("Location: login.php?error=First login");
	exit();
}
?>
