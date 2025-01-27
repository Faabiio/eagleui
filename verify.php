<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else {
    if (!empty($_SESSION['uuid'])) {
        header("Location: index.php");
        exit();
    } else {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $playername = $_POST['playername'];

            function formatUuid($uuid) {
                return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
            }

            // Fetch UUID from the API
            $api_url = "https://api.minetools.eu/uuid/$playername";
            $response = file_get_contents($api_url);
            $data = json_decode($response, true);

            if (isset($data['id'])) {
                $uuid = formatUuid($data['id']);

                // Insert a new entry
                $sql = "INSERT INTO auth (uuid, verified) VALUES ('$uuid', 0)";
                if ($conn->query($sql) === TRUE) {
                    $entry_id = $conn->insert_id;
                    header("Location: checkverification.php?entry_id=$entry_id");
                    exit();
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "Failed to fetch UUID for player: $playername<br>";
            }

            // Close connection
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Authentication</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/logo.ico" type="image/x-icon">
</head>
<body data-bs-theme="dark">
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="d-flex justify-content-center p-3">
            <img src="assets/img/logo.png" height="50" width="50" alt="">
        </div>
        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
            <form class="d-flex flex-column" method="post" action="">
                <p>Enter your Minecraft Username</p>
                <input class="form-control mb-3" type="text" name="playername" placeholder="Username" required>
                <button class="btn btn-success">SUBMIT</button>
            </form>
        </div>
    </div>
</body>
</html>