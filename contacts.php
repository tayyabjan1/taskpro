<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Contact.php";

    $contacts = get_all_contacts($conn);
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Contact List | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .contact-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .contact-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--primary); }
        .contact-avatar {
            width: 60px; height: 60px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 800;
            margin-bottom: 15px;
        }
        .contact-name { font-size: 18px; font-weight: 800; color: var(--dark); margin-bottom: 5px; }
        .contact-info { font-size: 13px; color: #64748b; margin-bottom: 15px; }
        .contact-info div { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .contact-info i { width: 14px; text-align: center; color: var(--primary); }
        
        .add-contact-modal {
            display: none;
            position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.5); z-index: 2000;
            align-items: center; justify-content: center;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: #fff; padding: 30px; border-radius: 24px;
            width: 450px; max-width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        .form-control {
            width: 100%; padding: 10px 14px; border-radius: 10px;
            border: 1px solid #e2e8f0; font-size: 14px; outline: none;
            transition: 0.2s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">Enterprise Contacts</h2>
                        <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 14px;">Centralized directory for all business stakeholders and partners.</p>
                    </div>
                    <button onclick="showModal()" class="btn" style="background: var(--primary); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(18, 123, 142, 0.4); border:none; cursor:pointer;">
                        <i class="fa fa-user-plus"></i> Add New Contact
                    </button>
                </div>

                <?php if (isset($_GET['success'])) { ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; border: 1px solid #bbf7d0;">
                        <i class="fa fa-check-circle"></i> <?=$_GET['success']?>
                    </div>
                <?php } ?>

                <div class="contact-grid">
                    <?php if ($contacts != 0) { 
                        foreach($contacts as $c) { ?>
                        <div class="contact-card">
                            <div class="contact-avatar">
                                <?=strtoupper(substr($c['name'], 0, 1))?>
                            </div>
                            <div class="contact-name"><?=htmlspecialchars($c['name'])?></div>
                            <div style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 15px;">
                                <?=htmlspecialchars($c['designation'] ?: 'Stakeholder')?> @ <?=htmlspecialchars($c['company'] ?: 'Enterprise')?>
                            </div>
                            <div class="contact-info">
                                <div><i class="fa fa-envelope-o"></i> <?=htmlspecialchars($c['email'] ?: 'N/A')?></div>
                                <div><i class="fa fa-phone"></i> <?=htmlspecialchars($c['phone'] ?: 'N/A')?></div>
                            </div>
                            <div style="display:flex; justify-content: flex-end; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                                <a href="app/delete-contact.php?id=<?=$c['id']?>" onclick="return confirm('Delete this contact?')" style="color: #ef4444; font-size: 13px; text-decoration: none; font-weight: 700;">
                                    <i class="fa fa-trash"></i> DELETE
                                </a>
                            </div>
                        </div>
                    <?php } } else { ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                            <div style="font-size: 60px; color: #e2e8f0; margin-bottom: 20px;"><i class="fa fa-address-book-o"></i></div>
                            <h3 style="color: var(--dark);">Directory is Empty</h3>
                            <p style="color: #94a3b8;">Start building your enterprise network by adding your first contact.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
		</section>
	</div>

    <!-- Add Contact Modal -->
    <div id="contactModal" class="add-contact-modal">
        <div class="modal-content">
            <h3 style="margin-top:0; font-weight: 800; color: var(--dark); font-size: 20px;">New Stakeholder</h3>
            <form action="app/add-contact.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Alexander Pierce" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. alexander@company.com">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. +1 234 567 890">
                </div>
                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" name="designation" class="form-control" placeholder="e.g. Chief Operations Officer">
                </div>
                <div class="form-group">
                    <label>Company / Organization</label>
                    <input type="text" name="company" class="form-control" placeholder="e.g. Pierce Enterprises">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn" style="flex:1; background: var(--primary); color: #fff; padding: 12px; border-radius: 12px; font-weight: 700; border:none; cursor:pointer;">SAVE CONTACT</button>
                    <button type="button" onclick="hideModal()" class="btn" style="flex:1; background: #f1f5f9; color: #475569; padding: 12px; border-radius: 12px; font-weight: 700; border:none; cursor:pointer;">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal() { document.getElementById('contactModal').style.display = 'flex'; }
        function hideModal() { document.getElementById('contactModal').style.display = 'none'; }
    </script>
</body>
</html>
<?php } else { header("Location: login.php"); exit(); } ?>
