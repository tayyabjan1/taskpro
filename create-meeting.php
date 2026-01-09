<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Record Minutes | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .form-card {
            background: #fff;
            padding: 35px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 11px; font-weight: 800;
            color: #475569; margin-bottom: 8px; text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%; padding: 12px 16px; border-radius: 12px;
            border: 1px solid #e2e8f0; font-size: 14px; outline: none;
            transition: 0.2s; background: #f8fafc;
        }
        .form-control:focus { background: #fff; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
        textarea.form-control { resize: none; min-height: 120px; }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <div style="margin-bottom: 30px; text-align: center;">
                    <a href="collaboration.php?tab=meetings" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                        <i class="fa fa-arrow-left"></i> BACK TO COLLABORATION HUB
                    </a>
                    <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">Record Meeting Minutes</h2>
                </div>

                <div class="form-card">
                    <form action="app/add-meeting.php" method="POST">
                        <div class="form-group">
                            <label>Meeting Title / Objective</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Q3 Strategic Alignment" required>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Meeting Date</label>
                                <input type="date" name="meeting_date" class="form-control" value="<?=date('Y-m-d')?>" required>
                            </div>
                            <div class="form-group">
                                <label>Location / Mode</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Conference Room A / Zoom">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Attendees (Comma separated)</label>
                            <input type="text" name="attendees" class="form-control" placeholder="e.g. John Doe, Sarah Smith, Alex Pierce">
                        </div>

                        <div class="form-group">
                            <label>Points of Discussion</label>
                            <textarea name="discussion" class="form-control" placeholder="Bullet points of what was discussed..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Key Decisions & Action Items</label>
                            <textarea name="decisions" class="form-control" placeholder="Final decisions and who is responsible for what..."></textarea>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn" style="width: 100%; background: var(--primary); color: #fff; padding: 15px; border-radius: 14px; font-weight: 800; border:none; cursor:pointer; font-size: 15px; box-shadow: 0 4px 14px rgba(18, 123, 142, 0.4);">
                                <i class="fa fa-check-circle"></i> SAVE MINUTES & LOG RECORD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
		</section>
	</div>
</body>
</html>
<?php } else { header("Location: login.php"); exit(); } ?>
