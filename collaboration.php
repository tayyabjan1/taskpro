<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Contact.php";
    include "app/Model/MeetingMinute.php";

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'contacts';
    
    $contacts = get_all_contacts($conn);
    $meetings = get_all_meetings($conn);
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Collaboration Hub | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        /* Shared Styles */
        /* Shared Styles */
        .collab-header {
            background: #fff;
            padding: 30px;
            border-radius: var(--radius-3d) var(--radius-3d) 0 0;
            border: 1px solid rgba(255,255,255,0.4);
            border-bottom: none;
            box-shadow: var(--shadow-3d);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .collab-tabs {
            background: #fff;
            padding: 0 30px;
            display: flex;
            gap: 30px;
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid #f1f5f9;
            border-radius: 0 0 var(--radius-3d) var(--radius-3d);
            margin-bottom: 30px;
            box-shadow: var(--shadow-3d);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .tab-btn {
            padding: 20px 0;
            font-size: 13px;
            font-weight: 800;
            color: #94a3b8;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        .tab-btn i { font-size: 16px; }

        /* Contact Styles */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .contact-card {
            background: #fff;
            padding: 24px;
            border-radius: 24px;
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            transition: var(--transition);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .contact-card:hover { transform: translateY(-5px); }
        .contact-avatar {
            width: 50px; height: 50px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800;
            margin-bottom: 15px;
        }

        /* Meeting Styles */
        .meeting-card {
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            transition: var(--transition);
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .meeting-card:hover { transform: translateX(5px); }
        .m-date {
            width: 60px; text-align: center;
            padding: 10px; border-radius: 12px;
            background: #f8fafc; border: 1px solid #e2e8f0;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.5); z-index: 2000;
            align-items: center; justify-content: center;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: #fff; padding: 30px; border-radius: var(--radius-3d);
            width: 500px; max-width: 90%;
            box-shadow: 20px 20px 60px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .form-control {
            width: 100%; padding: 12px; border-radius: 10px;
            border: 1px solid #e2e8f0; margin-bottom: 15px;
            font-size: 14px; outline: none;
        }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <!-- Header -->
                <div class="collab-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2 style="font-size: 28px; font-weight: 800; color: var(--dark); margin:0;">Team Collaboration</h2>
                            <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 14px;">Unified hub for network directory and operational discussions.</p>
                        </div>
                        <?php if ($tab == 'contacts'): ?>
                            <button onclick="showModal('contactModal')" class="btn" style="background: var(--primary); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; border:none; cursor:pointer;">
                                <i class="fa fa-user-plus"></i> Add Contact
                            </button>
                        <?php else: ?>
                            <a href="create-meeting.php" class="btn" style="background: var(--primary); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none;">
                                <i class="fa fa-plus"></i> New Meeting
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="collab-tabs">
                    <a href="collaboration.php?tab=contacts" class="tab-btn <?=$tab=='contacts'?'active':''?>">
                        <i class="fa fa-address-book-o"></i> Contact List
                    </a>
                    <a href="collaboration.php?tab=meetings" class="tab-btn <?=$tab=='meetings'?'active':''?>">
                        <i class="fa fa-comments-o"></i> Meeting Minutes
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600;">
                        <i class="fa fa-check-circle"></i> <?=$_GET['success']?>
                    </div>
                <?php endif; ?>

                <!-- Contacts View -->
                <?php if ($tab == 'contacts'): ?>
                    <div class="contact-grid">
                        <?php if ($contacts != 0): foreach($contacts as $c): ?>
                            <div class="contact-card">
                                <div class="contact-avatar"><?=strtoupper(substr($c['name'], 0, 1))?></div>
                                <div style="font-weight: 800; color: var(--dark); font-size: 17px;"><?=htmlspecialchars($c['name'])?></div>
                                <div style="font-size: 12px; color: var(--primary); font-weight: 700; margin-bottom: 15px; text-transform: uppercase;">
                                    <?=htmlspecialchars($c['designation'])?> @ <?=htmlspecialchars($c['company'])?>
                                </div>
                                <div style="font-size: 13px; color: #64748b;">
                                    <div style="margin-bottom: 5px;"><i class="fa fa-envelope-o"></i> <?=htmlspecialchars($c['email'])?></div>
                                    <div><i class="fa fa-phone"></i> <?=htmlspecialchars($c['phone'])?></div>
                                </div>
                                <div style="margin-top: 15px; text-align: right; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                                    <a href="app/delete-contact.php?id=<?=$c['id']?>&redirect=collab" onclick="return confirm('Delete?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 700;">DELETE</a>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">Directory is empty.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Meetings View -->
                <?php if ($tab == 'meetings'): ?>
                    <div class="meeting-list">
                        <?php if ($meetings != 0): foreach($meetings as $m): $d = strtotime($m['meeting_date']); ?>
                            <div class="meeting-card">
                                <div class="m-date">
                                    <div style="font-size: 18px; font-weight: 800;"><?=date('d', $d)?></div>
                                    <div style="font-size: 10px; font-weight: 700; color: var(--primary);"><?=date('M', $d)?></div>
                                </div>
                                <div style="flex: 1; padding: 0 20px;">
                                    <div style="font-weight: 800; color: var(--dark);"><?=htmlspecialchars($m['title'])?></div>
                                    <div style="font-size: 12px; color: #64748b;">
                                        <i class="fa fa-map-marker"></i> <?=htmlspecialchars($m['location'] ?: 'Virtual')?>
                                    </div>
                                </div>
                                <a href="meeting-view.php?id=<?=$m['id']?>&redirect=collab" style="font-size: 11px; font-weight: 800; color: var(--primary); text-decoration: none;">VIEW MINUTES</a>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="text-align: center; padding: 50px;">No meeting records found.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
		</section>
	</div>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0;">New Contact</h3>
            <form action="app/add-contact.php?redirect=collab" method="POST">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                <input type="email" name="email" class="form-control" placeholder="Email">
                <input type="text" name="phone" class="form-control" placeholder="Phone">
                <input type="text" name="designation" class="form-control" placeholder="Designation">
                <input type="text" name="company" class="form-control" placeholder="Company">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex:1; background: var(--primary); color: #fff; padding: 12px; border-radius: 12px; border:none; font-weight: 800;">SAVE</button>
                    <button type="button" onclick="hideModal('contactModal')" class="btn" style="flex:1; background: #f1f5f9; padding: 12px; border-radius: 12px; border:none;">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).style.display = 'flex'; }
        function hideModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>
<?php } else { header("Location: login.php"); exit(); } ?>
