<?php 
include 'core.php';


if(!isTeamMember() || isset($_GET['id'])) {
    $report_id = $_GET['id'];
    $report = getReport($report_id);
    if(!$report) {
        header('Location: staff.php');
    }
} else {
    header('Location: staff.php');
}

// fetch ban and mute history
$ban_history = getBans($report['reported_uuid']);
$mute_history = getMutes($report['reported_uuid']);

if(isset($_POST['banDuration']) || isset($_POST['muteDuration'])) {
    if(isset($_POST['banDuration'])) {
        $ban_duration = $_POST['banDuration'];
        if ($ban_duration === 'no_punishment') {
            $ban_duration = 0;
        } elseif ($ban_duration == -1) {
            $ban_duration = -1;
        } else {
            $ban_duration = $ban_duration * 86400000; // Convert days to seconds
        }
        $ban_reason = $report['reason'];
        $ban_reported = $report['reported_uuid'];
        $ban_by = $_SESSION['uuid'];
        $ban_expiration = round(microtime(true) * 1000) + $ban_duration;
    
        $ban_id = createBan($ban_reason, $ban_duration, $ban_expiration, $ban_reported, $ban_by, $report_id);
    } 
    if(isset($_POST['muteDuration'])) {
        $mute_duration = $_POST['muteDuration'];
        if ($mute_duration === 'no_punishment') {
            $mute_duration = 0;
        } elseif ($mute_duration == -1) {
            $mute_duration = -1;
        } else {
            $mute_duration = $mute_duration * 86400000; // Convert days to seconds
        }
        $mute_reason = $report['reason'];
        $mute_reported = $report['reported_uuid'];
        $mute_by = $_SESSION['uuid'];
        $mute_expiration = round(microtime(true) * 1000) + $mute_duration;
    
        $mute_id = createMute($mute_reason, $mute_duration, $mute_expiration, $mute_reported, $mute_by, $report_id);
    }

    closeReport($report_id, $_SESSION['uuid']);    

    header('Location: staff.php');
}   


include 'assets/php/staff_header.php';

?>

<div class="container full-height d-flex flex-column">
    <div class="container mt-5">
        <div class="row">
            <div class="col-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>Report #<?php echo $report['id']; ?></h3>
                            <p><?php echo $report['report_date']; ?></p>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <p class="fw-bold"><?php echo $report['reason']; ?> <span class="ms-1 badge text-bg-secondary"><?php echo $report['server_name']; ?></span></p>
                            <p>reported by <b><?php echo getPlayerName($report['reporter_uuid']); ?></b></p>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h3>Actions</h3>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#banModal">
                            Ban
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#muteModal">
                            Mute
                        </button>
                        <form action="report.php?id=<?php echo $report['id']?>" method="post">
                            <div class="modal fade" id="banModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Ban</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="d-flex flex-column">
                                                <input type="hidden" name="id" value="<?php echo $report['id']; ?>">
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="no_punishment" value="no_punishment" required>
                                                    <label class="form-check-label ps-1" for="no_punishment">No punishment</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="1d" value="1">
                                                    <label class="form-check-label" for="1d">1d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="7d" value="7">
                                                    <label class="form-check-label" for="7d">7d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="14d" value="14">
                                                    <label class="form-check-label" for="14d">14d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="30d" value="30">
                                                    <label class="form-check-label" for="30d">30d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="1y" value="365">
                                                    <label class="form-check-label" for="1y">1y</label>
                                                </div>                                         
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="banDuration" id="perma" value="-1">
                                                    <label class="form-check-label" for="perma">Permanent</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form action="report.php?id=<?php echo $report['id']?>" method="post">
                            <div class="modal fade" id="muteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Mute</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="d-flex flex-column">
                                                <input type="hidden" name="id" value="<?php echo $report['id']; ?>">
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="no_punishment" value="no_punishment" required>
                                                    <label class="form-check-label ps-1" for="no_punishment">No punishment</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="1d" value="1">
                                                    <label class="form-check-label" for="1d">1d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="7d" value="7">
                                                    <label class="form-check-label" for="7d">7d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="14d" value="14">
                                                    <label class="form-check-label" for="14d">14d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="30d" value="30">
                                                    <label class="form-check-label" for="30d">30d</label>
                                                </div>
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="1y" value="365">
                                                    <label class="form-check-label" for="1y">1y</label>
                                                </div>                                         
                                                <div class="form-check m-2">
                                                    <input class="form-check-input" type="radio" name="muteDuration" id="perma" value="-1">
                                                    <label class="form-check-label" for="perma">Permanent</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-5">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Player</h4>
                            <div>
                            <?php
                                if(isUuidBanned($report['reported_uuid'])['is_banned']) {
                                    echo '<span class="mx-1 badge bg-danger">Banned</span>';
                                } 
                                if (isUuidMuted($report['reported_uuid'])['is_muted']) {
                                    echo '<span class="mx-1 badge bg-warning">Muted</span>';
                                } 
                            ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center my-3">
                            <img class="me-2" src="<?php echo getPlayerImageUrl($report['reported_uuid'])?>" width="50" alt="">
                            <h3 class="m-0"><?php echo getPlayerName($report['reported_uuid']); ?></h3>
                        </div>
                        <p><?php echo $report['reported_uuid']; ?></p>
                        <hr>
                        <a href="logs.php?uuid=<?php echo $report['reported_uuid']; ?>">Logs</a><br>
                        <a href="https://namemc.com/<?php echo getPlayerName($report['reported_uuid']); ?>">NameMC</a>
                        <hr>
                        <p class="fw-bolder">Bans</p>
                        <?php foreach ($ban_history as $ban): ?>
                            <div class="row">
                                <p class="col-3 mb-0 fw-bolder"><?php echo $ban['reason'] ; ?></p>
                                <p class="col-3 mb-0 "><?php echo $ban['banned'] == 1 ? 'Active' : 'Inactive'; ?></p>
                                <p class="col-3 mb-0 "><?php echo date('Y-m-d', strtotime($ban['ban_date'])); ?></p>
                                <p class="col-3 mb-0 "><?php echo formatDuration($ban['duration'])?></p>
                            </div>
                            <hr>
                        <?php endforeach; ?>

                        <p class="fw-bolder">Mutes</p>
                        <?php foreach ($mute_history as $mute): ?>
                            <div class="row fs-6">
                                <p class="col-3 mb-0 fw-bolder"><?php echo $mute['reason'] ; ?></p>
                                <p class="col-3 mb-0 "><?php echo $mute['muted'] == 1 ? 'Active' : 'Inactive'; ?></p>
                                <p class="col-3 mb-0 "><?php echo date('Y-m-d', strtotime($mute['mute_date'])); ?></p>
                                <p class="col-3 mb-0 "><?php echo formatDuration($mute['duration'])?></p>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    

<?php

include 'assets/php/staff_footer.php';
?>