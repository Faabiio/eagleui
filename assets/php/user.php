<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else {
    if (empty($_SESSION['uuid'])) {
        header("Location: verify.php");
        exit();
    } 
}

$user_id = $_SESSION['user_id'];
$user_uuid = $_SESSION['uuid'];



?>