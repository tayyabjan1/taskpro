<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    include "app/Model/Department.php";

    $users = get_all_users($conn);
    $departments = get_all_departments($conn);
    $total_employees = count_users($conn);
    
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'employees';
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Resource Intelligence | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f6f7fb;
            --c-orange: #fe9365;
            --c-green: #0ac282;
            --c-pink: #fe5d70;
            --c-cyan: #01a9ac;
            --c-dark: #404e67;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        body { font-family: 'Open Sans', sans-serif; background-color: var(--bg-body); color: var(--c-dark); }
        .emp-container { padding: 30px; }
        
        /* Premium Header */
        .emp-header {
            background: #fff;
            padding: 25px 35px;
            border: 1px solid rgba(255,255,255,0.4);
            border-bottom: none;
            border-radius: var(--radius-3d) var(--radius-3d) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-3d);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .emp-header h2 { margin: 0; font-size: 18px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1px; }
        
        .tab-nav {
            background: #fff;
            padding: 0 35px;
            display: flex;
            gap: 25px;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid #f1f5f9;
            margin-bottom: 30px;
            border-radius: 0 0 var(--radius-3d) var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .tab-link {
            padding: 18px 0;
            color: #888;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tab-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .add-btn {
            background: var(--primary);
            color: #fff; padding: 12px 24px; border-radius: 12px;
            text-decoration: none; font-size: 12px; font-weight: 800;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(18, 123, 142, 0.3);
        }
        .add-btn:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Stats Blocks */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .emp-header { 
                flex-direction: column; 
                align-items: flex-start !important; 
                gap: 15px; 
            }
            .tab-nav {
                overflow-x: auto;
                padding: 0 15px;
            }
            .emp-container { padding: 15px; }
        }

        .stat-block {
            background: #fff;
            padding: 25px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            border-left: 5px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left-width: 5px;
        }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            background: #f0f4f8; display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 20px;
        }
        .stat-details h4 { margin: 0; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
        .stat-details .value { font-size: 26px; font-weight: 800; color: var(--dark); }

        /* Employee Table */
        .white-card {
            background: #fff;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .emp-table { width: 100%; border-collapse: collapse; }
        .emp-table th {
            background: #fafbfc;
            text-align: left; padding: 18px 25px;
            font-size: 11px; font-weight: 800; color: #94a3b8;
            text-transform: uppercase; border-bottom: 1px solid #eee;
        }
        .emp-table td { padding: 18px 25px; border-bottom: 1px solid #f1f1f1; font-size: 14px; color: #444; }
        
        .emp-info { display: flex; align-items: center; gap: 15px; }
        .emp-avatar {
            width: 40px; height: 40px; border-radius: 10px;
            background: var(--dark); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px;
        }
        
        .badge {
            padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: 700;
            text-transform: uppercase;
        }
        .badge-task { background: #e0f2f1; color: #00897b; }
        .badge-project { background: #e3f2fd; color: #1e88e5; }
        
        .action-links { display: flex; gap: 8px; }
        .btn-sm {
            padding: 6px 14px; border-radius: 4px; text-decoration: none;
            font-size: 11px; font-weight: 600; border: none; cursor: pointer;
        }
        .btn-edit { background: #f1f5f9; color: var(--c-dark); }
        .btn-delete { background: #fff1f2; color: #e11d48; }
        .btn-pw { background: #f0fdf4; color: #16a34a; }

        /* Modal */
        .modal {
            display: none; position: fixed; top: 0; left: 0; 
            width: 100%; height: 100%; background: rgba(0,0,0,0.5);
            z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; padding: 25px; border-radius: 8px;
            width: 400px; max-width: 90%;
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
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
                <div class="emp-container">
                <div class="emp-header">
                    <h2>Resource Intel Hub</h2>
                    <?php if($tab == 'employees'): ?>
                        <a href="add-user.php" class="add-btn"><i class="fa fa-user-plus"></i> NEW EMPLOYEE</a>
                    <?php else: ?>
                        <button onclick="showDeptModal()" class="add-btn"><i class="fa fa-plus"></i> NEW DEPARTMENT</button>
                    <?php endif; ?>
                </div>
                
                <div class="tab-nav">
                    <a href="user.php?tab=employees" class="tab-link <?=$tab=='employees'?'active':''?>">EMPLOYEES</a>
                    <a href="user.php?tab=departments" class="tab-link <?=$tab=='departments'?'active':''?>">DEPARTMENTS</a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success" style="background: var(--c-green); color: #fff; padding: 15px; border-radius: 5px; margin-bottom: 25px; font-size: 12px;">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <div class="white-card">
                    <?php if($tab == 'employees'): ?>
                    <table class="emp-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Workload</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?=htmlspecialchars($user['full_name'])?></div>
                                    <div style="font-size: 11px; color: #999;">@<?=htmlspecialchars($user['username'])?></div>
                                </td>
                                <td><span style="color: var(--c-cyan)"><?=htmlspecialchars($user['department_name'] ?? 'Unassigned')?></span></td>
                                <td>
                                    <span class="badge" style="background: #f1f5f9;"><?=$user['active_projects']?> Projects</span>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="edit-user.php?id=<?=$user['id']?>" class="btn-sm btn-edit">EDIT</a>
                                        <button onclick="resetPw(<?=$user['id']?>, '<?=htmlspecialchars($user['full_name'])?>')" class="btn-sm btn-pw">PW RESET</button>
                                        <a href="delete-user.php?id=<?=$user['id']?>" class="btn-sm btn-delete" onclick="return confirm('Offboard this resource?')">DELETE</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table class="emp-table">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th>Employee Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td style="font-weight: 600;"><?=htmlspecialchars($dept['name'])?></td>
                                <td><?=$dept['employee_count']?> Members</td>
                                <td>
                                    <div class="action-links">
                                        <button onclick="editDept(<?=$dept['id']?>, '<?=htmlspecialchars($dept['name'])?>')" class="btn-sm btn-edit">RENAME</button>
                                        <a href="app/delete-department.php?id=<?=$dept['id']?>" class="btn-sm btn-delete" onclick="return confirm('Delete department?')">DELETE</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                </div>
            </div>
		</section>
	</div>

    <!-- Password Reset Modal -->
    <div id="pwModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0">Reset Password</h3>
            <p id="pwUser" style="font-size: 13px; color: #666;"></p>
            <form action="app/admin-reset-pw.php" method="POST">
                <input type="hidden" name="user_id" id="pwUserId">
                <input type="password" name="new_pw" placeholder="New Password" required style="width:100%; padding:10px; margin: 15px 0; border: 1px solid #ddd; border-radius:4px;">
                <div style="display:flex; gap:10px">
                    <button type="submit" class="btn-sm btn-pw" style="flex:1">UPDATE PASSWORD</button>
                    <button type="button" onclick="closeModal('pwModal')" class="btn-sm btn-edit">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dept Modal -->
    <div id="deptModal" class="modal">
        <div class="modal-content">
            <h3 id="deptTitle" style="margin-top:0">New Department</h3>
            <form action="app/manage-dept.php" method="POST">
                <input type="hidden" name="dept_id" id="deptId">
                <input type="text" name="dept_name" id="deptName" placeholder="Department Name" required style="width:100%; padding:10px; margin: 15px 0; border: 1px solid #ddd; border-radius:4px;">
                <div style="display:flex; gap:10px">
                    <button type="submit" class="btn-sm btn-pw" style="flex:1">SAVE DEPARTMENT</button>
                    <button type="button" onclick="closeModal('deptModal')" class="btn-sm btn-edit">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetPw(id, name) {
            document.getElementById('pwUserId').value = id;
            document.getElementById('pwUser').innerText = "For: " + name;
            document.getElementById('pwModal').style.display = 'flex';
        }
        function showDeptModal() {
            document.getElementById('deptId').value = '';
            document.getElementById('deptName').value = '';
            document.getElementById('deptTitle').innerText = 'New Department';
            document.getElementById('deptModal').style.display = 'flex';
        }
        function editDept(id, name) {
            document.getElementById('deptId').value = id;
            document.getElementById('deptName').value = name;
            document.getElementById('deptTitle').innerText = 'Rename Department';
            document.getElementById('deptModal').style.display = 'flex';
        }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>
<?php } else { header("Location: login.php?error=Access Denied"); exit(); } ?>