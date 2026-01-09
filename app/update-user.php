<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['full_name']) && $_SESSION['role'] == 'admin') {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$user_name = validate_input($_POST['user_name']);
	$full_name = validate_input($_POST['full_name']);
	$id = validate_input($_POST['id']);


	if (empty($user_name)) {
		$em = "Username is required";
	    header("Location: ../edit-user.php?error=$em&id=$id");
	    exit();
	}else if (empty($full_name)) {
		$em = "Full name is required";
	    header("Location: ../edit-user.php?error=$em&id=$id");
	    exit();
	}else {
       include "Model/User.php";
       $dept_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;

       $data = [
           'full_name' => $full_name,
           'username' => $user_name,
           'role' => "employee",
           'department_id' => $dept_id
       ];
       update_user($conn, $id, $data);

       $em = "User updated successfully";
	    header("Location: ../user.php?success=$em");
	    exit();
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../user.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../user.php?error=$em");
   exit();
}