<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else {
    if (!empty($_SESSION['uuid'])) {
        header("Location: index.php");
        exit();
    }
}
if (!isset($_GET['entry_id'])) {
    echo "No entry ID provided.";
    exit();
}

$entry_id = intval($_GET['entry_id']);
$user_id = $_SESSION['user_id'];

include 'db_config.php';

// Check if the entry_id is valid, has a verified status of 1
$sql = "SELECT uuid, verified FROM auth WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $entry_id);
$stmt->execute();
$stmt->bind_result($entry_uuid, $verified);
$stmt->fetch();
$stmt->close();

if ($verified != 1) {
    echo "Verification failed.";
    exit();
}

// Set the user's UUID to the one provided in the auth table
$sql = "UPDATE users SET uuid = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $entry_uuid, $user_id);

if ($stmt->execute()) {
    // Delete the row from the auth table
    $sql = "DELETE FROM auth WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $entry_id);

    if ($stmt->execute()) {
        header("Location: logout.php");
        exit();
    } else {
        echo "Error deleting the row: " . $stmt->error;
    }
} else {
    echo "Error updating the user's UUID: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>