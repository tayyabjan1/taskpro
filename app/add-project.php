<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
	if ($_SESSION['role'] != 'admin') {
		header("Location: ../index.php");
		exit();
	}

	include "../DB_connection.php";
	include "Model/Project.php";

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$name = trim($_POST['name']);
		$description = trim($_POST['description']);
		$assignees = !empty($_POST['assignees']) ? $_POST['assignees'] : [];
		$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
		$deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
		$status = $_POST['status'];

		if (empty($name)) {
			$em = "Project name is required";
			header("Location: ../create_project.php?error=$em");
			exit();
		}

        if (empty($assignees)) {
			$em = "At least one employee must be assigned";
			header("Location: ../create_project.php?error=$em");
			exit();
		}

		$data = [
			'name' => $name,
			'description' => $description,
			'assignees' => $assignees,
			'start_date' => $start_date,
			'deadline' => $deadline,
			'status' => $status
		];

		if (add_project($conn, $data)) {
			$sm = "Project created successfully";
			header("Location: ../projects.php?success=$sm");
			exit();
		} else {
			$em = "Failed to create project";
			header("Location: ../create_project.php?error=$em");
			exit();
		}
	} else {
		header("Location: ../create_project.php");
		exit();
	}
} else {
	header("Location: ../login.php");
	exit();
}
?>
