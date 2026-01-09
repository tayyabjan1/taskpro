<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "ensure_tables.php";
    include "app/Model/MeetingMinute.php";

    if (!isset($_GET['id'])) {
        header("Location: collaboration.php?tab=meetings");
        exit();
    }

    $id = $_GET['id'];
    $m = get_meeting_by_id($conn, $id);

    if (!$m) {
        header("Location: collaboration.php?tab=meetings");
        exit();
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Minutes: <?=htmlspecialchars($m['title'])?> | Hub</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
    <style>
        .view-card {
            background: #fff;
            padding: 50px;
            border-radius: var(--radius-3d);
            box-shadow: var(--shadow-3d);
            border: 1px solid rgba(255,255,255,0.4);
            max-width: 1000px;
            margin: 0 auto;
            border-top: 1px solid rgba(255,255,255,0.8);
            border-left: 1px solid rgba(255,255,255,0.8);
        }
        .v-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 30px;
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .v-title { font-size: 28px; font-weight: 800; color: var(--dark); margin-bottom: 12px; }
        .v-meta { display: flex; gap: 20px; font-size: 14px; color: #94a3b8; }
        .v-meta span { display: flex; align-items: center; gap: 8px; font-weight: 600; }

        .v-section { margin-bottom: 35px; }
        .v-label {
            font-size: 11px; font-weight: 800; color: var(--primary);
            text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 15px; display: block;
        }
        .v-text {
            font-size: 15px; color: #404e67; line-height: 1.8;
            white-space: pre-wrap; background: #f8fafc;
            padding: 25px; border-radius: 20px; border: 1px solid #e2e8f0;
            box-shadow: inset 1px 1px 3px rgba(0,0,0,0.02);
        }
        
        .feedback-box {
            background: #f0fdf4; border: 1px solid rgba(187, 247, 208, 0.4);
            padding: 30px; border-radius: var(--radius-3d); margin-top: 45px;
            box-shadow: var(--shadow-3d);
            border-top: 1px solid rgba(255,255,255,0.8);
        }
        .feedback-label { color: #166534; font-weight: 800; font-size: 13px; margin-bottom: 15px; display: block; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
            <div class="container-fluid">
                <div style="margin-bottom: 30px;">
                    <a href="collaboration.php?tab=meetings" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa fa-arrow-left"></i> BACK TO COLLABORATION HUB
                    </a>
                </div>

                <div class="view-card">
                    <div class="v-header">
                        <div>
                            <div class="v-title"><?=htmlspecialchars($m['title'])?></div>
                            <div class="v-meta">
                                <span><i class="fa fa-calendar"></i> <?=date('d M Y', strtotime($m['meeting_date']))?></span>
                                <span><i class="fa fa-map-marker"></i> <?=htmlspecialchars($m['location'] ?: 'Virtual')?></span>
                            </div>
                        </div>
                        <button onclick="window.print()" class="btn" style="background: #f1f5f9; color: #475569; padding: 10px 18px; border-radius: 10px; font-weight: 700; border:none; cursor:pointer; font-size: 12px;">
                            <i class="fa fa-print"></i> EXPORT
                        </button>
                    </div>

                    <div class="v-section">
                        <span class="v-label">Attendees</span>
                        <div style="font-size: 14px; color: #475569; font-weight: 600;">
                            <?=str_replace(',', ' • ', htmlspecialchars($m['attendees'] ?: 'None recorded'))?>
                        </div>
                    </div>

                    <div class="v-grid" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                        <div class="v-section">
                            <span class="v-label">Points of Discussion</span>
                            <div class="v-text"><?=htmlspecialchars($m['discussion'] ?: 'No discussion points recorded.')?></div>
                        </div>

                        <div class="v-section">
                            <span class="v-label">Decisions & Action Items</span>
                            <div class="v-text" style="background: #eff6ff; border-color: #dbeafe; color: #1e40af; font-weight: 500;"><?=htmlspecialchars($m['decisions'] ?: 'No specific decisions recorded.')?></div>
                        </div>
                    </div>

                    <div class="feedback-box">
                        <span class="feedback-label"><i class="fa fa-comments"></i> Meeting Feedback / Closing Remarks</span>
                        <?php if($m['feedback']) { ?>
                            <div style="color: #166534; font-size: 14px; line-height: 1.6;"><?=htmlspecialchars($m['feedback'])?></div>
                        <?php } else { ?>
                            <form action="app/update-meeting-feedback.php" method="POST">
                                <input type="hidden" name="id" value="<?=$m['id']?>">
                                <textarea name="feedback" class="form-control" style="width:100%; border-radius: 12px; padding:15px; border:1px solid #bbf7d0; font-size:14px; margin-bottom:15px;" placeholder="Add feedback, outcome or closing remarks..."></textarea>
                                <button type="submit" class="btn" style="background: #16a34a; color: #fff; padding: 10px 20px; border-radius: 10px; font-weight: 700; border:none; cursor:pointer; font-size: 12px;">SUBMIT FEEDBACK</button>
                            </form>
                        <?php } ?>
                    </div>

                    <!-- Multiple Comments Feature -->
                    <div style="margin-top: 40px; border-top: 1px solid #f1f5f9; padding-top: 30px;">
                        <span class="v-label" style="font-size: 14px; color: var(--dark);">Discussion History</span>
                        
                        <?php 
                        $comments = get_meeting_comments($conn, $m['id']);
                        if (!empty($comments)) {
                            foreach ($comments as $comment) {
                        ?>
                            <div style="background: #fff; border: 1px solid #f1f5f9; padding: 15px; border-radius: 16px; margin-bottom: 12px; box-shadow: var(--shadow-sm);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-weight: 800; font-size: 13px; color: var(--primary);">@<?=htmlspecialchars($comment['user_name'])?></span>
                                    <span style="font-size: 11px; color: #94a3b8;"><?=date('d M, H:i', strtotime($comment['created_at']))?></span>
                                </div>
                                <div style="font-size: 14px; color: #475569; line-height: 1.5;"><?=nl2br(htmlspecialchars($comment['comment']))?></div>
                            </div>
                        <?php 
                            }
                        } else {
                        ?>
                            <div style="text-align: center; padding: 30px; color: #94a3b8; font-size: 13px;">No comments yet. Start the discussion below.</div>
                        <?php } ?>

                        <div style="margin-top: 25px; background: #f8fafc; padding: 20px; border-radius: 20px; border: 1px dashed #e2e8f0;">
                            <form action="app/add-meeting-comment.php" method="POST">
                                <input type="hidden" name="meeting_id" value="<?=$m['id']?>">
                                <textarea name="comment" style="width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; font-size: 14px; min-height: 80px; resize: none; margin-bottom: 12px; outline: none;" placeholder="Add a comment or follow-up note..."></textarea>
                                <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa fa-paper-plane"></i> POST COMMENT
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
		</section>
	</div>
</body>
</html>
<?php } else { header("Location: login.php"); exit(); } ?>
