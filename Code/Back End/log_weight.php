<?php
session_start();
include 'header.php';

// Connect to DB
$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$userId = $_SESSION['user_id'];
$message = "";

// Handle new or updated entry
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $weight = $_POST["weight"];
    if (is_numeric($weight) && $weight > 0) {
        $stmt = $conn->prepare("INSERT INTO weight_log (weight, user_id) VALUES (?, ?)");
        $stmt->bind_param("di", $weight, $userId);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Entry saved.");
        exit();
    } else {
        $message = "Please enter a valid weight.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM weight_log WHERE log_id = $deleteId AND user_id = $userId");
    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Entry deleted.");
    exit();
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Filter 
$filter = $_GET['filter'] ?? 'all';
$filterQuery = "1=1";
$summaryMessage = "";

if ($filter === 'week') {
    $filterQuery = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $summaryLabel = "this week";
} elseif ($filter === 'month') {
    $filterQuery = "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
    $summaryLabel = "this month";
}

// Summary message
if ($filter !== 'all') {
    $summarySql = "SELECT weight FROM weight_log WHERE user_id = $userId AND $filterQuery ORDER BY created_at ASC";
    $summaryResult = $conn->query($summarySql);
    $weights = [];
    while ($row = $summaryResult->fetch_assoc()) {
        $weights[] = $row['weight'];
    }
    if (count($weights) > 1) {
        $start = $weights[0];
        $end = end($weights);
        $diff = round($end - $start, 1);
        if ($diff > 0) {
            $summaryMessage = "You gained {$diff} lbs $summaryLabel.";
        } elseif ($diff < 0) {
            $summaryMessage = "You lost " . abs($diff) . " lbs $summaryLabel.";
        } else {
            $summaryMessage = "No weight change $summaryLabel.";
        }
    }
}

// Fetch logs
$entries = $conn->query("SELECT * FROM weight_log WHERE user_id = $userId AND $filterQuery ORDER BY created_at DESC");

// Chart data
$chartQuery = $conn->query("SELECT weight, created_at FROM weight_log WHERE user_id = $userId AND $filterQuery ORDER BY created_at ASC");
$weights = [];
$dates = [];
while ($row = $chartQuery->fetch_assoc()) {
    $weights[] = $row['weight'];
    $dates[] = $row['created_at'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Weight Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h1 class="page-heading">Weight Tracker</h1>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <fieldset class="weight-form">
            <legend>New Entry</legend>
            <label for="weight">Weight (lbs):</label>
            <input type="number" step="0.1" name="weight" required>
            <input type="submit" value="Log Weight">
        </fieldset>
    </form>

    <div class="message" style="text-align: center;">
        Filter: 
        <a href="?filter=all">All</a> |
        <a href="?filter=week">This Week</a> |
        <a href="?filter=month">This Month</a>
    </div>

    <?php if ($summaryMessage): ?>
        <div class="message" style="text-align: center;"><?= $summaryMessage ?></div>
    <?php endif; ?>

    <h2 class="section-heading">Weight History</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Weight</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $entries->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['created_at'] ?></td>
                    <td><?= $row['weight'] ?> lbs</td>
                    <td>
                        <form method="GET" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= $row['log_id'] ?>">
                            <button type="submit" onclick="return confirm('Delete this entry?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2 class="section-heading">Weight Progress Chart</h2>
    <canvas id="weightChart" width="600" height="300" class="weight-chart"></canvas>
</div>

<script>
    const ctx = document.getElementById('weightChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Weight (lbs)',
                data: <?= json_encode($weights) ?>,
                borderColor: 'blue',
                borderWidth: 2,
                fill: true,
                tension: 0.5
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
