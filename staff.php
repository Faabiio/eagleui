<?php
include 'core.php';

if (!isTeamMember()) {
    header("Location: index.php");
    exit();
}

// Fetch ban history
$sql = "SELECT * FROM bans WHERE banned_by = ? ORDER BY expiration DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_uuid);
$stmt->execute();
$ban_result = $stmt->get_result();
$bans = $ban_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch last open report
$sql = "SELECT id FROM reports WHERE state = 1 ORDER BY report_date DESC LIMIT 1";
$result = $conn->query($sql);
if ($result) {
    $last_open_report = $result->fetch_assoc();
    $last_open_report_id = $last_open_report ? $last_open_report['id'] : null;
} else {
    $last_open_report_id = null;
    // Handle error, e.g., log it or display a message
    error_log("Query failed: " . $conn->error);
}

// Fetch all appeals assigned to the staff member
$sql = "SELECT * FROM appeals WHERE staff_uuid = ? AND state = 0 LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_uuid);
$stmt->execute();
$appeals_result = $stmt->get_result();
$appeals = $appeals_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>


        <?php include('assets/php/staff_header.php'); ?>
        <div class="container full-height pt-5">
            <h2 class="mb-3">Hello, <?php echo getPlayerName($_SESSION['uuid']); ?></h2>
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-body">
                            <?php if (count($appeals) > 0): ?>
                                <h4>Appeals Assigned to You</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Type</th>
                                            <th>ID</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appeals as $appeal): ?>
                                            <tr>
                                                <td>
                                                    <img class="rounded me-2" src="<?php echo getPlayerImageUrl($appeal['uuid']); ?>" alt="Player Avatar" width="20" height="20">
                                                    <?php echo getPlayerName($appeal['uuid']); ?>
                                                </td>  
                                                <td><?php echo $appeal['type']; ?></td>
                                                <td><?php echo $appeal['type_id']; ?></td>
                                                <td><?php echo $appeal['state'] = 1 ? 'Open' : 'Closed'; ?></td>
                                                <td>
                                                    <a href="staff_appeal.php?id=<?php echo $appeal['id']; ?>" class="btn btn-primary btn-sm">View Appeal</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                    <h4>No Appeals assigned to you.</h4>
                                <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4>Ban History</h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Player</th>
                                        <th>Reason</th>
                                        <th>Banned By</th>
                                        <th>Expiration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bans as $ban): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img class="rounded me-2" src="<?php echo getPlayerImageUrl($ban['uuid']); ?>" alt="Player Avatar" width="20" height="20">
                                                    <?php echo getPlayerName($ban['uuid']); ?>
                                                </div>   
                                            </td>
                                            <td><?php echo $ban['reason']; ?></td>
                                            <td><?php echo getPlayerName($ban['banned_by']); ?></td>
                                            <td><?php echo $ban['expiration'] == -1 ? 'Permanent' : date('Y-m-d ', $ban['expiration'] / 1000); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                        <h4>Reports</h4>
                        <?php if ($last_open_report_id): ?>
                            <a href="report.php?id=<?php echo $last_open_report_id; ?>" class="btn btn-success">Open Report</a>
                        <?php else: ?>
                            <p>No open reports available.</p>
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4>Player Logs</h4>
                            <form action="logs.php">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="player_name" name="name">
                                </div>
                                <button type="submit" class="btn btn-success">Search</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('assets/php/staff_footer.php'); ?>
