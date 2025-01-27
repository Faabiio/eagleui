<?php
include 'core.php';

if (!isTeamMember() || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$appeal_id = intval($_GET['id']);

// Fetch the appeal details
$appeal = getAppealDetails($appeal_id);

if (!$appeal) {
    echo "No appeal found with the provided ID.";
    exit();
}

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = $_POST['message'];

    $sql = "INSERT INTO appeals_messages (appeal_id, author, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $appeal_id, $user_uuid, $message);
    if ($stmt->execute()) {
        header("Location: staff_appeal.php?id=" . $appeal_id);
        exit();
    } else {
        echo "Error submitting message: " . $stmt->error;
    }

    $stmt->close();
}

// Handle accept and decline actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['accept']) || isset($_POST['decline']))) {
    $state = 1;
    $action_message = '';

    if (isset($_POST['accept'])) {
        $unbanned_date = date('Y-m-d H:i:s');
        $sql = "UPDATE bans SET banned = 0, unbanned_by = ?, unbanned_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $user_uuid, $unbanned_date, $appeal['type_id']);
        $stmt->execute();
        $stmt->close();

        $action_message = 'The appeal has been accepted and the ban has been lifted.';
    } elseif (isset($_POST['decline'])) {
        $action_message = 'The appeal has been declined.';
    }

    // Update the appeal state to 1
    $sql = "UPDATE appeals SET state = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $state, $appeal_id);
    $stmt->execute();
    $stmt->close();

    // Insert the action message into the appeals_messages table
    $sql = "INSERT INTO appeals_messages (appeal_id, author, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $appeal_id, $user_uuid, $action_message);
    $stmt->execute();
    $stmt->close();

    header("Location: staff_appeal.php?id=" . $appeal_id);
    exit();
}

// Fetch the chat history
$sql = "SELECT * FROM appeals_messages WHERE appeal_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appeal_id);
$stmt->execute();
$messages_result = $stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

    <?php include('assets/php/staff_header.php'); ?>
    <div class="container full-height d-flex flex-column align-items-center">
        <div class="col-8 mt-5">
            <h3>Appeal</h3>
            <div class="chat-history">
                <?php foreach ($messages as $message): ?>
                    <div class="card mb-3">
                        <div class="card-body message border-bottom">
                            <div class="d-flex align-items-center">
                                <img class="rounded me-2" src="<?php echo getPlayerImageUrl($message['author'])?>" alt="Avatar" width="30">
                                <b><?php echo getPlayerName($message['author']); ?></b>
                            </div>
                            <div class="message-content p-3 pb-0">
                                <p><?php echo $message['message']; ?></p>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <form class="p-2" method="post" action="">
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Response</label>
                            <textarea class="form-control" id="message" name="message" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Response</button>
                    </form>

                    <?php if ($appeal['state'] == 0): ?>
                    <form method="post" action="" class="mt-3">
                        <button type="submit" name="accept" class="btn btn-success">Accept Appeal</button>
                        <button type="submit" name="decline" class="btn btn-danger">Decline Appeal</button>
                    </form>
                    <?php else: ?>
                    <div class="mt-3 alert alert-info" role="alert">
                        This appeal is closed!
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include('assets/php/staff_footer.php'); ?>