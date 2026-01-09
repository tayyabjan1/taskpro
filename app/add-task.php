<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['assigned_to']) && $_SESSION['role'] == 'admin' && isset($_POST['due_date'])) {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$title = validate_input($_POST['title']);
	$description = validate_input($_POST['description']);
	$assigned_to = validate_input($_POST['assigned_to']);
	$due_date = validate_input($_POST['due_date']);
    
    // Fix: Convert '0' to null for database compatibility with foreign key constraints
    if ($assigned_to == 0) $assigned_to = null;
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'create_task.php';

	if (empty($title)) {
		$em = "Title is required";
	    header("Location: ../$redirect?error=$em");
	    exit();
	}else if (empty($description)) {
		$em = "Description is required";
	    header("Location: ../$redirect?error=$em");
	    exit();
	}else {
    
       include "Model/Task.php";
       include "Model/Notification.php";

       $data = array($title, $description, $assigned_to, $due_date);
       insert_task($conn, $data);

       if ($assigned_to != 0) {
           $notif_data = array("'$title' has been assigned to you. Please review and start working on it", $assigned_to, 'New Task Assigned');
           insert_notification($conn, $notif_data);
       }


       $em = "Task created successfully";
	    header("Location: ../$redirect?success=$em");
	    exit();

    
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../tasks.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../login.php?error=$em");
   exit();
}