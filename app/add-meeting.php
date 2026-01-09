<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/MeetingMinute.php";

    if (isset($_POST['title'])) {
        $title = $_POST['title'];
        $meeting_date = $_POST['meeting_date'];
        $location = $_POST['location'];
        $attendees = $_POST['attendees'];
        $discussion = $_POST['discussion'];
        $decisions = $_POST['decisions'];
        $created_by = $_SESSION['id'];

        $data = [$title, $meeting_date, $location, $attendees, $discussion, $decisions, $created_by];
        $res = add_meeting($conn, $data);

        if ($res) {
            header("Location: ../collaboration.php?tab=meetings&success=Meeting minutes recorded successfully");
            exit();
        } else {
            header("Location: ../collaboration.php?tab=meetings&error=Unknown error occurred");
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
