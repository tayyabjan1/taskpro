<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {

    if (isset($_POST['title']) && isset($_POST['assigned_to'])) {
        include "../DB_connection.php";
        include "Model/Task.php";
        include "Model/Notification.php";

        $title = trim($_POST['title']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : "";
        $assigned_to = $_POST['assigned_to'];
        $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d');

        if (empty($title)) {
            header("Location: ../tasks.php?error=Title is required&date=" . $due_date);
            exit();
        }

        // Check if a task with the same title, assignee, and date already exists
        $sql_check = "SELECT id FROM tasks WHERE title = ? AND assigned_to = ? AND due_date = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$title, $assigned_to, $due_date]);
        $existing_task = $stmt_check->fetch();

        if ($existing_task) {
            // "Update Only" logic - Update description and reset status to pending (re-trigger)
            $sql_upd = "UPDATE tasks SET description = ?, status = 'pending' WHERE id = ?";
            $stmt_upd = $conn->prepare($sql_upd);
            $stmt_upd->execute([$description, $existing_task['id']]);
            $msg = "Task record updated for this date!";
        } else {
            // New Task
            $data = array($title, $description, $assigned_to, $due_date);
            insert_task($conn, $data);
            
            // Notification only for new tasks
            $notif_data = array("New task assigned: $title for " . date('d M', strtotime($due_date)), $assigned_to, 'Execution Board');
            insert_notification($conn, $notif_data);
            $msg = "New task added to board!";
        }

        // Redirect DIRECT to the day record
        header("Location: ../tasks.php?success=$msg&date=" . $due_date);
        exit();
    } else {
        header("Location: ../tasks.php");
        exit();
    }

} else {
    header("Location: ../login.php?error=Access Denied");
    exit();
}
?>
