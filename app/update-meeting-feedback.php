<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/MeetingMinute.php";

    if (isset($_POST['feedback']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $feedback = $_POST['feedback'];

        $res = update_meeting_feedback($conn, $id, $feedback);

        if ($res) {
            header("Location: ../meeting-view.php?id=$id&success=Feedback submitted successfully");
            exit();
        } else {
            header("Location: ../meeting-view.php?id=$id&error=Unknown error occurred");
            exit();
        }
    } else {
        header("Location: ../meetings.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
