<?php
include 'minecraft.php';


/**
 * Checks if a UUID is banned.
 *
 * @param string $uuid The UUID of the player.
 * @return array An associative array with 'is_banned' status, 'ban_id', and 'staff_uuid'.
 */
function isUuidBanned($uuid) {
    global $conn;
    $sql = "SELECT id, banned_by FROM bans WHERE uuid = ? AND (expiration = -1 OR expiration > ?) AND banned = 1 ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $current_time = time() * 1000; 
    $stmt->bind_param("si", $uuid, $current_time);
    $stmt->execute();
    $ban_result = $stmt->get_result();
    $is_banned = $ban_result->num_rows > 0;
    $ban_info = $is_banned ? $ban_result->fetch_assoc() : null;
    $ban_id = $ban_info ? $ban_info['id'] : null;
    $staff_uuid = $ban_info ? $ban_info['banned_by'] : null;
    $stmt->close();
    return ['is_banned' => $is_banned, 'ban_id' => $ban_id, 'staff_uuid' => $staff_uuid];
}

/**
 * Checks if a UUID is muted.
 *
 * @param string $uuid The UUID of the player.
 * @return array An associative array with 'is_muted' status, 'mute_id', and 'staff_uuid'.
 */
function isUuidMuted($uuid) {
    global $conn;
    $sql = "SELECT id, muted_by FROM mutes WHERE uuid = ? AND (expiration = -1 OR expiration > ?) AND muted = 1 ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $current_time = time() * 1000; 
    $stmt->bind_param("si", $uuid, $current_time);
    $stmt->execute();
    $mute_result = $stmt->get_result();
    $is_muted = $mute_result->num_rows > 0;
    $mute_info = $is_muted ? $mute_result->fetch_assoc() : null;
    $mute_id = $mute_info ? $mute_info['id'] : null;
    $staff_uuid = $mute_info ? $mute_info['muted_by'] : null;
    $stmt->close();
    return ['is_muted' => $is_muted, 'mute_id' => $mute_id, 'staff_uuid' => $staff_uuid];
}

/**
 * Checks if the user is a team member.
 *
 * @return bool True if the user is a team member, false otherwise.
 */
function isTeamMember() {
    return $_SESSION['rank'] == "2";
}

/**
 * Gets all mutes for a given player UUID.
 *
 * @param string $uuid The UUID of the player.
 * @return array An array of mutes for the player.
 */
function getMutes($uuid) {
    global $conn;
    $sql = "SELECT * FROM mutes WHERE uuid = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    $mutes = [];
    while ($row = $result->fetch_assoc()) {
        $mutes[] = $row;
    }
    $stmt->close();
    return $mutes;
}


/**
 * Creates a mute for a user.
 *
 * @param string $reason The reason for the mute.
 * @param int $duration The duration of the mute in milliseconds.
 * @param int $expiration The expiration time of the mute in milliseconds.
 * @param string $uuid The UUID of the user being muted.
 * @param string $muted_by The UUID of the user who issued the mute.
 * @param int $report_id The ID of the report.
 * @return bool True if the mute was created successfully, false otherwise.
 */
function createMute($reason, $duration, $expiration, $uuid, $muted_by, $report_id) {
    if (isUuidMuted($uuid)['is_muted']) {
        createAlert('warning', 'This player is already muted.');
    } else {
        global $conn;
        $sql = "INSERT INTO mutes (uuid, reason, muted_by, expiration, duration, report_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            createAlert('danger', 'Error preparing statement: ' . $conn->error);
            die();
        }
        $stmt->bind_param("sssiii", $uuid, $reason, $muted_by, $expiration, $duration, $report_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

/**
 * Gets all bans for a given player UUID.
 *
 * @param string $uuid The UUID of the player.
 * @return array An array of bans for the player.
 */

function getBans($uuid) {
    global $conn;
    $sql = "SELECT * FROM bans WHERE uuid = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        createAlert('danger', 'Error preparing statement: ' . $conn->error);
        die();
    }
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    $bans = [];
    while ($row = $result->fetch_assoc()) {
        $bans[] = $row;
    }
    $stmt->close();
    return $bans;
}

/**
 * Gets the details of a ban by its ID.
 *
 * @param int $ban_id The ID of the ban.
 * @return array|null The ban details or null if not found.
 */
function getBanDetails($ban_id) {
    global $conn;
    $sql = "SELECT * FROM bans WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();
    $ban_result = $stmt->get_result();
    $ban = $ban_result->fetch_assoc();
    $stmt->close();
    return $ban;
}

/**
 * Creates a ban
 *
 * @param string $reason The reason for the ban.
 * @param int $duration The duration of the ban.
 * @param int $expiration The expiration time of the ban.
 * @param string $server The server where the ban was issued.
 * @param string $reporter The UUID of the reporter.
 * @param string $reported The UUID of the reported player.
 * @param string $banned_by The UUID of the staff member who issued the ban.
 * @param int $report_id The ID of the report.
 * @return int The ID of the ban.
 */
 
function createBan($reason, $duration, $expiration, $uuid, $banned_by, $report_id) {
    if (isUuidBanned($uuid)['is_banned']) {
        createAlert('warning', 'This player is already banned.');
    } else {
        global $conn;
        $sql = "INSERT INTO bans (uuid, reason, banned_by, expiration, duration, report_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            createAlert('danger', 'Error preparing statement: ' . $conn->error);
            die();
        }
        $stmt->bind_param("sssiii", $uuid, $reason, $banned_by, $expiration, $duration, $report_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

/**
 * Sets the permission of a user to team member.
 *
 * @param string $uuid The UUID of the user.
 * @return bool True if the update was successful, false otherwise.
 */

function makeTeamMember($uuid) {
    global $conn;
    $sql = "UPDATE users SET permission = 2 WHERE uuid = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $uuid);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Gets the report details for a given report ID.
 *
 * @param int $id The ID of the report.
 * @return array An associative array with the report details.
 */
function getReport($id) {
    global $conn;
    $sql = "SELECT * FROM reports WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
    } else {
        $report = null;
    }
    $stmt->close();
    return $report;
}

/**
 * Closes a report by setting its state to 0.
 *
 * @param int $id The ID of the report.
 * @param string $staff_uuid The UUID of the staff member who closed the report.
 * @return bool True if the report was closed successfully, false otherwise.
 */
function closeReport($id, $staff_uuid) {
    global $conn;
    $sql = "UPDATE reports SET state = 0, closed_by = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("si", $staff_uuid, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


/**
 * Checks if a UUID has an open ban appeal.
 *
 * @param string $uuid The UUID of the player.
 * @return bool True if the UUID has an open ban appeal, false otherwise.
 */
function hasOpenBanAppeal($uuid) {
    global $conn;
    $sql = "SELECT * FROM appeals WHERE uuid = ? AND state = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $appeal_result = $stmt->get_result();
    $has_open_appeal = $appeal_result->num_rows > 0;
    $stmt->close();
    return $has_open_appeal;
}

/**
 * Checks if there is already an appeal with the given ban/mute ID and UUID.
 *
 * @param int $type_id The ID of the ban or mute.
 * @param string $uuid The UUID of the player.
 * @param string $type The type of appeal ('ban' or 'mute').
 * @return array An associative array with 'status' (1 if open, 2 if closed, 0 if none), 'appeal_id' (null if none), and 'type' (type of the appeal).
 */
function hasOrCanAppeal($type_id, $uuid, $type) {
    global $conn;
    $sql = "SELECT id, state, type FROM appeals WHERE type_id = ? AND uuid = ? AND type = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iss", $type_id, $uuid, $type);
    $stmt->execute();
    $stmt->bind_result($appeal_id, $state, $appeal_type);
    if ($stmt->fetch()) {
        $stmt->close();
        return ['status' => $state == 0 ? 1 : 2, 'appeal_id' => $appeal_id, 'type' => $appeal_type];
    } else {
        $stmt->close();
        return ['status' => 0, 'appeal_id' => null, 'type' => null];
    }
}


function getAppealDetails($appeal_id) {
    global $conn;
    $sql = "SELECT * FROM appeals WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appeal_id);
    $stmt->execute();
    $appeal_result = $stmt->get_result();
    $appeal = $appeal_result->fetch_assoc();
    $stmt->close();
    return $appeal;
}

/**
 * Gets the details of an appeal by the ban ID.
 *
 * @param int $ban_id The ID of the ban.
 * @return array|null The appeal details or null if not found.
 */
function getAppealByBanId($ban_id) {
    global $conn;
    $sql = "SELECT * FROM appeals WHERE ban_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();
    $appeal_result = $stmt->get_result();
    $appeal = $appeal_result->fetch_assoc();
    $stmt->close();
    return $appeal;
}

function formatDuration($t) {
    if ($t == -1) {
        return 'Permanent';
    } else {
        $d = $t / 1000;
        if ($d < 3600) {
            return round($d / 60) . 'm';
        } elseif ($d < 86400) {
            return round($d / 3600) . 'h';
        } elseif ($d < 31536000) {
            return round($d / 86400) . 'd';
        } else {
            return round($d / 31536000) . 'y';
        }
    }
}

/**
 * Creates an alert message.
 *
 * @param string $type The type of the alert (success, danger, warning, info).
 * @param string $message The message to display.
 */

function createAlert($type, $message) {
    echo '<div class="position-absolute mt-5 px-5 z-3 vw-100"><div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . $message . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>';
    return;
}


?>