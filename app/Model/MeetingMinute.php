<?php 

function get_all_meetings($conn) {
    $sql = "SELECT m.*, u.full_name as creator_name 
            FROM meeting_minutes m 
            JOIN users u ON m.created_by = u.id 
            ORDER BY meeting_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $meetings = $stmt->fetchAll();
        return $meetings;
    } else {
        return 0;
    }
}

function get_meeting_by_id($conn, $id) {
    $sql = "SELECT m.*, u.full_name as creator_name 
            FROM meeting_minutes m 
            JOIN users u ON m.created_by = u.id 
            WHERE m.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $meeting = $stmt->fetch();
        return $meeting;
    } else {
        return 0;
    }
}

function add_meeting($conn, $data) {
    $sql = "INSERT INTO meeting_minutes (title, meeting_date, location, attendees, discussion, decisions, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount() > 0;
}

function update_meeting_feedback($conn, $id, $feedback) {
    $sql = "UPDATE meeting_minutes SET feedback = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$feedback, $id]);
    return $stmt->rowCount() > 0;
}

function get_meeting_comments($conn, $meeting_id) {
    $sql = "SELECT c.*, u.full_name as user_name 
            FROM meeting_comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.meeting_id = ? 
            ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$meeting_id]);
    return $stmt->fetchAll();
}

function add_meeting_comment($conn, $meeting_id, $user_id, $comment) {
    $sql = "INSERT INTO meeting_comments (meeting_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$meeting_id, $user_id, $comment]);
    return $conn->lastInsertId();
}
