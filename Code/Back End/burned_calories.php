<?php
session_start();
include 'header.php';

//connect to database
$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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

//handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity = $_POST["activity_name"];
    $duration = intval($_POST["duration_minutes"]);
    $met = $activityMETs[$activity] ?? 0;

    //get users weight from weight_log
    $weightResult = $conn->query("SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1");
    $userWeight = ($weightResult && $weightResult->num_rows > 0) ? floatval($weightResult->fetch_assoc()['weight']) : 0;

    if ($duration > 0 && $met > 0 && $userWeight > 0) {
        //calorie formula
        $weightKg = $userWeight * 0.453592;
        $calories = round(($met * 3.5 * $weightKg * $duration) / 200);

        $stmt = $conn->prepare("INSERT INTO burned_calories_log (user_id, calories_burned, activity_name, duration_minutes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idsi", $userId, $calories, $activity, $duration);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Entry saved.");
        exit();
    } else {
        $message = "Please enter a valid activity, duration, and make sure weight is set.";
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

//filter
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

$entries = $conn->query("SELECT * FROM burned_calories_log WHERE user_id = $userId AND $filterQuery ORDER BY log_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Burned Calories Tracker</title>
</head>
<body>
<div class="container">
    <h1 class="page-heading">Burned Calories Tracker</h1>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <fieldset class="weight-form">
            <legend>New Entry</legend>
            <label for="activity_name">Activity:</label>
            <select name="activity_name" required>
                <option value="">--Select--</option>
                <?php foreach ($activityMETs as $act => $val): ?>
                    <option value="<?= $act ?>"><?= $act ?></option>
                <?php endforeach; ?>
            </select>

            <label for="duration_minutes">Duration (minutes):</label>
            <input type="number" name="duration_minutes" required>

            <input type="submit" value="Log Burned Calories">
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
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Activity</th>
                <th>Duration</th>
                <th>Calories</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $entries->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['log_date'] ?></td>
                    <td><?= $row['activity_name'] ?></td>
                    <td><?= $row['duration_minutes'] ?> min</td>
                    <td><?= round($row['calories_burned']) ?> cal</td>
                    <td>
                        <form method="GET" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= $row['log_id'] ?>">
                            <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!--Burned calories chart-->
    <h2 class="section-heading">Calories Burned Over Time</h2>
    <iframe src="burned_calories_chart.php?filter=<?= urlencode($filter) ?>" width="600" height="300" style="border:none; display:block; margin:auto;"></iframe>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
