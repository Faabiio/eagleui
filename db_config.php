<?php
//
$SERVERNAME = "MyServer.com";

// DATABASE CONFIGURATION
$server = "localhost";
$username = "root";
$password = "";
$dbname = "bansys";

// Create connection
$conn = new mysqli($server, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>