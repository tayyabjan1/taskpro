<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    include "app/Model/Department.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: user.php");
    	 exit();
    }
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);
    $departments = get_all_departments($conn);

    if ($user == 0) {
    	 header("Location: user.php");
    	 exit();
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Resource | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .form-card {
            background: #fff;
            padding: 40px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            max-width: 600px;
            margin: 0 auto;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .v-label {
            font-size: 11px; font-weight: 800; color: var(--primary);
            text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 12px; display: block;
        }
        .form-control {
            width: 100%; padding: 14px 18px; border-radius: 12px;
            border: 1px solid #e2e8f0; font-size: 14px; outline: none;
            background: #f8fafc; transition: 0.3s;
            margin-bottom: 25px;
        }
        .form-control:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1); }
        
        .submit-btn {
            width: 100%; background: var(--primary); color: #fff;
            padding: 16px; border-radius: 16px; font-weight: 800;
            border: none; cursor: pointer; font-size: 15px;
            box-shadow: 0 10px 20px rgba(18, 123, 142, 0.3);
            transition: 0.3s;
        }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(18, 123, 142, 0.4); }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <div style="margin-bottom: 30px; text-align: center;">
                    <a href="user.php" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                        <i class="fa fa-arrow-left"></i> BACK TO RESOURCE HUB
                    </a>
                    <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">Modify Resource Intel</h2>
                    <p style="color: var(--text-muted); margin-top: 8px;">Update details for account: <strong>@<?=htmlspecialchars($user['username'])?></strong></p>
                </div>

                <div class="form-card">
                    <form action="app/update-user.php" method="POST">
                        <input type="hidden" name="id" value="<?=$user['id']?>">
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 13px; font-weight: 600;">
                                <i class="fa fa-exclamation-circle"></i> <?=$_GET['error']?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success'])): ?>
                            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 13px; font-weight: 600;">
                                <i class="fa fa-check-circle"></i> <?=$_GET['success']?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <span class="v-label">Full Name</span>
                            <input type="text" name="full_name" class="form-control" value="<?=htmlspecialchars($user['full_name'])?>" required>
                        </div>

                        <div class="form-group">
                            <span class="v-label">Assignment / Department</span>
                            <select name="department_id" class="form-control" required>
                                <option value="">Select Deployment Sector...</option>
                                <?php foreach($departments as $d): ?>
                                    <option value="<?=$d['id']?>" <?=($user['department_id'] == $d['id'] ? 'selected' : '')?>><?=$d['name']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <span class="v-label">Operational Username</span>
                            <input type="text" name="user_name" class="form-control" value="<?=htmlspecialchars($user['username'])?>" required>
                        </div>

                        <button type="submit" class="submit-btn" style="background: var(--dark);">
                            <i class="fa fa-save"></i> UPDATE INTELLIGENCE RECORD
                        </button>
                    </form>
                </div>
            </div>
		</section>
	</div>
</body>
</html>
<?php }else{ 
   header("Location: login.php?error=First login");
   exit();
} ?>
