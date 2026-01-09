<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
	if ($_SESSION['role'] != 'admin') {
		header("Location: ../index.php");
		exit();
	}

	include "../DB_connection.php";
	include "Model/Project.php";

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
		$id = $_POST['id'];
		$name = trim($_POST['name']);
		$description = trim($_POST['description']);
		$assignees = !empty($_POST['assignees']) ? $_POST['assignees'] : [];
		$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
		$deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
		$status = $_POST['status'];

		if (empty($name)) {
			$em = "Project name is required";
			header("Location: ../edit-project.php?id=$id&error=$em");
			exit();
		}

        if (empty($assignees)) {
			$em = "At least one employee must be assigned";
			header("Location: ../edit-project.php?id=$id&error=$em");
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

		if (update_project($conn, $id, $data)) {
			$sm = "Project updated successfully";
			header("Location: ../projects.php?success=$sm");
			exit();
		} else {
			$em = "Failed to update project";
			header("Location: ../edit-project.php?id=$id&error=$em");
			exit();
		}
	} else {
		header("Location: ../projects.php");
		exit();
	}
} else {
	header("Location: ../login.php");
	exit();
}
?>
