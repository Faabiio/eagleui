<?php
include 'core.php';

if (!isTeamMember()) {
    header("Location: index.php");
    exit();
}

if(!isset($_GET['uuid'])) {
    if(!isset($_GET['name'])) {
        header("Location: index.php");
        exit();
    } else {
        $player_uuid = getPlayerUuid($_GET['name']);
        if ($player_uuid == null) {
            header("Location: index.php");
            exit();
        }
    }
} else {
    $player_uuid = $_GET['uuid'];
}



// Fetch all bans for the player
$sql = "SELECT * FROM bans WHERE uuid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_uuid);
$stmt->execute();
$bans_result = $stmt->get_result();
$bans = $bans_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all reports for the player
$sql = "SELECT * FROM reports WHERE reported_uuid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_uuid);
$stmt->execute();
$reports_result = $stmt->get_result();
$reports = $reports_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mutes = getMutes($player_uuid);

// Fetch all appeals for the player
$sql = "SELECT * FROM appeals WHERE uuid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_uuid);
$stmt->execute();
$appeals_result = $stmt->get_result();
$appeals = $appeals_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle make staff action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_staff'])) {
    if (makeTeamMember($player_uuid)) {
        ?>
        <div class="position-absolute mt-5 px-5 z-3 vw-100">
            <div class="m-5 alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> The user has been made a staff member, if they have an account.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php 
    } else {
        ?>
        <div class="position-absolute mt-5 px-5 z-3 vw-100">
            <div class="m-5 alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Maybe the user doesn't have an account.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php 
    }
}

$conn->close();
?>


    <?php include('assets/php/staff_header.php'); ?>
    <div class="container full-height d-flex flex-column justify-content-center align-items-center">
        <div class="col-12 my-5">
            <div class="d-flex justify-content-between align-items-between">
                <h2>Logs for Player: <?php echo getPlayerName($player_uuid); ?></h2>
                
                <form method="post" action="">
                    <input type="hidden" name="uuid" value="<?php echo $player_uuid; ?>">
                    <button type="submit" name="make_staff" class="btn btn-secondary">Make Staff Member</button>
                </form>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                <h3>Bans</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reason</th>
                                <th>Banned By</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Expiration</th>
                                <th>Unbanned by</th>
                                <th>Unbanned date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bans as $ban): ?>
                                <tr>
                                    <td><?php echo $ban['id']; ?></td>
                                    <td><?php echo $ban['reason']; ?></td>
                                    <td><?php echo getPlayerName($ban['banned_by']); ?></td>
                                    <td><?php echo formatDuration($ban['duration']); ?></td>
                                    <td><?php echo $ban['banned'] == 0 ? 'Unbanned' : 'Active'; ?></td>
                                    <td><?php echo $ban['expiration'] == -1 ? 'Permanent' : date('Y-m-d', $ban['expiration'] / 1000); ?></td>
                                    <td><?php echo getPlayerName($ban['unbanned_by']); ?></td>
                                    <td><?php echo $ban['unbanned_date']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h3>Mutes</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reason</th>
                                <th>Muted by</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Expiration</th>
                                <th>Unmuted by</th>
                                <th>Unmuted date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mutes as $mute): ?>
                                <tr>
                                    <td><?php echo $mute['id']; ?></td>
                                    <td><?php echo $mute['reason']; ?></td>
                                    <td><?php echo getPlayerName($mute['muted_by']); ?></td>
                                    <td><?php echo formatDuration($mute['duration'])?></td>
                                    <td><?php echo $mute['muted'] == 0 ? 'Unmuted' : 'Active'; ?></td>
                                    <td><?php echo $mute['expiration'] == -1 ? 'Permanent' : date('Y-m-d', $mute['expiration'] / 1000); ?></td>
                                    <td><?php echo getPlayerName($mute['unmuted_by']); ?></td>
                                    <td><?php echo $mute['unmuted_date'] ; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                <h3>Reports</h3>
                <table class="table ">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reason</th>
                            <th>Reported By</th>
                            <th>Report Date</th>
                            <th>Closed by</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo $report['id']; ?></td>
                                <td><?php echo $report['reason']; ?></td>
                                <td><?php echo getPlayerName($report['reporter_uuid']); ?></td>
                                <td><?php echo $report['report_date']; ?></td>
                                <td><?php echo getPlayerName($report['closed_by']); ?></td>
                                <td><a href="report.php?id=<?php echo $report['id']; ?>" class="btn btn-primary btn-sm">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                <h3>Appeals</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type ID</th>
                            <th>Staff UUID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appeals as $appeal): ?>
                            <tr>
                                <td><?php echo $appeal['id']; ?></td>
                                <td><?php echo $appeal['type_id']; ?></td>
                                <td><?php echo getPlayerName($appeal['staff_uuid']); ?></td>
                                <td><?php echo $appeal['state'] == 0 ? 'Open' : 'Closed'; ?></td>
                                <td><a href="staff_appeal.php?id=<?php echo $appeal['id']; ?>" class="btn btn-primary btn-sm">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <?php include('assets/php/staff_footer.php'); ?>