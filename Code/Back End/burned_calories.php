<?php
session_start();
include 'header.php';

//connect to the database
$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$message = "";

//MET values for different activities
$activityMETs = [
    "Running" => 9.8,
    "Walking" => 3.5,
    "Cycling" => 7.5,
    "Swimming" => 8.3,
    "Yoga" => 2.5,
    "Weightlifting" => 6.0
];

//get user weight
$weightQuery = $conn->query("SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1");
$userWeight = ($weightQuery && $weightQuery->num_rows > 0) ? floatval($weightQuery->fetch_assoc()['weight']) : 0;

//handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity = $_POST["activity_name"];
    $duration = intval($_POST["duration_minutes"]);
    $met = $activityMETs[$activity] ?? 0;
    $weightKg = $userWeight * 0.453592;
    $calories = round(($met * 3.5 * $weightKg) / 200 * $duration);

    if ($duration > 0 && $met > 0 && $userWeight > 0) {
        if (isset($_POST["edit_id"])) {
            $editId = intval($_POST["edit_id"]);
            $stmt = $conn->prepare("UPDATE burned_calories_log SET calories_burned = ?, activity_name = ?, duration_minutes = ? WHERE log_id = ? AND user_id = ?");
            $stmt->bind_param("dsiii", $calories, $activity, $duration, $editId, $userId);
        } else {
            $stmt = $conn->prepare("INSERT INTO burned_calories_log (user_id, calories_burned, activity_name, duration_minutes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idsi", $userId, $calories, $activity, $duration);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Entry saved.");
        exit();
    } else {
        $message = "Please provide valid activity, duration, and make sure your weight is set.";
    }
}

//handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM burned_calories_log WHERE log_id = $deleteId AND user_id = $userId");
    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Entry deleted.");
    exit();
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

//filter and chart setup
$filter = $_GET['filter'] ?? 'all';
$filterQuery = "1=1";
$summaryMessage = "";

if ($filter === 'week') {
    $filterQuery = "log_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $sumQuery = "SELECT SUM(calories_burned) AS total FROM burned_calories_log WHERE user_id = $userId AND $filterQuery";
    $summaryLabel = "this week";
} elseif ($filter === 'month') {
    $filterQuery = "MONTH(log_date) = MONTH(NOW()) AND YEAR(log_date) = YEAR(NOW())";
    $sumQuery = "SELECT SUM(calories_burned) AS total FROM burned_calories_log WHERE user_id = $userId AND $filterQuery";
    $summaryLabel = "this month";
}

if (isset($sumQuery)) {
    $res = $conn->query($sumQuery);
    $total = $res ? intval($res->fetch_assoc()['total']) : 0;
    $summaryMessage = "You burned $total calories $summaryLabel.";
}

//data fetch
$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = $conn->query("SELECT * FROM burned_calories_log WHERE log_id = $editId AND user_id = $userId");
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}
$entries = $conn->query("SELECT * FROM burned_calories_log WHERE user_id = $userId AND $filterQuery ORDER BY log_date DESC");

$chartResult = $conn->query("SELECT log_date, calories_burned FROM burned_calories_log WHERE user_id = $userId AND $filterQuery ORDER BY log_date ASC");
$labels = [];
$data = [];
while ($row = $chartResult->fetch_assoc()) {
    $labels[] = $row['log_date'];
    $data[] = $row['calories_burned'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Burned Calories Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">

    <h1 class="page-heading">Burned Calories Tracker</h1>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <fieldset class="weight-form">
            <legend><?= $editData ? "Edit Entry" : "New Entry" ?></legend>
            <label for="activity_name">Activity:</label>
            <select name="activity_name" required>
                <option value="">--Select--</option>
                <?php foreach ($activityMETs as $act => $val): ?>
                    <option value="<?= $act ?>" <?= ($editData && $editData['activity_name'] == $act) ? 'selected' : '' ?>><?= $act ?></option>
                <?php endforeach; ?>
            </select>

            <label for="duration_minutes">Duration (minutes):</label>
            <input type="number" name="duration_minutes" required value="<?= $editData['duration_minutes'] ?? '' ?>">

            <?php if ($editData): ?>
                <input type="hidden" name="edit_id" value="<?= $editData['log_id'] ?>">
                <input type="submit" value="Update Entry">
                <a class="cancel-link" href="<?= $_SERVER['PHP_SELF'] ?>">Cancel</a>
            <?php else: ?>
                <input type="submit" value="Log Burned Calories">
            <?php endif; ?>
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

    <h2 class="section-heading">Burned Calories History</h2>
    <table class="weight-table">
        <tr>
            <th>Log ID</th>
            <th>Activity</th>
            <th>Duration (min)</th>
            <th>Calories Burned</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $entries->fetch_assoc()): ?>
            <tr>
                <td><?= $row['log_id'] ?></td>
                <td><?= $row['activity_name'] ?></td>
                <td><?= $row['duration_minutes'] ?></td>
                <td><?= round($row['calories_burned']) ?></td>
                <td><?= $row['log_date'] ?></td>
                <td>
                    <a href="?edit=<?= $row['log_id'] ?>">Edit</a> |
                    <a href="?delete=<?= $row['log_id'] ?>" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2 class="section-heading">Calories Burned Over Time</h2>
    <canvas id="caloriesChart" width="600" height="300" class="weight-chart"></canvas>
</div>

<script>
    const ctx = document.getElementById('caloriesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Calories Burned',
                data: <?= json_encode($data) ?>,
                borderColor: 'red',
                borderWidth: 2,
                fill: true,
                tension: 0.5
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
