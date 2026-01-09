<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && isset($_GET['id'])) {
	if ($_SESSION['role'] != 'admin') {
		header("Location: index.php");
		exit();
	}

	include "DB_connection.php";
	include "app/Model/Project.php";

	$id = $_GET['id'];

	if (delete_project($conn, $id)) {
		$sm = "Project deleted successfully";
		header("Location: projects.php?success=$sm");
		exit();
	} else {
		$em = "Failed to delete project";
		header("Location: projects.php?error=$em");
		exit();
	}
} else {
	header("Location: projects.php");
	exit();
}
?>
