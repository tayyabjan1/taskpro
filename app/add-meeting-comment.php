<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    if (isset($_POST['meeting_id']) && isset($_POST['comment'])) {
        include "../DB_connection.php";
        include "Model/MeetingMinute.php";

        $meeting_id = $_POST['meeting_id'];
        $user_id = $_SESSION['id'];
        $comment = trim($_POST['comment']);

        if (!empty($comment)) {
            add_meeting_comment($conn, $meeting_id, $user_id, $comment);
            header("Location: ../meeting-view.php?id=$meeting_id&success=Comment added");
        } else {
            header("Location: ../meeting-view.php?id=$meeting_id&error=Comment cannot be empty");
        }
    } else {
        header("Location: ../collaboration.php");
    }
} else {
    header("Location: ../login.php");
}
?>
