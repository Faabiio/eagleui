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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Verification Status</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/logo.ico" type="image/x-icon">
    <script>
        function checkVerification(entryId) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "assets/php/auth.php?entry_id=" + entryId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.verified == 1) {
                        document.getElementById("verificationStatus").innerHTML = "Entry has been verified!";
                        window.location.href = "updateverification.php?entry_id=" + entryId;
                    } else {
                        document.getElementById("verificationStatus").innerHTML = "Entry has not been verified yet.";
                        setTimeout(function() { checkVerification(entryId); }, 5000); // Check again after 5 seconds
                    }
                    if (response.uuid) {
                        document.getElementById("playerAvatar").src = "https://mc-heads.net/avatar/" + response.uuid + "/100";
                    }
                }
            };
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", function() {
            var entryId = <?php echo $entry_id; ?>;
            checkVerification(entryId);
        });
    </script>
</head>
    <body data-bs-theme="dark">
        <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
            <div class="d-flex justify-content-center p-3">
                <img src="assets/img/logo.png" height="50" width="50" alt="">
            </div>
            <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
                <img id="playerAvatar" src="" alt="Player Avatar" class="mt-3">
                <p class="mt-5">Please connect to the Server. If you aren't banned do: /eagle verify</p>
                <div class= "mb-5" id="verificationStatus">Checking verification status...</div>
                <div class="loader"></div>
                <!--<button class="btn btn-primary mt-3" onclick="checkVerification(<?php echo $entry_id; ?>)">Recheck Verification Status</button>-->
            </div>
        </div>
    </body>
</html>

