<?php
include 'core.php';

if(isTeamMember()){
    header("Location: staff.php");
    exit();
}

$user_uuid = $_SESSION['uuid']; // Assuming user UUID is stored in session

/**
 * Creates an appeal for a ban or mute and adds the reason to the appeal_messages table.
 *
 * @param int $type_id The ID of the ban or mute.
 * @param string $uuid The UUID of the player.
 * @param string $reason The reason for the appeal.
 * @param string $type The type of appeal ('ban' or 'mute').
 * @param string $staff_uuid The UUID of the staff member who issued the ban or mute.
 * @return bool True if the appeal was created successfully, false otherwise.
 */
function createAppeal($type_id, $uuid, $reason, $type, $staff_uuid) {
    global $conn;
    $sql = "INSERT INTO appeals (type_id, uuid, type, state, staff_uuid, created_at) VALUES (?, ?, ?, 0, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("isss", $type_id, $uuid, $type, $staff_uuid);
    $result = $stmt->execute();
    $appeal_id = $stmt->insert_id;
    $stmt->close();

    if ($result) {
        $sql = "INSERT INTO appeals_messages (appeal_id, author, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("iss", $appeal_id, $uuid, $reason);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: appeal.php?id=" . $appeal_id);
    return $result;

}

// Check if the user is banned
$ban_info = isUuidBanned($user_uuid);
$is_banned = $ban_info['is_banned'];
$ban_id = $is_banned ? $ban_info['ban_id'] : null;
$ban_staff_uuid = $is_banned ? $ban_info['staff_uuid'] : null;

// Check if the user is muted
$mute_info = isUuidMuted($user_uuid);
$is_muted = $mute_info['is_muted'];
$mute_id = $is_muted ? $mute_info['mute_id'] : null;
$mute_staff_uuid = $is_muted ? $mute_info['staff_uuid'] : null;

// Check if the user has an open or closed appeal for ban
$ban_appeal_info = hasOrCanAppeal($ban_id, $user_uuid, 'ban');
$ban_appeal_status = $ban_appeal_info['status'];
$ban_appeal_id = $ban_appeal_info['appeal_id'];

// Check if the user has an open or closed appeal for mute
$mute_appeal_info = hasOrCanAppeal($mute_id, $user_uuid, 'mute');
$mute_appeal_status = $mute_appeal_info['status'];
$mute_appeal_id = $mute_appeal_info['appeal_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $type_id = $_POST['type_id'];
    $reason = $_POST['reason'];

    if ($type == 'ban' && $is_banned && $ban_appeal_status == 0) {
        createAppeal($type_id, $user_uuid, $reason, 'ban', $ban_staff_uuid);
        createAlert('success', 'Ban appeal submitted successfully.');
    } elseif ($type == 'mute' && $is_muted && $mute_appeal_status == 0 && !$is_banned) {
        createAppeal($type_id, $user_uuid, $reason, 'mute', $mute_staff_uuid);
        echo "<div class='alert alert-success' role='alert'>Mute appeal submitted successfully.</div>";
        createAlert('success', 'Mute appeal submitted successfully.');
    } else {
        createAlert('danger', 'You cannot appeal at this time.');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/logo.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body data-bs-theme="dark">
    <?php include('assets/php/user_header.php'); ?>
    <div class="container full-height d-flex flex-column justify-content-center align-items-center">
        <div class="col-8">
            <?php if ($is_banned): ?>
                <div class="alert alert-danger" role="alert">
                    You are currently banned from the server.
                </div>
                <?php if ($ban_appeal_status == 1): ?>
                    <div class="alert alert-warning" role="alert">
                        You already have an open ban appeal. <a href="appeal.php?id=<?php echo $ban_appeal_id; ?>">View Appeal</a>
                    </div>
                <?php elseif ($ban_appeal_status == 2): ?>
                    <div class="alert alert-info" role="alert">
                        Your previous ban appeal has been closed. <a href="appeal.php?id=<?php echo $ban_appeal_id; ?>">View Appeal</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h2>Submit Ban Appeal</h2>
                            <form method="post" action="index.php">
                                <input type="hidden" name="type" value="ban">
                                <input type="hidden" name="type_id" value="<?php echo $ban_id; ?>">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Please state your case</label>
                                    <textarea id="reason" name="reason" class="form-control" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Appeal</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($is_muted): ?>
                <div class="alert alert-warning" role="alert">
                    You are currently muted on the server.
                </div>
                <?php if ($mute_appeal_status == 1): ?>
                    <div class="alert alert-warning" role="alert">
                        You already have an open mute appeal. <a href="appeal.php?id=<?php echo $mute_appeal_id; ?>">View Appeal</a>
                    </div>
                <?php elseif ($mute_appeal_status == 2): ?>
                    <div class="alert alert-info" role="alert">
                        Your previous mute appeal has been closed. <a href="appeal.php?id=<?php echo $mute_appeal_id; ?>">View Appeal</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h2>Submit Mute Appeal</h2>
                            <form method="post" action="index.php">
                                <input type="hidden" name="type" value="mute">
                                <input type="hidden" name="type_id" value="<?php echo $mute_id; ?>">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Please state your case</label>
                                    <textarea id="reason" name="reason" class="form-control" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Appeal</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-success" role="alert">
                    You are not banned or muted.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include('assets/php/user_footer.php'); ?>