<?php
include '../../db_config.php';

header('Content-Type: application/json');

if (isset($_GET['entry_id'])) {
    $entry_id = intval($_GET['entry_id']);
    $sql = "SELECT uuid, verified FROM auth WHERE id = $entry_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['uuid' => $row['uuid'], 'verified' => $row['verified']]);
    } else {
        echo json_encode(['verified' => false, 'error' => 'No entry found with the provided ID']);
    }
} else {
    echo json_encode(['error' => 'No entry ID provided']);
}

// Close connection
$conn->close();
?>