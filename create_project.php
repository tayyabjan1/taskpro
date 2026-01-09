<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {
	if ($_SESSION['role'] != 'admin') {
		header("Location: index.php");
		exit();
	}

	include "DB_connection.php";
	include "app/Model/User.php";

	$users = get_all_users($conn);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Launch Strategy | Task Pro</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: visible; /* Allow dropdown to overflow */
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
        }
        .form-header p {
            color: #94a3b8;
            font-size: 14px;
        }

        .input-group { margin-bottom: 25px; position: relative; }
        .input-group label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--primary);
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .modern-input {
            width: 100%;
            padding: 14px 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            color: var(--dark);
            outline: none;
            transition: 0.3s;
            font-weight: 500;
        }
        .modern-input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1);
        }

        /* Custom Multi-Select Styles */
        .custom-select-wrapper { position: relative; user-select: none; }
        .custom-select-trigger {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px 18px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            min-height: 50px;
        }
        .custom-select-trigger:hover { border-color: #cbd5e1; }
        .custom-select-trigger.active { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(18, 123, 142, 0.1); }
        
        .selection-render {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 13px;
            color: var(--dark);
        }
        .placeholder-text { color: #94a3b8; }
        
        .selected-tag {
            background: rgba(18, 123, 142, 0.1);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .custom-options {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 250px;
            overflow-y: auto;
            padding: 8px;
        }
        .custom-options.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .option-item {
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-main);
            font-size: 14px;
        }
        .option-item:hover { background: #f1f5f9; }
        .option-item.selected { background: #f0fdf4; color: #166534; }
        .option-item.selected .fa-check { opacity: 1; }
        .option-item .fa-check { opacity: 0; color: #166534; margin-left: auto; transition: 0.2s; }

        .btn-submit {
            background: var(--dark);
            color: #fff;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.25); }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 600px) { .form-grid-2 { grid-template-columns: 1fr; } }
	</style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                
                <div style="margin-bottom: 20px;">
                    <a href="projects.php" style="text-decoration: none; color: #94a3b8; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-arrow-left"></i> BACK TO HUB
                    </a>
                </div>

                <div class="form-container">
                    <div class="form-header">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), #0e6070); color: #fff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px auto; box-shadow: 0 10px 20px rgba(18, 123, 142, 0.3);">
                            <i class="fa fa-rocket"></i>
                        </div>
                        <h2>Create Operations Strategy</h2>
                        <p>Initialize a new strategic initiative and assign leadership.</p>
                    </div>

                    <?php if (isset($_GET['error'])) { ?>
						<div style="background: #fef2f2; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #fecaca; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
							<i class="fa fa-exclamation-circle"></i> <?php echo $_GET['error']; ?>
						</div>
					<?php } ?>

                    <form action="app/add-project.php" method="post" id="createProjectForm">
                        <div class="input-group">
                            <label>Strategy Title</label>
                            <input type="text" name="name" class="modern-input" placeholder="e.g. Q4 Global Expansion" required autofocus>
                        </div>

                        <div class="input-group">
                            <label>Context & Scope</label>
                            <textarea name="description" class="modern-input" placeholder="Define the core objectives and scope..." style="height: 100px; resize: none;"></textarea>
                        </div>

                        <div class="form-grid-2">
                             <div class="input-group">
                                <label>Target Timeline</label>
                                <input type="date" name="deadline" class="modern-input" required>
                            </div>
                            <div class="input-group">
                                <label>Initial Status</label>
                                <select name="status" class="modern-input">
                                    <option value="planning">📋 Planning</option>
                                    <option value="in_progress">🚀 Execution</option>
                                </select>
                            </div>
                        </div>

                        <!-- Custom Dropdown -->
                        <div class="input-group">
                            <label>Assign Team Leadership</label>
                            <div class="custom-select-wrapper">
                                <div class="custom-select-trigger" onclick="toggleDropdown()">
                                    <div class="selection-render" id="displayContainer">
                                        <span class="placeholder-text">Select Personnel...</span>
                                    </div>
                                    <i class="fa fa-chevron-down" style="font-size: 12px; color: #94a3b8;"></i>
                                </div>
                                <div class="custom-options" id="optionsContainer">
                                    <?php foreach ($users as $user) { ?>
                                        <div class="option-item" onclick="toggleSelection(this, '<?=$user['id']?>', '<?=htmlspecialchars($user['full_name'])?>')">
                                            <div style="width: 24px; height: 24px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #64748b;">
                                                <?=strtoupper(substr($user['full_name'], 0, 1))?>
                                            </div>
                                            <span><?=htmlspecialchars($user['full_name'])?></span>
                                            <span style="font-size: 10px; color: #94a3b8; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?=ucfirst($user['role'])?></span>
                                            <i class="fa fa-check"></i>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Hidden Select for Form Submission -->
                            <select name="assignees[]" id="realSelect" multiple style="display: none;"></select>
                        </div>

                        <button type="submit" class="btn-submit">
                            INITIALIZE PROJECT <i class="fa fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
		</section>
	</div>

    <script>
        var active = document.querySelector("#navList li:nth-child(2)");
        if(active) active.classList.add("active");

        /* Custom Dropdown Logic */
        const trigger = document.querySelector('.custom-select-trigger');
        const options = document.querySelector('.custom-options');
        const display = document.getElementById('displayContainer');
        const realSelect = document.getElementById('realSelect');
        let selectedValues = [];

        function toggleDropdown() {
            options.classList.toggle('open');
            trigger.classList.toggle('active');
        }

        function toggleSelection(element, id, name) {
            // Toggle Visuals
            element.classList.toggle('selected');
            
            if (selectedValues.includes(id)) {
                selectedValues = selectedValues.filter(v => v !== id);
            } else {
                selectedValues.push(id);
            }
            
            renderSelection();
            updateRealSelect();
        }

        function renderSelection() {
            // Get all selected item elements to retrieve names easily, or iterate data
            // Simpler: iterate the DOM elements to check which are selected
            const selectedEls = document.querySelectorAll('.option-item.selected');
            display.innerHTML = '';
            
            if (selectedEls.length === 0) {
                display.innerHTML = '<span class="placeholder-text">Select Personnel...</span>';
                return;
            }

            selectedEls.forEach(el => {
                const name = el.querySelector('span').innerText;
                const tag = document.createElement('div');
                tag.className = 'selected-tag';
                tag.innerHTML = `${name}`;
                display.appendChild(tag);
            });
        }

        function updateRealSelect() {
            realSelect.innerHTML = '';
            selectedValues.forEach(val => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.selected = true;
                realSelect.appendChild(opt);
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !options.contains(e.target)) {
                options.classList.remove('open');
                trigger.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php }else{ 
	$em = "First login";
	header("Location: login.php?error=$em");
	exit();
}
?>
