<?php
include 'core.php';

if (!isset($_GET['id'])) {
    echo "No appeal ID provided.";
    exit();
}

$appeal_id = intval($_GET['id']);

// Fetch the appeal details
$appeal = getAppealDetails($appeal_id);

if (!$appeal) {
    header("Location: index.php");
    exit();
}

if ($appeal['uuid'] != $user_uuid) {
    header("Location: index.php");
    exit();
}

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = $_POST['message'];

    $sql = "INSERT INTO appeals_messages (appeal_id, category, author, message) VALUES (?, 0, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $appeal_id, $user_uuid, $message);
    if ($stmt->execute()) {
        header("Location: appeal.php?id=" . $appeal_id);
        exit();
    } else {
        echo "Error submitting message: " . $stmt->error;
    }

    $stmt->close();
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

<!DOCTYPE html>
<html>
<head>
    <title>Appeal #<?php echo $appeal_id; ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/logo.ico" type="image/x-icon">
</head>
<body data-bs-theme="dark">
    <?php include('assets/php/user_header.php'); ?>

    <div class="container full-height d-flex flex-column align-items-center">
        <div class="col-8 mt-5">
            <h3>Your Appeal</h3>
            <div class="chat-history">
                <?php foreach ($messages as $message): ?>
                    <div class="card mb-3">
                        <div class="card-body message border-bottom py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <img class="rounded me-2" src="<?php echo getPlayerImageUrl($message['author'])?>" alt="Avatar" width="30">
                                    <b><?php echo getPlayerName($message['author']); ?></b>
                                </div>
                                <div class="text-muted"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></div>
                            </div>
                            <div class="message-content p-3 pb-0">
                                <p><?php echo $message['message']; ?></p>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
            <?php if ($appeal['state'] == 0): ?>
                <div class="card">
                    <div class="card-body">
                        <form class="p-2 mt-3" method="post" action="">
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Response</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Submit Response</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="mt-3 alert alert-info" role="alert">
                    This appeal is closed and cannot be responded to.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>