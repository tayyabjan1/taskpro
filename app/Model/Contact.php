<?php 

function get_all_contacts($conn) {
    $sql = "SELECT * FROM contacts ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $contacts = $stmt->fetchAll();
        return $contacts;
    } else {
        return 0;
    }
}

function add_contact($conn, $data) {
    $sql = "INSERT INTO contacts (name, email, phone, designation, company) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount() > 0;
}

function delete_contact($conn, $id) {
    $sql = "DELETE FROM contacts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
